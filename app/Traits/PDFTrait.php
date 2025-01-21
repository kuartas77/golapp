<?php


namespace App\Traits;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

trait PDFTrait
{
    protected $mpdf;
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
        80, 80
    ];

    /**
     * @param array $configuration
     */
    public function setConfigurationMpdf($configuration = array())
    {
        $this->configDefault = array_merge($this->configDefault,
            ['tempDir' => storage_path('app/tmp')],
            $configuration
        );
    }

    public function setWatermarkSize($size = array(80, 80))
    {
        $this->configWatermarkSize = $size;
    }

    /**
     * Get instance mpdf
     * @return static
     */
    public function getMpdf(): PDFTrait
    {
        return $this->mpdf;
    }

    /**
     * Save the PDF to a file
     *
     * @param $filename
     */
    public function save($filename)
    {
        $this->mpdf->Output($filename, Destination::FILE);
    }

    /**
     * Output the PDF as a string.
     *
     * @return string The rendered PDF as string
     */
    public function output($filename): string
    {
        return Storage::disk('public')->put("{$filename}.pdf", $this->mpdf->Output(null, Destination::STRING_RETURN));
    }

    /**
     * Make the PDF downloadable by the user
     *
     * @param string $filename
     */
    public function download(string $filename = 'document.pdf')
    {
        return $this->mpdf->Output($filename, Destination::DOWNLOAD);
    }

    /**
     * Return a response with the PDF to show in the browser
     *
     * @param string $filename
     */
    public function stream(string $filename = 'document.pdf')
    {
        return $this->mpdf->Output($filename, Destination::INLINE);
    }

    public function returnFile($file)
    {
        //This method will look for the file and get it from drive
        $path = storage_path('app/tmp/' . $file);
        try {
            $file = File::get($path);
            $type = File::mimeType($path);
            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);

        } catch (FileNotFoundException $exception) {
            abort(404);
        }
        return $response;
    }

    /**
     * Create pdf.
     *
     * @param array $data
     * @throws MpdfException
     */
    protected function createPDF(array $data, string $template, $showFooter = true)
    {
        $this->mpdf = new Mpdf($this->configDefault);

        if ($data['school']) {
            $this->mpdf->SetAuthor($data['school']->name);
            $this->mpdf->SetWatermarkImage($data['school']->logo_local, -1, $this->configWatermarkSize);
            $this->mpdf->showWatermarkImage = true;
        }
        $this->mpdf->SetCreator('GOLAPP');
        $this->mpdf->WriteHTML(view()->file($this->getTemplate($template), $data));
        if($showFooter) {
            $this->mpdf->SetHTMLFooter(view()->file($this->getTemplate('footer.blade.php'), $data));
        }
    }

    /**
     * Get template.
     *
     * @param string $template
     * @return string
     */
    private function getTemplate(string $template): string
    {
        return resource_path("templates/pdf/{$template}");
    }

    /**
     * Exists template.
     *
     * @param string $path
     *
     * @return bool
     */
    private function existsTemplate(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }
}
