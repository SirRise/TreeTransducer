<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Utility;

use Graphodata\GdPdfimport\Exception\UnhandledNodeException;
use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;

/**
 * Class NodeTypeUtility
 *
 * @package Graphodata\GdPdfimport\Utility
 */
final class NodeTypeUtility
{
    /**
     * @param string $nodeName
     * @return bool
     */
    public static function isRootNode(string $nodeName): bool
    {
        return $nodeName === NodeTypes::DOCUMENT;
    }

    /**
     * @param \DOMNode  $node
     * @param \SplStack $stack
     * @return bool
     */
    public static function isHeading(\DOMNode $node, \SplStack $stack): bool
    {
        return strlen($node->textContent) < 150
            && preg_match(DOMDocumentTransducer::CHAPTER_REGEX, $node->textContent)
            && $stack->top() === NodeTypes::SECTION;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isIgnoredNode(\DOMNode $node): bool
    {
        return self::isHTML($node)
            || self::isBody($node)
            || self::isMeta($node)
            || self::isTitle($node)
            || self::isHead($node)
            || self::isDate($node)
            || self::isComment($node);
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isContent(\DOMNode $node): bool
    {
        return self::isDiv($node)
            || self::isParagraph($node)
            || self::isSpan($node)
            || self::isHeader($node)
            || self::isBold($node)
            || self::isTbody($node)
            || self::isTr($node)
            || self::isTd($node)
            || self::isTh($node)
            || self::isLi($node)
            || self::isUl($node)
            || self::isOl($node)
            || self::isSup($node)
            || self::isI($node)
            || self::isA($node)
            || self::isU($node)
            || self::isHr($node)
            || self::isBr($node);
    }

    /**
     * @param \DOMNode  $node
     * @param \SplStack $stack
     * @return bool
     */
    public static function isNewSection(\DOMNode $node, \SplStack $stack): bool
    {
        return self::isDiv($node)
            && self::isRootNode($stack->top());
    }

    /**
     * @param \DOMNode  $node
     * @param \SplStack $stack
     * @return bool
     */
    public static function isSectionEnd(\DOMNode $node, \SplStack $stack): bool
    {
        return self::isDiv($node)
            && self::isInSectionState($stack);
    }

    /**
     * @param \SplStack $stack
     * @return bool
     */
    public static function isInSectionState(\SplStack $stack): bool
    {
        return $stack->top() === NodeTypes::SECTION;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isParagraph(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::P;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isDate(\DOMNode $node): bool
    {
        return (bool)preg_match(DOMDocumentTransducer::DATE_HEADER_REGEX, trim($node->nodeValue ?? ''));
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isDiv(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::DIV;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isHeader(\DOMNode $node): bool
    {
        return (bool)preg_match('/^h[1-6]$/', $node->nodeName);
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isSpan(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::SPAN;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isText(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TEXT;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isHTML(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::HTML;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isHead(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::HEAD;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isBody(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::BODY;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isBold(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::B;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isTable(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TABLE;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isTbody(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TBODY;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isTr(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TR;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isTh(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TH;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isTd(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TD;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isLi(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::LI;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isOl(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::OL;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isUl(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::UL;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isSup(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::SUP;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isBr(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::BR;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isI(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::I;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isA(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::A;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isU(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::U;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isHr(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::HR;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isMeta(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::META;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isImg(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::IMG;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isTitle(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TITLE;
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isListBegin(\DOMNode $node)
    {
        return $node->nodeName === NodeTypes::P && self::childNodesMatchList($node->childNodes);
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isListEnd(\DOMNode $node, bool $listMode)
    {
        return $listMode && !self::childNodesMatchList($node->childNodes);
    }

    /**
     * @param \DOMNodeList $nodes
     * @return bool
     */
    private static function childNodesMatchList(\DOMNodeList $nodes): bool
    {
        return  $nodes->length === 2
            && $nodes->item(0)->nodeName === NodeTypes::SPAN
            && $nodes->item(1)->nodeName === NodeTypes::SPAN
            && self::nodeValueMatchesListStart($nodes->item(0));
    }

    /**
     * @param \DOMNode $node
     * @return string
     * @throws \Graphodata\GdPdfimport\Exception\UnhandledNodeException
     */
    public static function getListType(\DOMNode $node): string
    {
        if (preg_match('/\(\d{1,2}\)/', $node->childNodes->item(0)->nodeValue)
         || preg_match('/\d{1,2}\./', $node->childNodes->item(0)->nodeValue))
            return NodeTypes::OL;
        else if ($node->childNodes->item(0)->nodeValue === '–') return NodeTypes::UL;
        throw new UnhandledNodeException("Couldn't determine type of list");
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    private static function nodeValueMatchesListStart(\DOMNode $node): bool
    {
        return preg_match('/\(\d{1,2}\)/', $node->nodeValue)
            || preg_match('/\d{1,2}\./', $node->nodeValue)
            || $node->nodeValue === '–'; // DON'T CHANGE - THIS IS NOT A DASH
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    public static function isComment(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::COMMENT;
    }
}