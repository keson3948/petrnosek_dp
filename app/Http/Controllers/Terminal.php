<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Terminal as TerminalModel;

class Terminal extends Controller
{
    public function __invoke(string $token)
    {
        $terminal = TerminalModel::where('slug', $token)
            ->where('is_active', true)
            ->firstOrFail();

        cookie()->queue(
            cookie(
                'terminal_token',
                $terminal->slug,
                60 * 24 * 30
            )
        );

        session(['terminal_id' => $terminal->id]);

        return redirect()->route('login');
    }
}
