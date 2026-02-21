<?php

namespace App\Jobs;

use App\Models\Printer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PrintLabelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Zkusit 3x při chybě

    protected $printerId;
    protected $filePath;
    protected $copies;

    public function __construct($printerId, $filePath, $copies = 1)
    {
        $this->printerId = $printerId;
        $this->filePath = $filePath;
        $this->copies = $copies;
    }

    public function handle()
    {
        $printer = Printer::find($this->printerId);

        if (!$printer || !Storage::exists($this->filePath)) {
            Log::error("Tisk selhal: Tiskárna nebo soubor neexistuje.");
            return;
        }

        $url = config('services.print_server.url', 'http://host.docker.internal:9100/print');

        $response = Http::attach(
            'file', Storage::get($this->filePath), 'label.pdf'
        )->post($url, [
            'printer_system_name' => $printer->system_name,
            'copies' => $this->copies,
            'page_size' => $printer->page_size,
            'orientation' => $printer->orientation,
            'media_type' => $printer->media_type,
        ]);

        if ($response->failed()) {
            throw new \Exception("Python Print Service Error: " . $response->body());
        }

        Storage::delete($this->filePath);
    }
}
