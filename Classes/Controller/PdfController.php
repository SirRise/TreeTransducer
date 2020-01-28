<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Controller;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;
use Graphodata\GdPdfimport\Task\ImportRunner;
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

    public function showAction(): void
    {
        GeneralUtility::makeInstance(ImportRunner::class, $this->transducer)->run();
    }

}