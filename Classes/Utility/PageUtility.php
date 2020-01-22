<?php

namespace Graphodata\GdPdfimport\Utility;

use Graphodata\GdPdfimport\Domain\Model\ContentElement;
use Graphodata\GdPdfimport\Domain\Model\Page;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PageUtility
{

    protected const DB = 'pages';

    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    /**
     * @var Connection
     */
    protected $pagesConnection;

    /**
     * @var QueryBuilder
     */
    protected $pagesQueryBuilder;

    /**
     * @var QueryBuilder
     */
    protected $ttContentQueryBuilder;

    /**
     * @var Connection
     */
    protected $ttContentConnection;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var array
     */
    protected $pages = [];

    public function __construct(array $pages)
    {
        $this->pages = self::createPageObjectArrayFromArray($pages);
        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $this->pagesConnection = $this->connectionPool->getConnectionForTable('pages');
        $this->pagesQueryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $this->ttContentQueryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $this->ttContentConnection = $this->connectionPool->getConnectionForTable('tt_content');
    }

    public function createPages(): void
    {
        /** @var Page $page */
//        foreach ($this->pages as $page) {
//
//            $parentPage = NestingUtility::getPageParent($page, $this->pagesQueryBuilder);
//
//            self::createPage($page, $this->pagesQueryBuilder, $parentPage);
//
//            $insertPid = $this->pagesConnection->lastInsertId('pages');
//
//            /** @var ContentElement $ce */
//            foreach ($page->getContentElements() as $ce) {
//                $this->ttContentConnection
//                    ->insert('tt_content',
//                        [
//                            'bodytext' => $ce->getBodytext(),
//                            'pid' => $insertPid,
//                            'CType' => 'textmedia'
//                        ],
//                        [
//                            \PDO::PARAM_STR,
//                            \PDO::PARAM_INT,
//                            \PDO::PARAM_STR
//                        ]);
//            }
//        }
    }

    public static function createPage(Page $page, QueryBuilder $queryBuilder, int $parentUid): int
    {
        /** @var Connection $connection */
        $connection = $queryBuilder->getConnection();
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $qb->insert('pages')
            ->values([
                'title' => htmlspecialchars($page->getTitle()),
                'pid' => $parentUid,
                'doktype' => 1
            ])
            ->execute();
        return $connection->lastInsertId('pages');
    }

    public static function createPageObjectArrayFromArray(array $pages): array
    {
        return NestingUtility::array_map_keys($pages, function($key, $value) {
            $chapterIndex = strpos($key, ')');
            $chapter = substr($key, 1, $chapterIndex - 1);
            $title = substr($key, $chapterIndex + 1);
            return [$key => new Page($title, $chapter, $value)];
        });
    }

    public function getPages(): array
    {
        return $this->pages;
    }

    public static function chapterMatches(Page $page, string $regex): bool
    {
        return (bool) preg_match($regex, $page->getChapter());
    }

}