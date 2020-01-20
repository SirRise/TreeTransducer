<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Parser;

use Graphodata\GdPdfimport\Exception\UnhandledNodeException;
use Graphodata\GdPdfimport\Exception\WrongStateException;
use Graphodata\GdPdfimport\Utility\NestingUtility;
use Graphodata\GdPdfimport\Utility\NodeTypes;
use Graphodata\GdPdfimport\Utility\NodeTypeUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Graphodata\GdPdfimport\Utility\PageUtility;

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

    const IGNORE = 404;
    const WRONG_STATE = 403;

    const CE_TEXTMEDIA = 'ce_textmedia';
    const CE_TABLE = 'ce_table';

    const CHAPTER_REGEX = '/^\d\.(\d\.?){0,3}+/';

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
     * @var \SplStack
     */
    protected $stack;

    /**
     * @var bool
     */
    protected $firstChecked = false;

    /**
     * @var string
     */
    protected $currentCType = '';

    public function __construct()
    {
        $this->stack = new \SplStack();
    }

    public function transduce(\DOMDocument $DOMDocument): array
    {
        foreach(Traverser::traverse($DOMDocument) as $item) {

            list($action, $node) = $item;

            switch ($this->transduceAction($node, $action))
            {
                /* ENTER ACTIONS */
                case self::ENT_CONTENT:
                    $this->checkFirstText(utf8_decode($node->textContent));
                    $this->pushNode($node);
                    $this->insertNode($node);
                    break;
                case self::ENT_TABLE:
                    $this->newSubSection();
                    $this->setSubSectionType(self::CE_TABLE);
                    $this->pushNode($node);
                    $this->insertNode($node);
                    break;
                case self::ENT_WSECTION:
                    $this->checkStack(NodeTypes::DOCUMENT);
                    $this->stack->push(NodeTypes::SECTION);
                    $this->firstChecked = false;
                    break;
                case self::ENT_DOC:
                    $this->pushNode($node);
                    break;
                case self::ENT_IMG: break;
                case self::ENT_TEXT: break;

                /* LEAVE ACTIONS */
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
                    $this->handleSectionEnd();
                    $this->wSectionFirstText = '';
                    break;
                case self::LEA_DOC:
                    $this->cleanupBuffers();
                    return NestingUtility::isolateNumbersForNesting($this->sectionBuffer);
                    break;
                case self::LEA_TEXT:
                    $this->insertTextNode($node);
                    break;
                case self::LEA_IMG:
                    $this->insertImg($node);
                    break;

                /* OTHER ACTIONS */
                case self::IGNORE: break;
                default: throw new \Exception('Unknown return value: ' . $this->transduceAction($node, $action));
            }
        }
    }

    protected function transduceAction(\DOMNode $node, string $action): int
    {
        if ($action === Traverser::ENTER) {
            if (NodeTypeUtility::isIgnoredNode($node)) {
                return self::IGNORE;
            } else if (NodeTypeUtility::isRootNode($node->nodeName)) {
                return self::ENT_DOC;
            } else if (NodeTypeUtility::isNewSection($node, $this->stack)) {
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
            } else if (NodeTypeUtility::isRootNode($node->nodeName)) {
                return self::LEA_DOC;
            } else if (NodeTypeUtility::isSectionEnd($node, $this->stack)) {
                return self::LEA_WSECTION;
            } else if (NodeTypeUtility::isContent($node)) {
                return self::LEA_CONTENT;
            } else if (NodeTypeUtility::isText($node)) {
                return self::LEA_TEXT;
            }  else if (NodeTypeUtility::isTable($node)) {
                return self::LEA_TABLE;
            } else if (NodeTypeUtility::isImg($node)) {
                return self::LEA_IMG;
            }
        }
        throw new UnhandledNodeException("Node with name " . $node->nodeName . " of type " . $node->nodeType . " not known");
    }

    protected function popAndCheckStack(string $expectedState): void
    {
        if (($val = $this->stack->pop()) !== $expectedState)
            throw new WrongStateException("Expected state " . $expectedState . ", got " . $val);
    }

    protected function checkStack(string $expectedState): void
    {
        if (($val = $this->stack->top()) !== $expectedState)
            throw new WrongStateException("Expected state " . $expectedState . ", got " . $val);
    }

    protected function insertNode(\DOMNode $node, bool $closing = false): void
    {
        $tag = '<' . ($closing? '/' : '') . $node->nodeName . '>';
        $this->contentBuffer[] = ($tag);
    }

    protected function insertTextNode(\DOMNode $node): void
    {
        $this->contentBuffer[] = utf8_decode($node->textContent);
    }

    protected function insertImg(\DOMNode $node): void
    {
        $tag = '<img src="' . $node->getAttribute('src') . '">';
        $this->contentBuffer[] = ($tag);
    }

    protected function handleSectionEnd(): void
    {
        if ($this->headerIsNewSection()) {
            $this->newSection($this->wSectionFirstText);
        }
    }

    protected function newSection(string $title): void
    {
        $this->newSubSection();
        $this->sectionBuffer[$title] = $this->subSectionBuffer;
        $this->contentBuffer = $this->subSectionBuffer = [];
    }

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

    protected function pushNode(\DOMNode $node): void
    {
        $this->stack->push($node->nodeName);
    }

    protected function cleanupBuffers(): void
    {
        if ($this->contentBuffer) {
            $this->contentBuffer['type'] = $this->currentCType ?: self::CE_TEXTMEDIA;
            $this->currentCType = '';
            $this->subSectionBuffer[] = $this->contentBuffer;
            $this->contentBuffer = [];
        }
        if ($this->subSectionBuffer) {
            $this->sectionBuffer[] = $this->subSectionBuffer;
            $this->subSectionBuffer = [];
        }
    }

    protected function setSubSectionType(string $type): void
    {
        $this->currentCType = $type;
    }

    protected function checkFirstText($text): void
    {
        if ($this->wSectionFirstText === '') $this->wSectionFirstText = trim($text);
        $this->firstChecked = true;
    }

    protected function headerIsNewSection(): bool
    {
        return (bool)preg_match(self::CHAPTER_REGEX, $this->wSectionFirstText);
    }

}