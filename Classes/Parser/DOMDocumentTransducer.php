<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Parser;

use Graphodata\GdPdfimport\Exception\UnhandledNodeException;
use Graphodata\GdPdfimport\Exception\WrongStateException;
use Graphodata\GdPdfimport\Stack\Stack;
use Graphodata\GdPdfimport\Task\ImportRunner;
use Graphodata\GdPdfimport\Utility\NestingUtility;
use Graphodata\GdPdfimport\Utility\NodeTypes;
use Graphodata\GdPdfimport\Utility\NodeTypeUtility;

/**
 * Class DOMDocumentTransducer
 *
 * @package Graphodata\GdPdfimport\Parser
 */
final class DOMDocumentTransducer
{
    const DEBUG = 0;
    const ENT_DOC = 1;
    const LEA_DOC = -self::ENT_DOC;
    const ENT_CONTENT = 2;
    const LEA_CONTENT = -self::ENT_CONTENT;
    const ENT_WSECTION = 3;
    const LEA_WSECTION = -self::ENT_WSECTION;
    const ENT_TEXT = 4;
    const LEA_TEXT = -self::ENT_TEXT;
    const ENT_TABLE = 5;
    const LEA_TABLE = -self::ENT_TABLE;
    const ENT_IMG = 6;
    const LEA_IMG = -self::ENT_IMG;
    const ENT_LIST = 7;
    const LEA_LIST = -selF::ENT_LIST;
    const IGNORE = 404;

    const CE_TEXTMEDIA = 'ce_textmedia';
    const CE_TABLE = 'ce_table';

    const IMAGE_PATH = '/fileadmin/pdf_import/';
    const CHAPTER_REGEX = '/^\d\.(\d\.?){0,3}+/';
    const DATE_HEADER_REGEX = '/^(januar|februar|m(ä|&auml;)rz|april|mai|ju(n|l)i|august|september|oktober|november|dezember)\s?(1|2)\d{3}$/i';

    /**
     * TYPO3 pages
     *
     * @var array[]
     */
    protected $sectionBuffer = [];

    /**
     * Single CE on pages
     *
     * @var array[]
     */
    protected $subSectionBuffer = [];

    /**
     * Content of CE
     *
     * @var string[]
     */
    protected $contentBuffer = [];

    /**
     * @var string[]
     */
    protected $headerBuffer = [];

    /**
     * @var string
     */
    protected $wSectionFirstText = '';

    /**
     * @var string
     */
    protected $nextSectionTitle = '';

    /**
     * @var Stack
     */
    protected $contentStack;

    /**
     * @var Stack
     */
    protected $listStack;

    /**
     * @var stack
     */
    protected $listStyleStack;

    /**
     * @var bool
     */
    protected $firstChecked = false;

    /**
     * @var string
     */
    protected $currentCType = '';

    /**
     * @var bool
     */
    protected $insideListItem = false;

    /**
     * @var bool
     */
    protected $skipContent = false;

    /**
     * @var
     */
    public $currentNode;
    /**
     * @var
     */
    public $currentAction;
    /**
     * @var array
     */
    public $debugBuffer = [];

    /**
     * DOMDocumentTransducer constructor.
     */
    public function __construct()
    {
        $this->contentStack = new Stack();
        $this->listStack = new Stack();
        $this->listStyleStack = new Stack();
    }

    /**
     * @param \DOMDocument $DOMDocument
     * @return array
     * @throws \Graphodata\GdPdfimport\Exception\UnhandledNodeException
     * @throws \Graphodata\GdPdfimport\Exception\WrongStateException
     */
    public function transduce(\DOMDocument $DOMDocument): array
    {
        $transducedContent = [];

        foreach (Traverser::traverse($DOMDocument) as $item) {

            /**
             * @var \DOMNode $node
             */
            list($action, $node) = $item;

            $this->currentNode = $node;
            $this->currentAction = $action;

            if ($this->skipContent) {
                $this->skipContent = !$this->checkIfLeftListItem($action, $node);
                continue;
            } else if ($this->skipLineBreaksBetweenContent($node)) {
                continue;
            }

            $this->checkForListEnd($node);

            switch ($this->transduceAction($node, $action)) {
                /* ENTER ACTIONS */
                case self::ENT_CONTENT:
                    $this->handleContent($node);
                    break;
                case self::ENT_LIST:
                    $this->pushList($node);
                    $this->insertListItem($node);
                    break;
                case self::ENT_TABLE:
                    $this->newSubSection();
                    $this->setSubSectionType(self::CE_TABLE);
                    $this->pushNode($node);
                    $this->insertNode($node);
                    break;
                case self::ENT_WSECTION:
                    $this->checkStack(NodeTypes::DOCUMENT);
                    $this->contentStack->push(NodeTypes::SECTION);
                    $this->debugBuffer[]=['pushed' => NodeTypes::SECTION];
                    break;
                case self::ENT_DOC:
                    $this->pushNode($node);
                    break;
                case self::ENT_IMG:
                    break;
                case self::ENT_TEXT:
                    $this->insertTextNode($node);
                    break;

                /* LEAVE ACTIONS */
                case self::LEA_LIST:
                case self::LEA_CONTENT:
                    $this->popAndCheckStack($node->nodeName);
                    $this->insertNode($node, true);
                    break;
                case self::LEA_TABLE:
                    $this->popAndCheckStack($node->nodeName);
                    $this->insertNode($node, true);
                    $this->newSubSection();
                    break;
                case self::LEA_WSECTION:
                    $this->popAndCheckStack(NodeTypes::SECTION);
                    $this->wSectionFirstText = '';
                    break;
                case self::LEA_DOC:
                    $this->cleanupBuffers();
                    $transducedContent = NestingUtility::isolateNumbersForNesting($this->sectionBuffer);
                    break;
                case self::LEA_TEXT:
                    break;
                case self::LEA_IMG:
                    $this->insertImg($node);
                    break;

                /* OTHER ACTIONS */
                case self::IGNORE:
                    break;
                default:
                    throw new \Exception('Unknown return value: ' . $this->transduceAction($node, $action));
            }
        }
        return $transducedContent;
    }

    /**
     * @param \DOMNode $node
     * @param string   $action
     * @return int
     * @throws \Graphodata\GdPdfimport\Exception\UnhandledNodeException
     */
    protected function transduceAction(\DOMNode $node, string $action): int
    {
        if ($action === Traverser::ENTER) {
            if (NodeTypeUtility::isIgnoredNode($node)) {
                return self::IGNORE;
            } else if (NodeTypeUtility::isListBegin($node, $this->listStyleStack)) {
                return self::ENT_LIST;
            } else if (NodeTypeUtility::isRootNode($node->nodeName)) {
                return self::ENT_DOC;
            } else if (NodeTypeUtility::isNewSection($node, $this->contentStack)) {
                return self::ENT_WSECTION;
            } else if (NodeTypeUtility::isContent($node)) {
                return self::ENT_CONTENT;
            } else if (NodeTypeUtility::isText($node)) {
                return self::ENT_TEXT;
            } else if (NodeTypeUtility::isTable($node)) {
                return self::ENT_TABLE;
            } else if (NodeTypeUtility::isImg($node)) {
                return self::ENT_IMG;
            }
        } else if ($action === Traverser::LEAVE) {
            if (NodeTypeUtility::isIgnoredNode($node)) {
                return self::IGNORE;
            } else if (NodeTypeUtility::isListEnd($node, $this->listStack, $this->insideListItem)) {
                return self::LEA_LIST;
            } else if (NodeTypeUtility::isRootNode($node->nodeName)) {
                return self::LEA_DOC;
            } else if (NodeTypeUtility::isSectionEnd($node, $this->contentStack)) {
                return self::LEA_WSECTION;
            } else if (NodeTypeUtility::isContent($node)) {
                return self::LEA_CONTENT;
            } else if (NodeTypeUtility::isText($node)) {
                return self::LEA_TEXT;
            } else if (NodeTypeUtility::isTable($node)) {
                return self::LEA_TABLE;
            } else if (NodeTypeUtility::isImg($node)) {
                return self::LEA_IMG;
            }
        }
        throw new UnhandledNodeException("Node with name " . $node->nodeName . " of type " . $node->nodeType . " not known");
    }

    /**
     * @param string $expectedState
     * @throws \Graphodata\GdPdfimport\Exception\WrongStateException
     */
    protected function popAndCheckStack(string $expectedState): void
    {
        $this->debugBuffer[] = ['pop' => $this->contentStack->top()];
        if (($val = $this->contentStack->pop()) !== $expectedState)
            throw new WrongStateException("[POP] Expected state " . $expectedState . ", got " . $val);
    }

    /**
     * @param string $expectedState
     * @throws \Graphodata\GdPdfimport\Exception\WrongStateException
     */
    protected function checkStack(string $expectedState): void
    {
        if (($val = $this->contentStack->top()) !== $expectedState)
            throw new WrongStateException("[TOP] Expected state " . $expectedState . ", got " . $val);
    }

    /**
     * @param \DOMNode $node
     * @param bool     $closing
     */
    protected function insertNode(\DOMNode $node, bool $closing = false): void
    {
        $tag = '<' . ($closing ? '/' : '') . $node->nodeName . '>';
        $this->contentBuffer[] = $tag;
    }

    /**
     * @param \DOMNode $node
     */
    protected function insertListItem(\DOMNode $node): void
    {
        $tag = '<' . NodeTypes::LI . '>';
        $this->contentBuffer[] = $tag;
        $this->insertTextNode($node->childNodes->item(1));
        $tag = '</' . NodeTypes::LI . '>';
        $this->contentBuffer[] = $tag;
    }

    /**
     * @param \DOMNode $node
     */
    protected function insertTextNode(\DOMNode $node): void
    {
        $this->contentBuffer[] = htmlentities(utf8_decode($node->nodeValue), ENT_NOQUOTES, 'utf-8');
    }

    /**
     * @param \DOMNode $node
     */
    protected function insertImg(\DOMNode $node): void
    {
        $tag = '<img src="' . $this->createImagePath($node->getAttribute('src')) . '">';
        $this->contentBuffer[] = $tag;
    }

    /**
     * @param string $title
     */
    protected function newSection(string $title): void
    {
        $this->newSubSection();
        $this->sectionBuffer[$this->nextSectionTitle] = $this->subSectionBuffer;
        $this->nextSectionTitle = $title;
        $this->contentBuffer = $this->subSectionBuffer = [];
    }

    /**
     *
     */
    protected function newSubSection(): void
    {
        $type = $this->currentCType ?: self::CE_TEXTMEDIA;
        $this->subSectionBuffer[] = [
            'type' => $type,
            'bodytext' => implode($this->contentBuffer)
        ];
        $this->contentBuffer = [];
        $this->currentCType = '';
    }

    /**
     * @param \DOMNode $node
     */
    protected function pushNode(\DOMNode $node): void
    {
        $this->debugBuffer[] = ['pushed' => $node->nodeName];
        $this->contentStack->push($node->nodeName);
    }

    /**
     *
     */
    protected function cleanupBuffers(): void
    {
        $this->newSubSection();
        if ($this->subSectionBuffer) {
            $this->sectionBuffer['Fussnoten'] = $this->subSectionBuffer;
            $this->subSectionBuffer = [];
        }
    }

    /**
     * @param string $type
     */
    protected function setSubSectionType(string $type): void
    {
        $this->currentCType = $type;
    }

    /**
     * @param string $text
     */
    protected function checkFirstText(string $text): void
    {
        if ($this->wSectionFirstText === '') {
            $this->wSectionFirstText = ImportRunner::PART === 3 || ImportRunner::PART === 1
                ? trim(self::fixEncoding($text))
                : trim($text);
            if ($this->headerIsNewSection()) {
                $this->newSection($this->wSectionFirstText);
            }
        }

    }

    /**
     * @param string $originalPath
     * @return string
     */
    protected function createImagePath(string $originalPath): string
    {
        $filename = substr($originalPath, strrpos($originalPath, '/') + 1);
        return self::IMAGE_PATH . 'teil_' . ImportRunner::PART . '/' . $filename;
    }

    /**
     * @return bool
     */
    protected function headerIsNewSection(): bool
    {
        return preg_match(self::CHAPTER_REGEX, $this->wSectionFirstText) === 1;
    }

    /**
     * @param string $s
     * @return string
     */
    public static function fixEncoding(string $s): string
    {
        return htmlentities(utf8_decode($s));
    }

    /**
     * @param \DOMNode $node
     */
    protected function handleContent(\DOMNode $node): void
    {
        if ($this->listStack->isEmpty()) {
            $this->checkFirstText($node->nodeValue);
            $this->pushNode($node);
            $this->insertNode($node);
        } else {
            $this->insertListItem($node);
            $this->skipContent = true;
        }
    }

    /**
     * @param \DOMNode $node
     * @throws \Graphodata\GdPdfimport\Exception\UnhandledNodeException
     */
    protected function pushList(\DOMNode $node): void
    {
        $type = NodeTypeUtility::getListTagType($node);
                if (!$this->listStack->isEmpty()) $this->contentBuffer[] = '<li>';
        $this->contentBuffer[] = '<' . $type . '>';
        $this->listStack->push($type);
        $this->listStyleStack->push(NodeTypeUtility::getListStyle($node));
        $this->skipContent = true;
        $this->insideListItem = true;
    }

    /**
     * @param string   $action
     * @param \DOMNode $node
     * @return bool
     */
    protected function checkIfLeftListItem(string $action, \DOMNode $node): bool
    {
        return $action === Traverser::LEAVE
            && NodeTypeUtility::isParagraph($node);
    }

    /**
     * @param \DOMNode $node
     */
    private function checkForListEnd(\DOMNode $node)
    {
        if (!$this->listStack->isEmpty()
            && !NodeTypeUtility::childNodesMatchList($node)
        ) {
            $this->closeAllListTags();
        }
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    private function skipLineBreaksBetweenContent(\DOMNode $node)
    {
        return !$this->contentStack->isEmpty()
            && $this->contentStack->top() === NodeTypes::SECTION
            && trim($node->nodeValue) === ''
            && $node->nodeName === NodeTypes::TEXT;
    }

    /**
     *
     */
    private function closeAllListTags(): void
    {
        foreach ($this->listStack->iterateDown() as $type) {
            $this->contentBuffer[] = '</' . $type . '>';
            $this->contentBuffer[] = '</li>';
        }
        array_pop($this->contentBuffer);
        $this->listStack->clear();
        $this->listStyleStack->clear();
        $this->insideListItem = false;
    }
}