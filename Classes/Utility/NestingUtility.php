<?php

namespace Graphodata\GdPdfimport\Utility;

use Doctrine\DBAL\FetchMode;
use Graphodata\GdPdfimport\Domain\Model\Page;
use Graphodata\GdPdfimport\Parser\DOMDocumentTransducer;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class NestingUtility
{
    /**
     * @var int
     */
    public static $currentParent = 0;

    /**
     * Gets chapter numbers, wraps them in parenthesis and puts them in front of the title
     *
     * @param array $array
     * @return array
     */
    public static function isolateNumbersForNesting(array $array): array
    {
        return self::array_map_keys($array, [__CLASS__, 'isolateChapterNumbers']);
    }

    /**
     * It does exactly what you would expect
     *
     * @param array    $array
     * @param callable $callable
     * @return array
     */
    public static function array_map_keys(array $array, callable $callable): array
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

    public static function getPageParent(Page $page, QueryBuilder $queryBuilder): int
    {
        if (!self::pageHasParent($page)) return 1;
        return self::createParentPageIfMissing($page, $queryBuilder);
    }

    public static function createParentPageIfMissing(Page $page, QueryBuilder $queryBuilder): int
    {
        $parentChapter = mb_strlen($page->getChapter()) > 3
            ? substr($page->getChapter(), 0, mb_strlen($page->getChapter()) - 2)
            : substr($page->getChapter(), 0, mb_strlen($page->getChapter()) - 1);

        $result = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->like(
                    'title',
                    $queryBuilder->createNamedParameter($queryBuilder->escapeLikeWildcards($parentChapter) . '%')
                )
            )->orderBy('uid', QueryInterface::ORDER_ASCENDING)
            ->execute();

        if ($result->rowCount() > 0) {
            return $result->fetch(FetchMode::ASSOCIATIVE)['uid'];
        } else {
            $newPage = new Page($parentChapter, $parentChapter, []);
            $newPageParent = self::getPageParent($newPage, $queryBuilder);
            return PageUtility::createPage($newPage, $queryBuilder, $newPageParent);
        }
    }

    public static function pageHasParent(Page $page): bool
    {
        return mb_strlen($page->getChapter()) > 2;
    }

}