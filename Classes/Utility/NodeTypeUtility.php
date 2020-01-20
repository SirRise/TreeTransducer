<?php

namespace Graphodata\GdPdfimport\Utility;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;

class NodeTypeUtility
{
    public static function isRootNode(string $node): bool
    {
        return $node === NodeTypes::DOCUMENT;
    }

    public static function isHeading($node, $stack): bool
    {
        return strlen($node->textContent) < 150
            && preg_match(DOMDocumentTransducer::CHAPTER_REGEX, $node->textContent)
            && $stack->top() === NodeTypes::SECTION;
    }

    public static function isIgnoredNode($node): bool
    {
        return self::isHTML($node)
            || self::isBody($node)
            || self::isMeta($node)
            || self::isTitle($node)
            || self::isHead($node);
    }

    public static function isContent($node): bool
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

    public static function skipNode($node): bool
    {
        return self::isPageheader($node)
            || self::isHTML($node);
    }

    public static function isNewSection($node, $stack): bool
    {
        return self::isDiv($node)
            && self::isRootNode($stack->top());
    }

    public static function isSectionEnd($node, $stack): bool
    {
        return self::isDiv($node)
            && self::isInSectionState($stack);
    }

    public static function isInSectionState($stack): bool
    {
        return $stack->top() === NodeTypes::SECTION;
    }

    public static function isParagraph($node): bool
    {
        return $node->nodeName === NodeTypes::P;
    }

    public static function isDiv($node): bool
    {
        return $node->nodeName === NodeTypes::DIV;
    }

    public static function isHeader($node): bool
    {
        return preg_match('/^h[1-6]$/', $node->nodeName);
    }

    public static function isSpan($node): bool
    {
        return $node->nodeName === NodeTypes::SPAN;
    }

    public static function isText($node): bool
    {
        return $node->nodeName === NodeTypes::TEXT;
    }

    public static function isHTML($node): bool
    {
        return $node->nodeName === NodeTypes::HTML;
    }

    public static function isHead($node): bool
    {
        return $node->nodeName === NodeTypes::HEAD;
    }

    public static function isBody($node): bool
    {
        return $node->nodeName === NodeTypes::BODY;
    }

    public static function isBold($node): bool
    {
        return $node->nodeName === NodeTypes::B;
    }

    public static function isTable($node): bool
    {
        return $node->nodeName === NodeTypes::TABLE;
    }

    public static function isTbody($node): bool
    {
        return $node->nodeName === NodeTypes::TBODY;
    }

    public static function isTr($node): bool
    {
        return $node->nodeName === NodeTypes::TR;
    }

    public static function isTh($node): bool
    {
        return $node->nodeName === NodeTypes::TH;
    }

    public static function isTd($node): bool
    {
        return $node->nodeName === NodeTypes::TD;
    }

    public static function isSup($node): bool
    {
        return $node->nodeName === NodeTypes::SUP;
    }

    public static function isBr($node): bool
    {
        return $node->nodeName === NodeTypes::BR;
    }

    public static function isI($node): bool
    {
        return $node->nodeName === NodeTypes::I;
    }

    public static function isA($node): bool
    {
        return $node->nodeName === NodeTypes::A;
    }

    public static function isU($node): bool
    {
        return $node->nodeName === NodeTypes::U;
    }

    public static function isHr($node): bool
    {
        return $node->nodeName === NodeTypes::HR;
    }

    public static function isMeta($node): bool
    {
        return $node->nodeName === NodeTypes::META;
    }

    public static function isImg($node): bool
    {
        return $node->nodeName === NodeTypes::IMG;
    }

    public static function isTitle($node): bool
    {
        return $node->nodeName === NodeTypes::TITLE;
    }
}