<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Polozka;

class StaPoController extends Controller
{
    public function index()
    {
        $polozky = Polozka::with(['stavDokladu', 'staPo'])
            ->stavPolozkyNot('939073-XX')
            ->skupinaIn(['OPERACE'])
            ->orderBy('KlicPoloz')
            ->get();

        return view('stapo.index', compact('polozky'));
    }
}
