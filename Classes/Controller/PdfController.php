<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Controller;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;
use Graphodata\GdPdfimport\Task\ImportRunner;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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

    public function injectDOMDocumentTransducer(DOMDocumentTransducer $transducer): void
    {
        $this->transducer = $transducer;
    }

    public function __construct()
    {
//        $this->pdf = file_get_contents(Environment::getPublicPath() . '/PDF_1.html');
        $this->pdf = file_get_contents(Environment::getPublicPath() . '/PDF_2_stripped.html');
        $this->domDocument = new \DOMDocument();
    }

    public function showAction(): void
    {
        GeneralUtility::makeInstance(ImportRunner::class, $this->transducer)->run();
    }

}