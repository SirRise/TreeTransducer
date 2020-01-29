<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Utility;

use Graphodata\GdPdfimport\Domain\Model\ContentElement;
use Graphodata\GdPdfimport\Domain\Model\Page;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageUtility
{

    protected const DB_PAGES = 'pages';
    protected const DB_TTCONTENT = 'tt_content';

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
     * @var Connection
     */
    protected $ttContentConnection;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var int
     */
    protected $pid;

    /**
     * @var array
     */
    protected $pages = [];

    public function __construct(array $pages, int $pid)
    {
        $this->pid = $pid;
        $this->pages = self::createPageObjectArrayFromArray($pages);
        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $this->pagesConnection = $this->connectionPool->getConnectionForTable(self::DB_PAGES);
        $this->pagesQueryBuilder = $this->connectionPool->getQueryBuilderForTable(self::DB_PAGES);
        $this->ttContentConnection = $this->connectionPool->getConnectionForTable(self::DB_TTCONTENT);
    }

    public function createPages(bool $execImport): void
    {
        if ($execImport) {
            /** @var Page $page */
            foreach ($this->pages as $page) {


//                $parentPage = NestingUtility::getPageParent($page, $this->pagesQueryBuilder, $this->pid);
//
//                self::createPage($page, $this->pagesQueryBuilder, $parentPage);
//
//                $insertPid = $this->pagesConnection->lastInsertId(self::DB_PAGES);


                /** @var ContentElement $ce */
                foreach ($page->getContentElements() as $ce) {

                    $bodytext = preg_replace('/\n/', '', $ce->getBodytext());

                    echo $bodytext;

//                    $this->ttContentConnection
//                        ->insert(self::DB_TTCONTENT,
//                            [
//                                'bodytext' => $bodytext,
//                                'pid' => $insertPid,
//                                'CType' => 'textmedia'
//                            ],
//                            [
//                                \PDO::PARAM_STR,
//                                \PDO::PARAM_INT,
//                                \PDO::PARAM_STR
//                            ]);
                }
            }
        }

    }

    public static function createPage(Page $page, QueryBuilder $queryBuilder, int $parentUid): int
    {
        /** @var Connection $connection */
        $connection = $queryBuilder->getConnection();
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::DB_PAGES);
        $qb->insert(self::DB_PAGES)
            ->values([
                'title' => html_entity_decode($page->getTitle()),
                'pid' => $parentUid,
                'doktype' => 1
            ])
            ->execute();
        $pid = (int) $connection->lastInsertId(self::DB_PAGES);
        NestingUtility::$cache[] = $pid;
        return $pid;
    }

    protected static function createPageObjectArrayFromArray(array $pages): array
    {
        return NestingUtility::array_map_keys($pages, function($key, $value) {
            $chapterIndex = mb_strpos($key, ')');
            $chapter = mb_substr($key, 1, $chapterIndex - 1);
            $title = mb_substr($key, $chapterIndex + 1);
            return [$key => new Page($title, $chapter, $value)];
        });
    }

    public function getPages(): array
    {
        return $this->pages;
    }

}