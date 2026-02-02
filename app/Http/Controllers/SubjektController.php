<?php

namespace App\Http\Controllers;

use App\Models\Subjekt;
use Illuminate\Http\Request;

class SubjektController extends Controller
{
    public function index()
    {
        $subjekty = Subjekt::with('funkce')
            ->tdfDocType(495031)
            ->funkce('0300')
            ->stavSubjektuNot('748367-XX')
            ->orderBy('KlicSubjektu')
            ->limit(10)
            ->get();

        return view('subjekt.index', compact('subjekty'));
    }
}
