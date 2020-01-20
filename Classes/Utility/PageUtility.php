<?php

namespace Graphodata\GdPdfimport\Utility;


use Graphodata\GdPdfimport\Domain\Model\Page;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageUtility
{

    protected const DB = 'pages';

    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    /**
     * @var ConnectionPool
     */
    protected $pagesConnection;


    /**
     * @var ConnectionPool
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


    public function injectConnectionPool(ConnectionPool $pool): void
    {
        $this->connectionPool = $pool;
        $this->pagesConnection = $this->connectionPool->getConnectionForTable('pages');
        $this->ttContentConnection = $this->connectionPool->getConnectionForTable('tt_content');
    }

    public function __construct(array $pages)
    {
        $this->pages = $pages;
//        $this->pagesConnection = $this->
    }

    public function createPages(): void
    {
        /** @var Page $page */
        foreach($this->pages as $page) {

            $this->pagesConnection
                ->insert('pages', [
                    'title' => $page->getTitle()
                ]);

            foreach($page->getContentElements as $ce) {
                $this->ttContentConnection
                    ->insert('tt_content', [

                    ]);
            }
        }
    }

//    public function createPageObjectArrayFromArray(array $pages): array
//    {
//        $ret = [];
//        foreach ($pages as $title => $content) {
//            $ret[] =
//        }
//    }

}