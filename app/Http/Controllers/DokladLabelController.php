<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DokladLabelController extends Controller
{
    public function show(Request $request)
    {
        $id = $request->input('id');
        
        if (!$id) {
            abort(404, 'Doklad ID missing');
        }
        
        $qrCode = base64_encode(QrCode::format('png')->size(200)->margin(0)->generate($id));
        
        $data = [
            'id' => $id,
            'qrCode' => $qrCode,
            'author' => Auth::user()->name,
            'date' => date('d.m.Y H:i')
        ];
        
        $customPaper = [0, 0, 175.75,100]; // 62mm x 40mm
        
        $pdf = Pdf::loadView('pdf.pdf-label', $data)
             ->setPaper($customPaper); 

        $safeId = str_replace(['/', '\\'], '-', $id);

        
        
        // Ujisti se, že adresář existuje
        Storage::disk('local')->makeDirectory('public/labels');

        // Relativní cesta v storage/app
        $relativePath = 'public/labels/label-' . $safeId . '.pdf';

        // Absolutní cesta (pro tiskovou službu)
        $absolutePath = storage_path('app/' . $relativePath);

                // stream pdf to user
       //return $pdf->stream("label-$safeId.pdf");

        $pdf->save($absolutePath);



        echo $absolutePath;

    Http::withHeaders([
        'Accept' => 'application/json',
    ])
    ->attach(
        'file',
        file_get_contents($absolutePath),
        basename($absolutePath)
    )
    ->post(config('services.print.url') . '/print', [
        'printer' => 'default',
    ]);
    }
}
