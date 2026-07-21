<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductPage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class ConvertProductPdfJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public Product $product)
    {
    }

    public function handle(): void
    {
        $pdfFullPath = Storage::disk('local')->path($this->product->pdf_path);
        // Private disk — page images are only ever served through an authenticated
        // route (source-code's BookPageImageController), never as a public static file.
        $outputDir = storage_path("app/product-pages/{$this->product->id}");

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $process = new Process(
            command: [
                config('services.imagemagick.binary'),
                '-density', '150',
                $pdfFullPath,
                $outputDir.DIRECTORY_SEPARATOR.'page-%d.png',
            ],
            env: [
                // ImageMagick shells out to Ghostscript for PDF pages — make sure it's
                // findable even if the system PATH change hasn't reached this process yet.
                'PATH' => getenv('PATH').PATH_SEPARATOR.config('services.imagemagick.ghostscript_bin'),
            ],
        );
        // No timeout — large books (685+ pages) can take well over 10 minutes,
        // especially with other dev processes competing for CPU. This runs in a
        // background job, so nothing user-facing is blocked while it works.
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->product->update(['pdf_conversion_status' => 'failed']);

            Log::error('Product PDF conversion failed', [
                'product_id' => $this->product->id,
                'error' => $process->getErrorOutput(),
            ]);

            return;
        }

        $this->product->pdfPages()->delete();

        $pages = collect(glob($outputDir.DIRECTORY_SEPARATOR.'page-*.png'))
            ->sortBy(fn (string $path) => (int) preg_replace('/\D/', '', basename($path)))
            ->values();

        foreach ($pages as $index => $path) {
            [$width, $height] = getimagesize($path) ?: [null, null];

            ProductPage::create([
                'product_id' => $this->product->id,
                'page_number' => $index + 1,
                'image_path' => "product-pages/{$this->product->id}/".basename($path),
                'width' => $width,
                'height' => $height,
            ]);
        }

        $this->product->update([
            'pdf_conversion_status' => 'ready',
            'pdf_page_count' => $pages->count(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->product->update(['pdf_conversion_status' => 'failed']);

        Log::error('Product PDF conversion job failed', [
            'product_id' => $this->product->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
