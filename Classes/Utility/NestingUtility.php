<?php

namespace Graphodata\GdPdfimport\Utility;

use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;

class NestingUtility
{

    public static function isolateNumbersForNesting(array $array): array
    {
        return self::array_map_keys($array, [__CLASS__, 'isolateChapterNumbers']);
    }

    public static function array_map_keys(array $array, callable $callable) : array
    {
        $map = [];
        foreach ($array as $key => $value) {
            $result = $callable($key, $value);
            $map[key($result)] = $result[key($result)];
        }
        return $map;
    }

    public static function isolateChapterNumbers(string $chapterName, array $chapterContent): array
    {
        $matches = [];
        preg_match(DOMDocumentTransducer::CHAPTER_REGEX, $chapterName, $matches);
        return ['(' . $matches[0] . ')' . $chapterName => $chapterContent];
    }

}