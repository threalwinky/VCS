<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use SessionGuards;

    public function dashboard(Request $request)
    {
        if ($redirect = $this->requireAuth($request)) {
            return $redirect;
        }

        return view('dashboard');
    }
}
