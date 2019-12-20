<?php

namespace Graphodata\GdPdfimport\Utility;

class NodeTypeUtility
{
    const DIV = 'div';
    const P = 'p';
    const SPAN = 'span';

    public static function isRootNode($node): bool
    {

    }

    public static function isHeading($node): bool
    {

    }

    public static function isParagraph($node): bool
    {

    }

    public static function isPageHeader($node): bool
    {
        return false;
    }

    public static function isDate($node): bool
    {
        return false;
    }

    public static function isDiv($node): bool
    {
        return $node->nodeName === 'div';
    }

    public static function skipNode($node): bool
    {
        return self::isDiv($node);
    }

    public static function isSection($node): bool
    {

    }
}