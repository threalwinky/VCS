<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LegacyDashboardController extends Controller
{
    use LegacySessionGuards;

    public function dashboard(Request $request)
    {
        if ($redirect = $this->requireAuth($request)) {
            return $redirect;
        }

        return view('legacy.dashboard');
    }
}
