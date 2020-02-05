<?php

namespace shihjay2\tcpdi_merger;

#require_once("src/MyTCPDI.php");

class Merger{
    private $tcpdi;

    private $files = array();

    private $output = "";

    private $tempDir;

    public function __construct($showPagination = false){
        $this->tcpdi = new MyTCPDI($showPagination); //Default page format params
    }

    public function addRaw($pdf)
    {
        // Create temporary file
        $fname = $this->getTempFname();
        if (@file_put_contents($fname, $pdf) === false) {
            throw new Exception("Unable to create temporary file");
        }
        $this->addFromFile($fname, true);
    }


    public function addFromFile($fname, $cleanup = false)
    {
        if (!is_file($fname) || !is_readable($fname)) {
            throw new Exception("'$fname' is not a valid file");
        }
        $this->files[] = array($fname, $cleanup);
    }

    public function merge()
    {
        if (empty($this->files)) {
            throw new Exception("Unable to merge, no PDFs added");
        }
        $fname = '';
        try {
            $tcpdi = clone $this->tcpdi;
            foreach ($this->files as $fileData) {
                list($fname, $cleanup) = $fileData;

                $iPageCount = $tcpdi->setSourceFile($fname);
                // Add all pages
                $pages = range(1, $iPageCount);

                // Add specified pages
                foreach ($pages as $page) {
                    $template = $tcpdi->importPage($page);
                    $size = $tcpdi->getTemplateSize($template);
                    $orientation = ($size['w'] > $size['h']) ? 'L' : 'P';
                    $tcpdi->AddPage($orientation, array($size['w'], $size['h']));
                    $tcpdi->useTemplate($template);
                }
            }

            $output = $tcpdi->Output('', 'S');
            $tcpdi->cleanUp();
            foreach ($this->files as $fileData) {
                list($fname, $cleanup) = $fileData;
                if ($cleanup) {
                    unlink($fname);
                }
            }
            $this->files = array();
            $this->output = $output;
        } catch (Exception $e) {
            throw new Exception("FPDI: '{$e->getMessage()}' in '$fname'", 0, $e);
        }
    }

    public function getRawOutput()
    {
        return $this->output;
    }

    public function download($filename)
    {
        header("Content-Type: application/pdf");
        header('Content-Length: '.strlen($this->output));
        header('Content-disposition: attachment; filename="'.$filename.'"');
        echo $this->output;
    }

    public function save($path){
        file_put_contents($path, $this->output);
    }

    /**
     * Create temporary file and return name
     *
     * @return string
     */
    public function getTempFname()
    {
        return tempnam($this->getTempDir(), "pdfmerge");
    }
    /**
     * Get directory path for temporary files
     *
     * Set path using setTempDir(), defaults to sys_get_temp_dir().
     *
     * @return string
     */
    public function getTempDir()
    {
        return $this->tempDir ?: sys_get_temp_dir();
    }


    /**
     * Set directory path for temporary files
     *
     * @param  string $dirname
     * @return void
     */
    public function setTempDir($dirname)
    {
        $this->tempDir = $dirname;
    }
}

class Exception extends \Exception{}

?>
