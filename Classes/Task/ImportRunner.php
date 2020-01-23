<?php

namespace Graphodata\GdPdfimport\Task;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;
use Graphodata\GdPdfimport\Utility\PageUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImportRunner
{

    /**
     * @var DOMDocumentTransducer
     */
    protected $transducer;

    public function __construct(DOMDocumentTransducer $transducer)
    {
        $this->transducer = $transducer;
    }

    public function run(): void
    {
        $pdf = file_get_contents(Environment::getPublicPath() . '/PDF_2_stripped.html');
//        $pdf = file_get_contents(Environment::getPublicPath() . '/PDF_1.html');
        $domDocument = new \DOMDocument();
        $domDocument->loadHTML($pdf);
        $domDocument->normalize();
        $pageUtility = GeneralUtility::makeInstance(
            PageUtility::class,
            $this->transducer->transduce($domDocument)
        );
        $pageUtility->createPages();
    }
}