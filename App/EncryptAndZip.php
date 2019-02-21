<?php

namespace Moshe;

use Chumper\Zipper\Zipper;
use NFePHP\eFinanc\Common\Crypto;

class EncryptAndZip
{
    private $cryptozip;
    private $zipper;
    private $output;
    
    public function __construct()
    {
        $this->output = date('YmdHis').'.zip';
        $this->criptozip = 'storage/'.$this->output;
        
        $this->zipper = new Zipper();
        $this->zipper->make($this->criptozip);
    }
    
    public function __destruct()
    {
        $this->zipper->close();
    }
    
    public function process($zipfile)
    {
        $origZip = new Zipper();
        $files = $origZip->make($zipfile)->listFiles();
        foreach ($files as $file) {
            $content = $origZip->getFileContent($file);
            //é um xml
            if ($this->isXML($content)) {
                //é um evento efinanceira
                if ($this->isEvent($content)) {
                    //está assinado
                    if ($this->isSigned($content)) {
                        $encripted = $this->encrypt($content);
                        $this->pack($file, $encripted);
                    }
                }
            }
        }
        return $this->output;
    }
    
    private function isXML($content)
    {
        return true;
    }
    
    private function isEvent($content)
    {
        return true;
    }
    
    private function isSigned($content)
    {
        return true;
    }

    private function pack($filename, $content)
    {
        $this->zipper->addString($filename, $content);
    }

    private function encrypt($content)
    {
        $content = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $content);
        $xml = "<eFinanceira "
            . "xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" "
            . "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" "
            . "xmlns=\"http://www.eFinanceira.gov.br/schemas/envioLoteEventos/v1_2_0\">";
        $iCount = 0;
        $lote = date('YmdHms');
        $xml .= "<loteEventos>";
        $xml .= "<evento id=\"ID" . $iCount . "\">";
        $xml .= $content;
        $xml .= "</evento>";
        $xml .= "</loteEventos>";
        $xml .= "</eFinanceira>";

        $cer = file_get_contents(realpath(__DIR__.'/../vendor/nfephp-org/sped-efinanceira/storage/efinanc_web.cer'));
        $crypt = new Crypto($cer);
        $resp = $crypt->certificateInfo();
        $id = 1;// round(microtime(true) * 1000);
        $key = $crypt->getEncrypedKey();
        $fingerprint = $crypt->getThumbprint();
        $crypted = $crypt->encryptMsg($xml);
        $cryptoXML = "<eFinanceira xmlns=\"http://www.eFinanceira.gov.br/schemas"
            . "/envioLoteCriptografado/v1_2_0\">"
            . "<loteCriptografado>"
            . "<id>$id</id>"
            . "<idCertificado>$fingerprint</idCertificado>"
            . "<chave>$key</chave>"
            . "<lote>$crypted</lote>"
            . "</loteCriptografado>"
            . "</eFinanceira>";
        return $cryptoXML;
    }

}
