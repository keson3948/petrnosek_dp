<?php

namespace App\Http\Controllers;

use App\Models\DruhSubjektu;
use App\Models\Subjekt;
use Illuminate\Http\Request;

class DruhSubjektuController extends Controller
{
    public function index()
    {
        $druhySubjektu = DruhSubjektu::limit(20)->get();
        return view('druh-subjektu.index', compact('druhySubjektu'));
    }

    public function create()
    {
        $subjekty = DruhSubjektu::find("10")->subjekty()->limit(10)->get();
        dd($subjekty);
    }
}
