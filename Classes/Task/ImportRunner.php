<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Task;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;
use Graphodata\GdPdfimport\Utility\PageUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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

    const PART = 1;
    const CREATE_CONTENT = true;

    const PDFs = [
        '/lists.html',
        '/PDF_1.html',
        '/PDF_2.html',
        '/PDF_3.html',
    ];

    const ROOTPAGES = [
        2,
        3,
        4
    ];

    public function run(): void
    {
        $pdf = file_get_contents(Environment::getPublicPath() . self::PDFs[self::PART - 1]);
        $domDocument = new \DOMDocument();
        $domDocument->loadHTML($pdf);
        $domDocument->normalize();
        try {
            $pageUtility = GeneralUtility::makeInstance(
                PageUtility::class,
                $this->transducer->transduce($domDocument),
                self::ROOTPAGES[self::PART - 1]
            );
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($this->transducer->debugBuffer);
            echo $e->getMessage();
            die;
        }
//        DebuggerUtility::var_dump($pageUtility->getPages());
        $pageUtility->createPages(self::CREATE_CONTENT);
    }
}