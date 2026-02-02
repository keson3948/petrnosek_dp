<?php

namespace App\Http\Controllers;

use App\Models\Prostredek;
use Illuminate\Http\Request;

class ProstredekController extends Controller
{
    public function index()
    {
        $prostredky = Prostredek::dbcnt(730550)
            ->orderBy('KlicProstredku')
            ->limit(30)
            ->get();

        return view('prostredky.index', compact('prostredky'));
    }
}
