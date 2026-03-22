<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RfidLoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'izo' => 'required|string|max:10',
        ]);

        $user = User::where('izo', $request->izo)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return back()->withErrors(['izo' => 'Neznámý nebo zablokovaný čip.']);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended('dashboard');
    }
}
