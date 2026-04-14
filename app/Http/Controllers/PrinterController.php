<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function index()
    {
        return view('admin.printer.index');
    }
}
