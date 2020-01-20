<?php

namespace Graphodata\GdPdfimport\Task;


use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class ImportRunner
{

    /**
     * @var \Graphodata\GdPdfimport\Parser\DOMDocumentTransducer
     */
    protected $transducer;

    /**
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;

    public function injectDataHandler(DataHandler $dataHandler): void
    {
        $this->dataHandler = $dataHandler;
    }

    public function __construct(DOMDocumentTransducer $transducer)
    {
        $this->transducer = $transducer;
    }

    public function run()
    {

    }
}