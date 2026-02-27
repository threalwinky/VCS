<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    use SessionGuards;

    public function assignments(Request $request)
    {
        if ($redirect = $this->requireAuth($request)) {
            return $redirect;
        }

        $userId = $this->currentUserId($request);
        $isTeacher = $this->isTeacherOrAdmin($request);
        $message = '';
        $error = '';

        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');

            if ($action === 'create_assignment' && $isTeacher) {
                $title = trim((string) $request->input('title', ''));
                $description = trim((string) $request->input('description', ''));

                if ($title === '' || !$request->hasFile('assignment_file')) {
                    $error = 'Vui long nhap tieu de va chon file bai tap.';
                } else {
                    $file = $request->file('assignment_file');
                    if ($file && $file->isValid()) {
                        $targetDir = public_path('uploads/assignments');
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0777, true);
                        }
                        $originalName = (string) $file->getClientOriginalName();
                        $ext = strtolower((string) $file->getClientOriginalExtension());
                        $savedName = 'assignment_'.time().'_'.random_int(1000, 9999).($ext ? '.'.$ext : '');
                        $file->move($targetDir, $savedName);
                        $relativePath = '/uploads/assignments/'.$savedName;

                        DB::table('assignments')->insert([
                            'title' => $title,
                            'description' => $description,
                            'file_path' => $relativePath,
                            'original_name' => $originalName,
                            'created_by' => $userId,
                            'created_at' => now(),
                        ]);
                        $message = 'Dang bai tap thanh cong.';
                    } else {
                        $error = 'Khong upload duoc file bai tap.';
                    }
                }
            }

            if ($action === 'submit_assignment' && !$isTeacher) {
                $assignmentId = (int) $request->input('assignment_id', 0);
                if ($assignmentId <= 0 || !$request->hasFile('submission_file')) {
                    $error = 'Thong tin nop bai khong hop le.';
                } else {
                    $access = DB::selectOne(
                        'SELECT a.id FROM assignments a JOIN class_user cu_student ON cu_student.user_id = ? JOIN class_user cu_teacher ON cu_teacher.user_id = a.created_by AND cu_teacher.class_id = cu_student.class_id WHERE a.id = ? LIMIT 1',
                        [$userId, $assignmentId]
                    );

                    if (!$access) {
                        $error = 'Ban khong co quyen nop bai cho bai tap nay.';
                    } else {
                        $file = $request->file('submission_file');
                        if ($file && $file->isValid()) {
                            $targetDir = public_path('uploads/submissions');
                            if (!is_dir($targetDir)) {
                                mkdir($targetDir, 0777, true);
                            }
                            $originalName = (string) $file->getClientOriginalName();
                            $ext = strtolower((string) $file->getClientOriginalExtension());
                            $savedName = 'submission_'.$userId.'_'.$assignmentId.'_'.time().($ext ? '.'.$ext : '');
                            $file->move($targetDir, $savedName);
                            $relativePath = '/uploads/submissions/'.$savedName;

                            $existing = DB::table('submissions')
                                ->where('assignment_id', $assignmentId)
                                ->where('student_id', $userId)
                                ->first();

                            if ($existing) {
                                DB::table('submissions')->where('id', $existing->id)->update([
                                    'file_path' => $relativePath,
                                    'original_name' => $originalName,
                                    'submitted_at' => now(),
                                ]);
                                $message = 'Da cap nhat bai nop thanh cong.';
                            } else {
                                DB::table('submissions')->insert([
                                    'assignment_id' => $assignmentId,
                                    'student_id' => $userId,
                                    'file_path' => $relativePath,
                                    'original_name' => $originalName,
                                    'submitted_at' => now(),
                                ]);
                                $message = 'Nop bai thanh cong.';
                            }
                        } else {
                            $error = 'Khong upload duoc bai lam.';
                        }
                    }
                }
            }
        }

        if ($isTeacher) {
            $assignments = DB::table('assignments as a')
                ->join('users as u', 'a.created_by', '=', 'u.id')
                ->select(['a.id', 'a.title', 'a.description', 'a.file_path', 'a.original_name', 'a.created_at', 'u.full_name as teacher_name'])
                ->orderByDesc('a.id')
                ->get();
        } else {
            $assignments = DB::select(
                'SELECT DISTINCT a.id, a.title, a.description, a.file_path, a.original_name, a.created_at, u.full_name AS teacher_name FROM assignments a JOIN users u ON a.created_by = u.id JOIN class_user cu_student ON cu_student.user_id = ? JOIN class_user cu_teacher ON cu_teacher.user_id = a.created_by AND cu_teacher.class_id = cu_student.class_id ORDER BY a.id DESC',
                [$userId]
            );
            $assignments = collect($assignments);
        }

        $submissionRows = collect();
        if ($isTeacher) {
            $submissionRows = DB::table('submissions as s')
                ->join('users as u', 's.student_id', '=', 'u.id')
                ->join('assignments as a', 's.assignment_id', '=', 'a.id')
                ->select(['s.id', 's.assignment_id', 's.file_path', 's.original_name', 's.submitted_at', 'u.full_name as student_name', 'a.title as assignment_title'])
                ->orderByDesc('s.submitted_at')
                ->get();
        }

        return view('assignments', compact('assignments', 'submissionRows', 'isTeacher', 'message', 'error'));
    }
}
