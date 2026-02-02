<?php

namespace App\Http\Controllers;

use App\Models\Polozka;
use Illuminate\Http\Request;

class PolozkaController extends Controller
{
    public function index()
    {
        $polozky = Polozka::limit(40)->get();
        return view('polozka.index', compact('polozky'));

    }

    public function store()
    {
        $klic = \Illuminate\Support\Str::random(10);
        
        Polozka::create([
            'KlicPoloz' => $klic,
            'TdfDocType' => '305019',
            'DruhPolozky' => 'PZ',
            'Nazev1' => 'Test Polozka ' . $klic,
            'Skupina' => '6',
            'ZaklaJedn' => 'ks',
            'PocDesetinyMiZaklaMeJedno' => 0,
            'PoloNepou' => 0,
            'DanovaSkupina' => '117',
            'VerzePolozek' => 0,
            'MernaJednotkaHmotnosti' => 'kg',
            'PoDesetinnMiMeJednoHmotno' => 3,
            'NettoVMJHmotnosti' => 0,
            'PoJednoVO' => 0,
            'HmotnoObVMJHmo' => 0,
            'BruttoVMJHmotnosti' => 0,
            'SpecifickaHmotnost' => 0,
            'RozmerAMJ' => '~',
            'RozmerAHodnota' => 0,
            'RozmerBMernaJednotka' => '~',
            'RozmerBHodnota' => 0,
            'RozmerCMernaJednotka' => '~',
            'RozmerCHodnota' => 0,
            'ObjemM3' => 0,
            'ZatrizeniPolozkyADruh' => '~',
            'ZatrizeniPolozkyAKod' => '~',
            'ZatrizeniPolozkyBDruh' => '~',
            'ZatrizeniPolozkyBKod' => '~',
            'CenovaJednotka' => 1,
            'ProdejnCe' => 0,
            'MaloobchC' => 0,
            'PrijmovCe' => 0,
            'ZaSlAPrir' => 0,
            'Poznamka' => '',
            'Material' => 'TEST',
            'PovrchovaUprava' => 'TEST',
            'DrCarkovK' => 0,
            'PoVlasEAK' => '~',
            'CarkovyKo' => '',
            // 'SYSTIMEST' => now(), // Firebird might handle this or fail if format is wrong
            // 'CTSMP' => now(),
            'UPUSR' => 1,
            'CUSR' => 1,
            'UPCNT' => 0,
            'DBCNTID' => 1,
            'DbCntNum' => 0,
            'OwnSiteNum' => 0,
            'CRC' => 0,
            'DocId' => rand(10000, 99999), // Assuming this needs to be unique-ish
            'StatCelnihoSazebniku' => '~',
            'KodCelnihoSazebniku' => '~',
            'KodSKP' => '',
            'KodDPHNakTuz' => '~',
            'KodDPHNakZahr' => '~',
            'KodDPHProdTuz' => '~',
            'KodDPHProdZahr' => '~',
            'MarketingovyKod' => '',
            'MistUloze' => '~',
            'VyjmZRozpoustSouvisejiNak' => 0,
            'KlicPolozkyPuvodni' => '',
            'Sestava' => 0,
            'SkupinaSkladu' => '~',
            'HlavniSklad' => '~',
            'SkladProPrijem' => '~',
            'SkladProVydej' => '~',
            'DruhUmisteni' => '~',
            'Umisteni' => '~',
            'MernaJednotkaNakupni' => '~',
            'KoefPrepMJNakNaZakl' => 0,
            'MinimalniStav' => 0,
            'MaximalniStav' => 0,
            'OptimalniStav' => 0,
            'HlavniDodavatel' => '~',
            'PosledPrijCeNaHlavSkl' => 0,
            'PrijmCenHlavDodavate' => 0,
            'UctoSkupina' => '~',
            'NedelitelnaJednotka' => '~',
            'Vyrabet' => 0,
            'CisloVykresu' => '',
            'PoziceVykresu' => '',
            'RozsahPozic' => '',
            'DruhZaruky' => 0,
            'DelkaZaruky' => 0,
            'PouziSeVKonsignacniSklade' => 0,
            'Dodavat' => 0,
            'Fakturova' => 0,
            'Objednava' => 0,
            'NepoPrVyP' => 0,
            'PoSkNaVPo' => 0,
            'Ucet' => '~',
            'PocetProdejnichCen' => 1,
            'PocetNakupnichCen' => 0,
            'InterniPoznamka' => '',
            'ZobrazovanyNazev' => 'Test Polozka ' . $klic,
            'ZkracenyNazevVyhledavaci' => 'test polozka ' . $klic,
            'VyjmoutZIntrastatu' => 0,
            'KlicPolozkyVyhledavaci' => '',
            'MernaJednotkaRozliseni' => '',
            'VyzadovatRozliseniPolozky' => 0,
            'StatusPolozky' => '',
            'KlicPolozkyNovy' => '',
            'Obal' => '',
            'PolozkaSouvisejicicNaklad' => 0,
            'MetodaNormy' => 0,
            'ProcentoInventurnihSchodk' => 0,
            'ProcentInventurnihPrebytk' => 0,
            'MnozstviZakladnyZMJ' => 0,
            'MnozstviRozdiluZMJ' => 0,
            'VztahnoutINaCiziSklady' => 0,
            'PRODCZECH' => '',
            'PrednastaDrOrganizacStruk' => '',
            'StavPolozkyNakupuAProdeje' => '',
            'KlicSkupinyNovy' => '',
            'PlochaProPovrchovoUpravMJ' => '',
            'PlochaProPovrchovouUpravu' => 0,
            'VychoziZpusobPrijmuPolozk' => '',
            'StdDataPkgId' => '',
            'StdDataPkgVer' => -1,
            'ZakazatDuplicitCislVykres' => 0,
            'TvaroveCislo' => '',
            'MinimalniDavka' => 0,
            'MaximalniDavka' => 0,
            'OptimalniDavka' => 0,
            'Stroj' => '',
            'DruhStroju' => '',
            'SkupinaStroju' => '',
            'Varianta' => '',
            'Sablona' => 0,
            'SablonaPolozka' => '',
            'SablonaVarianta' => '',
            'JeSadouProPolozku' => '',
            'JeSadouProVariantuPolozky' => '',
            'ZpracovanePrednastavenSad' => '',
            'VygenerovanaZakazkaUcetni' => '',
        ]);

        return back()->with('success', 'Položka byla úspěšně vytvořena: ' . $klic);
    }
    public function deleteForm()
    {
        return view('polozka.delete-form');
    }

    public function destroyById(Request $request)
    {
        $request->validate([
            'KlicPoloz' => 'required|string',
        ]);

        $polozka = Polozka::where('KlicPoloz', $request->KlicPoloz)->first();

        if ($polozka) {
            $polozka->delete();
            return back()->with('success', 'Položka byla úspěšně smazána: ' . $request->KlicPoloz);
        }

        return back()->with('error', 'Položka s tímto ID nebyla nalezena: ' . $request->KlicPoloz);
    }
}
