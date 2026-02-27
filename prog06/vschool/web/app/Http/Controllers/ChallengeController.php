<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChallengeController extends Controller
{
    use SessionGuards;

    public function challenge(Request $request)
    {
        if ($redirect = $this->requireAuth($request)) {
            return $redirect;
        }

        $userId = $this->currentUserId($request);
        $isTeacher = $this->isTeacherOrAdmin($request);
        $message = '';
        $error = '';
        $poemContent = '';

        if ($request->isMethod('post')) {
            $action = (string) $request->input('action', '');

            if ($action === 'create_challenge' && $isTeacher) {
                $hintText = trim((string) $request->input('hint_text', ''));
                $file = $request->file('challenge_file');

                if ($hintText === '' || !$file) {
                    $error = 'Can nhap goi y va chon file txt.';
                } else {
                    $originalName = (string) $file->getClientOriginalName();
                    $ext = strtolower((string) $file->getClientOriginalExtension());

                    if ($ext !== 'txt') {
                        $error = 'Chi cho phep upload file .txt';
                    } elseif (!preg_match('/^[A-Za-z0-9]+( [A-Za-z0-9]+)*\.txt$/', $originalName)) {
                        $error = 'Ten file khong dau';
                    } else {
                        $baseDir = public_path('uploads/challenges');
                        if (!is_dir($baseDir)) {
                            mkdir($baseDir, 0777, true);
                        }

                        $folder = 'challenge_'.time().'_'.random_int(1000, 9999);
                        $challengeDir = $baseDir.'/'.$folder;
                        if (!mkdir($challengeDir, 0777, true) && !is_dir($challengeDir)) {
                            $error = 'Khong tao duoc thu muc challenge.';
                        } else {
                            $file->move($challengeDir, $originalName);
                            DB::table('challenges')->insert([
                                'hint_text' => $hintText,
                                'file_path' => '/uploads/challenges/'.$folder,
                                'original_name' => '',
                                'created_by' => $userId,
                                'created_at' => now(),
                            ]);
                            $message = 'Tao challenge thanh cong.';
                        }
                    }
                }
            }

            if ($action === 'solve_challenge' && !$isTeacher) {
                $challengeId = (int) $request->input('challenge_id', 0);
                $answer = trim((string) $request->input('answer', ''));

                if ($challengeId <= 0 || $answer === '') {
                    $error = 'Vui long nhap dap an.';
                } else {
                    $challenge = DB::selectOne(
                        'SELECT c.file_path FROM challenges c JOIN class_user cu_student ON cu_student.user_id = ? JOIN class_user cu_teacher ON cu_teacher.user_id = c.created_by AND cu_teacher.class_id = cu_student.class_id WHERE c.id = ? LIMIT 1',
                        [$userId, $challengeId]
                    );

                    if (!$challenge) {
                        $error = 'Challenge khong ton tai.';
                    } else {
                        $challengeDir = public_path(ltrim((string) $challenge->file_path, '/'));
                        $files = is_dir($challengeDir) ? glob($challengeDir.'/*.txt') : [];
                        if (!$files || count($files) === 0) {
                            $error = 'Khong tim thay file dap an tren server.';
                        } else {
                            $fileName = basename($files[0]);
                            $answerNoExt = pathinfo($fileName, PATHINFO_FILENAME);
                            $input = strtolower(trim($answer));
                            if ($input === strtolower(trim($answerNoExt)) || $input === strtolower(trim($fileName))) {
                                $poemContent = (string) file_get_contents($files[0]);
                                $message = 'Chinh xac! Day la noi dung file dap an.';
                            } else {
                                $error = 'Dap an chua dung, hay thu lai.';
                            }
                        }
                    }
                }
            }
        }

        if ($isTeacher) {
            $currentChallenge = DB::table('challenges as c')
                ->join('users as u', 'c.created_by', '=', 'u.id')
                ->select(['c.id', 'c.hint_text', 'c.created_at', 'u.full_name as teacher_name'])
                ->orderByDesc('c.id')
                ->first();
        } else {
            $currentChallenge = DB::selectOne(
                'SELECT c.id, c.hint_text, c.created_at, u.full_name AS teacher_name FROM challenges c JOIN users u ON c.created_by = u.id JOIN class_user cu_student ON cu_student.user_id = ? JOIN class_user cu_teacher ON cu_teacher.user_id = c.created_by AND cu_teacher.class_id = cu_student.class_id ORDER BY c.id DESC LIMIT 1',
                [$userId]
            );
        }

        return view('challenge', compact('currentChallenge', 'isTeacher', 'message', 'error', 'poemContent'));
    }
}
