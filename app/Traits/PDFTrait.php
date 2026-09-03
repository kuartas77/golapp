<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;

trait PDFTrait
{
    protected Mpdf $mpdf;

    /**
     * Config mpdf.
     *
     * @var array
     */
    protected $configDefault = [
        'mode' => 'utf-8',
        'margin_left' => 3,
        'margin_right' => 3,
        'margin_top' => 4,
        'margin_bottom' => 4,
        'margin_header' => 4,
        'margin_footer' => 4,
    ];

    protected $configWatermarkSize = [
        40, 40,
    ];

    /**
     * @param  array  $configuration
     */
    public function setConfigurationMpdf($configuration = [])
    {
        $this->configDefault = array_merge($this->configDefault,
            ['tempDir' => storage_path('app/tmp')],
            $configuration
        );
    }

    public function setWatermarkSize($size = [80, 80])
    {
        $this->configWatermarkSize = $size;
    }

    /**
     * Get instance mpdf
     *
     * @return static
     */
    public function getMpdf()
    {
        return $this->mpdf;
    }

    /**
     * Save the PDF to a file
     */
    public function save(string $filename)
    {
        $this->mpdf->Output($filename, Destination::FILE);
    }

    /**
     * Output the PDF as a string.
     *
     * @return string The rendered PDF as string
     */
    public function output(string $filename): string
    {
        return Storage::disk('public')->put("{$filename}.pdf", $this->mpdf->Output(null, Destination::STRING_RETURN));
    }

    /**
     * Make the PDF downloadable by the user
     */
    public function download(string $filename = 'document.pdf')
    {
        $content = $this->mpdf->Output(null, Destination::STRING_RETURN);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.str_replace('"', '', $filename).'"',
        ]);
    }

    /**
     * Return a response with the PDF to show in the browser
     */
    public function stream(string $filename = 'document.pdf')
    {
        $content = $this->mpdf->Output(null, Destination::STRING_RETURN);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $filename).'"',
        ]);
    }

    /**
     * Create pdf.
     *
     * @throws MpdfException
     */
    protected function createPDF(array $data, string $template, $showFooter = true, $mark = true)
    {
        $this->mpdf = new Mpdf($this->configDefault);

        if (isset($data['school'])) {
            $this->mpdf->SetAuthor($data['school']->name);
            if ($mark) {
                $this->mpdf->SetWatermarkImage($data['school']->logo_local, -1, $this->configWatermarkSize);
                $this->mpdf->showWatermarkImage = $mark;
            }
        }

        $this->mpdf->SetCreator('GOLAPP');
        $this->mpdf->WriteHTML(view()->file($this->getTemplate($template), $data));

        if (isset($data['school']) && $showFooter) {
            $this->mpdf->SetHTMLFooter(view()->file($this->getTemplate('footer.blade.php'), $data));
        }
    }

    /**
     * Get template.
     */
    private function getTemplate(string $template): string
    {
        return resource_path("views/templates/pdf/{$template}");
    }

    /**
     * Exists template.
     */
    private function existsTemplate(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }
}
