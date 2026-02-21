<?php

namespace App\Http\Middleware;

use App\Models\Terminal;
use Closure;
use Illuminate\Http\Request;

class ResolveTerminal
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('terminal_id')) {
            $terminal = Terminal::find(session('terminal_id'));

            if ($terminal && $terminal->is_active) {
                app()->instance('current_terminal', $terminal);
                return $next($request);
            }
        }

        if ($token = $request->cookie('terminal_token')) {
            $terminal = Terminal::where('slug', $token)
                ->where('is_active', true)
                ->first();

            if ($terminal) {
                session(['terminal_id' => $terminal->id]);
                app()->instance('current_terminal', $terminal);
            }
        }

        return $next($request);
    }
}
