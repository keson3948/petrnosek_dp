<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Subjekt;

class RfidLoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'izo' => 'required|string|max:10',
        ]);

        $izo = $request->input('izo');

        $subjekt = Subjekt::where('Izo', $izo)->first();

        if (! $subjekt) {
            return back()->withErrors(['izo' => 'Neznámý čip / karta (Firebird).']);
        }

        $user = User::where('izo', $izo)->first();

        if (! $user) {
            $user = User::create([
                'name' => trim($subjekt->Prijmeni . ' ' . $subjekt->Jmeno),
                'email' => $subjekt->emailKontakt->Hodnota ?? ($izo . '@rfid.local'), // Use Hodnota from relation or fallback
                'password' => Hash::make(Str::random(16)), // Random password, they use chip
                'izo' => $izo,
                'klic_subjektu' => $subjekt->KlicSubjektu,
            ]);
        } else {
            $newName = trim($subjekt->Prijmeni . ' ' . $subjekt->Jmeno);
            if ($user->klic_subjektu !== $subjekt->KlicSubjektu || $user->name !== $newName) {
                $user->update([
                    'klic_subjektu' => $subjekt->KlicSubjektu,
                    'name' => $newName
                ]);
            }
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended('dashboard');
    }
}
