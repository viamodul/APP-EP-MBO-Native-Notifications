<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shops = $user->shops()->withCount('webhookLogs')->get();

        return view('dashboard', [
            'shops' => $shops,
        ]);
    }
}
