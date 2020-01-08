<?php

namespace Graphodata\GdPdfimport\Parser;

use Graphodata\GdPdfimport\Domain\Model\Document;
use Graphodata\GdPdfimport\Domain\Model\Node;
use Graphodata\GdPdfimport\Domain\Model\Section;
use Graphodata\GdPdfimport\Exception\UnhandledNodeException;
use Graphodata\GdPdfimport\Exception\WrongStateException;
use Graphodata\GdPdfimport\Utility\NodeTypes;
use Graphodata\GdPdfimport\Utility\NodeTypeUtility;

final class DOMDocumentTransducer
{
    const DEBUG = 0;

    const ENT_DOC = 1;
    const LEA_DOC = -self::ENT_DOC;
    const ENT_CONTENT = 2;
    const LEA_CONTENT = -self::ENT_CONTENT;
    const ENT_SECTION = 3;
    const LEA_SECTION = -self::ENT_SECTION;
    const ENT_TEXT = 4;
    const LEA_TEXT = -self::ENT_TEXT;
    const ENT_TABLE = 5;
    const LEA_TABLE = -self::ENT_TABLE;
    const ENT_IMG = 6;
    const LEA_IMG = -self::ENT_IMG;

    const IGNORE = 404;
    const WRONG_STATE = 403;

    /**
     * TYPO3 pages
     *
     * @var Node[]
     */
    protected $sectionBuffer = [];

    /**
     * Single CE on pages
     * 
     * @var Node[]
     */
    protected $subSectionBuffer = [];

    /**
     * Content of CE
     *
     * @var Node[]
     */
    protected $contentBuffer = [];

    /**
     * @var \SplStack
     */
    protected $stack;

    public function __construct()
    {
        $this->stack = new \SplStack();
    }

    public function transduce(\DOMDocument $DOMDocument): Document
    {
        foreach(Traverser::traverse($DOMDocument) as $item) {

            list($action, $node) = $item;

            switch ($this->transduceAction($node, $action))
            {
                /* ENTER ACTIONS */
                case self::ENT_CONTENT:
                    $this->pushNode($node);
                    print_r($this->contentBuffer);
                    $this->insertNode($node);
                    break;
                case self::ENT_TABLE:
                    $this->newSubSection();
                    $this->pushNode($node);
                    $this->insertNode($node);
                    break;
                case self::ENT_SECTION:
                    $this->checkStack(NodeTypes::DOCUMENT);
                    $this->stack->push(NodeTypes::SECTION);
                    break;
                case self::ENT_DOC:
                    $this->pushNode($node);
                    break;
                case self::ENT_IMG: break;
                case self::ENT_TEXT: break;

                /* LEAVE ACTIONS */
                case self::LEA_CONTENT:
                    $this->popAndCheckStack(NodeTypes::CONTENT);
                    $this->insertNode($node, true);
                    print_r($this->contentBuffer);
                    break;
                case self::LEA_TABLE:
                    $this->popAndCheckStack(NodeTypes::TBODY);
                    $this->newSubSection();
                    $this->insertNode($node, true);
                    break;
                case self::LEA_SECTION:
                    $this->popAndCheckStack(NodeTypes::SECTION);
                    $this->newSection();
                    break;
                case self::LEA_DOC:
                    echo implode('', $this->contentBuffer);
                    return new Document($this->sectionBuffer);
                    break;
                case self::LEA_TEXT:
                    $this->insertTextNode($node);
                    break;
                case self::LEA_IMG:
                    $this->insertImg($node);
                    break;

                /* OTHER ACTIONS */
                case self::IGNORE: break;
            }
        }
    }

    protected function transduceAction(\DOMNode $node, string $action): int
    {
        if ($action === Traverser::ENTER) {
            if (NodeTypeUtility::isIgnoredNode($node)) {
                return self::IGNORE;
            } else if (NodeTypeUtility::isNewSection($node, $this->stack)) {
                return self::ENT_SECTION;
            } else if (NodeTypeUtility::isContent($node)) {
                return self::ENT_CONTENT;
            } else if (NodeTypeUtility::isRootNode($node)) {
                return self::ENT_DOC;
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
            } else if (NodeTypeUtility::isSectionEnd($node, $this->stack)) {
                return self::LEA_SECTION;
            } else if (NodeTypeUtility::isContent($node)) {
                return self::LEA_CONTENT;
            } else if (NodeTypeUtility::isRootNode($node)) {
                return self::LEA_DOC;
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
        if (!($val = $this->stack->pop()) === $expectedState && !self::DEBUG)
            throw new WrongStateException("Expected state " . $expectedState . ", got " . $val);
    }

    protected function checkStack(string $expectedState): void
    {
        if (!($val = $this->stack->top()) === $expectedState && !self::DEBUG)
            throw new WrongStateException("Expected state " . $expectedState . ", got " . $val);
    }

    protected function insertNode(\DOMNode $node, bool $closing = false): void
    {
        $tag = '<' . ($closing? '/' : '') . $node->nodeName . '>';
        $this->contentBuffer[] = htmlspecialchars($tag);
    }

    protected function insertTextNode(\DOMNode $node): void
    {
        $this->contentBuffer[] = $node->textContent;
    }

    protected function insertImg(\DOMNode $node): void
    {
        $tag = '<img src="' . $node->getAttribute('src') . '">';
        $this->contentBuffer[] = htmlspecialchars($tag);
    }

    protected function newSection(): void
    {
        $this->subSectionBuffer[] = $this->contentBuffer;
        $section = new Section($this->subSectionBuffer);
        $this->sectionBuffer[] = $section;
        $this->contentBuffer = $this->subSectionBuffer = [];
    }

    protected function newSubSection(): void
    {
        $this->sectionBuffer[] = $this->subSectionBuffer;
        $this->subSectionBuffer = [];
    }

    protected function pushNode(\DOMNode $node): void
    {
        $this->stack->push($node->nodeName);
    }

}