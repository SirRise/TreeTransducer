<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Utility;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;

final class NodeTypeUtility
{
    public static function isRootNode(string $nodeName): bool
    {
        return $nodeName === NodeTypes::DOCUMENT;
    }

    public static function isHeading(\DOMNode $node, \SplStack $stack): bool
    {
        return strlen($node->textContent) < 150
            && preg_match(DOMDocumentTransducer::CHAPTER_REGEX, $node->textContent)
            && $stack->top() === NodeTypes::SECTION;
    }

    public static function isIgnoredNode(\DOMNode $node): bool
    {
        return self::isHTML($node)
            || self::isBody($node)
            || self::isMeta($node)
            || self::isTitle($node)
            || self::isHead($node)
            || self::isDate($node);
    }

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
            || self::isSup($node)
            || self::isI($node)
            || self::isA($node)
            || self::isU($node)
            || self::isHr($node)
            || self::isBr($node);
    }

    public static function isNewSection(\DOMNode $node, \SplStack $stack): bool
    {
        return self::isDiv($node)
            && self::isRootNode($stack->top());
    }

    public static function isSectionEnd(\DOMNode $node, \SplStack $stack): bool
    {
        return self::isDiv($node)
            && self::isInSectionState($stack);
    }

    public static function isInSectionState(\SplStack $stack): bool
    {
        return $stack->top() === NodeTypes::SECTION;
    }

    public static function isParagraph(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::P;
    }

    public static function isDate(\DOMNode $node): bool
    {
        return (bool)preg_match(DOMDocumentTransducer::DATE_HEADER_REGEX, trim($node->nodeValue ?? ''));
    }

    public static function isDiv(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::DIV;
    }

    public static function isHeader(\DOMNode $node): bool
    {
        return (bool)preg_match('/^h[1-6]$/', $node->nodeName);
    }

    public static function isSpan(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::SPAN;
    }

    public static function isText(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TEXT;
    }

    public static function isHTML(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::HTML;
    }

    public static function isHead(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::HEAD;
    }

    public static function isBody(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::BODY;
    }

    public static function isBold(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::B;
    }

    public static function isTable(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TABLE;
    }

    public static function isTbody(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TBODY;
    }

    public static function isTr(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TR;
    }

    public static function isTh(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TH;
    }

    public static function isTd(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TD;
    }

    public static function isSup(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::SUP;
    }

    public static function isBr(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::BR;
    }

    public static function isI(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::I;
    }

    public static function isA(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::A;
    }

    public static function isU(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::U;
    }

    public static function isHr(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::HR;
    }

    public static function isMeta(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::META;
    }

    public static function isImg(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::IMG;
    }

    public static function isTitle(\DOMNode $node): bool
    {
        return $node->nodeName === NodeTypes::TITLE;
    }
}