<?php

namespace Graphodata\GdPdfimport\Task;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;
use Graphodata\GdPdfimport\Utility\NestingUtility;
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

    const PART = 3;
    const FULL_IMPORT = false;
    const CREATE_CONTENT = false;

    const PDFs = [
        '/PDF_1.html',
        '/PDF_2_stripped.html',
        '/PDF_3.html'
    ];

    public function run(): void
    {

        if (self::FULL_IMPORT) {
            foreach ([1,2,3] as $x) {
                $pdf = file_get_contents(Environment::getPublicPath() . self::PDFs[$x - 1]);
                $domDocument = new \DOMDocument();
                $domDocument->loadHTML($pdf);
                $domDocument->normalize();
                $pageUtility = GeneralUtility::makeInstance(
                    PageUtility::class,
                    $this->transducer->transduce($domDocument),
                    $x + 1
                );
                $pageUtility->createPages(self::CREATE_CONTENT);
                NestingUtility::$cache = [];
            }
        } else {
            $pdf = file_get_contents(Environment::getPublicPath() . self::PDFs[self::PART - 1]);
            $domDocument = new \DOMDocument();
            $domDocument->loadHTML($pdf);
            $domDocument->normalize();
            $pageUtility = GeneralUtility::makeInstance(
                PageUtility::class,
                $this->transducer->transduce($domDocument),
                self::PART + 1
            );
            $pageUtility->createPages(self::CREATE_CONTENT);
        }
    }
}