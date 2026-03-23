<?php

namespace App\Http\Controllers;

use App\Models\Prostredek;
use Illuminate\Http\Request;

class ProstredekController extends Controller
{
    public function index()
    {
        $prostredky = Prostredek::dbcnt(730550)
            ->where('KlicProstredku', 'like', '20%')
            ->orderBy('KlicProstredku')
            ->get();

        return view('prostredky.index', compact('prostredky'));
    }
}
