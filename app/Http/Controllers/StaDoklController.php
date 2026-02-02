<?php

namespace App\Http\Controllers;

use App\Models\Doklad;
use App\Models\StaDokl;
use App\Models\Subjekt;
use Illuminate\Http\Request;

class StaDoklController extends Controller
{
    public function index()
    {
        $staDoklady = StaDokl::with(['doklad.vlastniOsoba', 'doklad.rodicZakazka'])
            ->typPohybu('EC_ZAKVYR')
            ->vyhodnoceni(1)
            ->whereHas('doklad', function ($query) {
                $query->tdfDocType(410008)
                    ->dbcnt(10904)
                    ->docYear(2025)
                    ->where('VlastniOsoba', auth()->user()->klic_subjektu);
            })
            ->orderBy(
                Doklad::select('TerminDatum')
                    ->whereColumn('ecd_Dokl.SysPrimKlicDokladu', 'ecd_StaDokl.Doklad')
            , 'asc')
            ->get();
            
        return view('stadokl.index', compact('staDoklady'));
    }


    public function show($id)
    {
        // Decode ID if necessary or just use it. Firebird might have spaces.
        // We match valid records same as index: TypPohybu = EC_ZAKVYR
        $staDokl = StaDokl::with(['doklad.vlastniOsoba', 'doklad.rodicZakazka'])
            ->where('Doklad', $id)
            ->typPohybu('EC_ZAKVYR') 
            ->firstOrFail();

        return view('stadokl.show', compact('staDokl'));
    }

}
