<?php

namespace Graphodata\GdPdfimport\Controller;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PdfController extends ActionController
{
    /**
     * @var \Graphodata\GdPdfimport\Parser\DOMDocumentTransducer|object
     */
    protected $transducer;

    /**
     * @var string
     */
    protected $pdf;

    /**
     * @var \DOMDocument
     */
    protected $domDocument;

    public function injectDOMDocumentTransducer(DOMDocumentTransducer $transducer)
    {
        $this->transducer = $transducer;
    }

    public function __construct()
    {
//        $this->pdf = file_get_contents(Environment::getPublicPath() . '/PDF_1_stripped.html');
        $this->pdf = file_get_contents(Environment::getPublicPath() . '/parsetest.html');
        $this->domDocument = new \DOMDocument();

    }

    public function showAction()
    {
        echo '<pre>';
        $this->domDocument->loadHTML($this->pdf);
        $this->domDocument->normalize();
        $this->transducer->transduce($this->domDocument);
    }

}