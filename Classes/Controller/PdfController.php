<?php

namespace Graphodata\GdPdfimport\Controller;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;
use Graphodata\GdPdfimport\Utility\PageUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

    /**
     * @var DataHandler
     */
    protected $dataHandler;

    protected $pageUtility;

    public function injectDOMDocumentTransducer(DOMDocumentTransducer $transducer): void
    {
        $this->transducer = $transducer;
    }

    public function injectDataHandler(DataHandler $dataHandler): void
    {
        $this->dataHandler = $dataHandler;
    }

    public function __construct()
    {
//        $this->pdf = file_get_contents(Environment::getPublicPath() . '/PDF_1_shortened.html');
//        $this->pdf = file_get_contents(Environment::getPublicPath() . '/parsetest.html');
//        $this->pdf = file_get_contents(Environment::getPublicPath() . '/PDF_1_notsoshort.html');
        $this->pdf = file_get_contents(Environment::getPublicPath() . '/PDF_1.html');
        $this->domDocument = new \DOMDocument();
    }

    public function showAction(): void
    {
        $this->domDocument->loadHTML($this->pdf);
        $this->domDocument->normalize();
//        $this->pageUtility = GeneralUtility::makeInstance(
//            PageUtility::class,
//            $this->transducer->transduce($this->domDocument)
//        );
        $this->view->assign('parsedContent', $this->transducer->transduce($this->domDocument));
    }

    public function dataAction(): void
    {
        $data = [];
        $cmd = [];
        $this->dataHandler->start($data, $cmd);
        $this->dataHandler->process_cmdmap();
    }

}