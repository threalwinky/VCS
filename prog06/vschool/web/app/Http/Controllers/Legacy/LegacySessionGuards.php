<?php

namespace App\Http\Controllers\Legacy;

use Illuminate\Http\Request;

trait LegacySessionGuards
{
    protected function currentUserId(Request $request): int
    {
        return (int) $request->session()->get('user_id', 0);
    }

    protected function currentRole(Request $request): string
    {
        return (string) $request->session()->get('role', '');
    }

    protected function isTeacherOrAdmin(Request $request): bool
    {
        return in_array($this->currentRole($request), ['teacher', 'admin'], true);
    }

    protected function requireAuth(Request $request)
    {
        if (!$this->currentUserId($request)) {
            return redirect('/index.php');
        }

        return null;
    }

    protected function requireTeacherOrAdmin(Request $request): void
    {
        if (!$this->isTeacherOrAdmin($request)) {
            abort(403, 'Ban khong co quyen truy cap trang nay.');
        }
    }
}
