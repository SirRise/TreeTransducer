<?php

namespace Graphodata\GdPdfimport\Controller;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extensionmanager\Utility\FileHandlingUtility;

class PdfController extends ActionController
{
    /**
     * @var \Graphodata\GdPdfimport\Parser\DOMDocumentTransducer|object
     */
    protected $parser;

    /**
     * @var string
     */
    protected $pdf;

    /**
     * @var string
     */
    protected $parsedContent;


    /**
     * @var array
     */
    protected $ruleSet = [

    ];

    /**
     * @var \DOMDocument
     */
    protected $domDocument;

    public function injectDOMDocumentParser(DOMDocumentTransducer $parser)
    {
        $this->parser = $parser;
    }

    public function __construct()
    {
//        $this->pdf = file_get_contents(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/PDF_1.html');
        $this->pdf = file_get_contents(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/parsetest.html');
        $this->domDocument = new \DOMDocument();
    }

    public function showAction()
    {
        echo '<pre>';
        $this->domDocument->loadHTML($this->pdf);
        $this->domDocument->normalize();
        $this->parser->parse($this->domDocument);
    }

}