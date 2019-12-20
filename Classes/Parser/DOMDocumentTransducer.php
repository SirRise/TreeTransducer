<?php

namespace Graphodata\GdPdfimport\Parser;

use Graphodata\GdPdfimport\Domain\Model\Node;
use Graphodata\GdPdfimport\Domain\Model\Section;
use Graphodata\GdPdfimport\Utility\NodeTypeUtility;

final class DOMDocumentTransducer
{

    const HEADING = '0';
    const SECTION = '1';
    const INITIAL = '2';

    /**
     *
     * TYPO3 Pages
     *
     * @var Node[]
     */
    protected $sectionBuffer = [];

    /**
     *
     * Single CE on pages
     *
     * @var Node[]
     */
    protected $paragraphBuffer = [];

    /**
     * @var \SplStack
     */
    protected $stack;

    public function __construct() {
        $this->stack = new \SplStack();
        $this->stack->push(self::INITIAL);
    }

    public function parse(\DOMDocument $DOMDocument)
    {
        foreach(Traverser::traverse($DOMDocument) as $item) {

            list($action, $node) = $item;

            if (NodeTypeUtility::skipNode($node)) continue;

            if ($action === Traverser::ENTER) {

                if (NodeTypeUtility::isHeading($node)) {
                    $this->stack->push(self::HEADING);
                } else if (NodeTypeUtility::isNewSection($node)) {
                    $this->stack->push(self::SECTION);
                }

            } else if ($action === Traverser::LEAVE) {

                if (NodeTypeUtility::isHeading($node)) {
                    $this->paragraphBuffer[] = $node;
                } else if (NodeTypeUtility::isSection($node)) {
                    if ($this->isInSectionState()) {
                        $this->stack->pop();
                        $section = new Section($this->paragraphBuffer);
                        $this->paragraphBuffer = [];
                        $this->sectionBuffer[] = $section;
                    } else {
                        throw new WrongStateException();
                    }
                } else if (NodeTypeUtility::isRootNode($node)) {
                    return new class {};
                }
            }
        }
    }

    protected function isInSectionState() {
        return $this->stack->top() === self::SECTION;
    }
}