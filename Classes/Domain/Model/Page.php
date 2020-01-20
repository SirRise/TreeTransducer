<?php

namespace Graphodata\GdPdfimport\Domain\Model;

use Graphodata\GdPdfimport\Utility\NestingUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Page extends AbstractEntity
{

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var array
     */
    protected $contentElements = [];

    /**
     * @var string
     */
    protected $chapter = '';

    public function __construct(string $title, array $contentElements)
    {
        $chapterIndex = strpos($title, ')');
        $this->chapter = substr($title, 1, $chapterIndex - 1);
        $this->title = str_replace('\n', '', substr($title, $chapterIndex + 1));
        $this->contentElements = $this->createContentElementObjectArray($contentElements);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Page
     */
    public function setTitle(string $title): Page
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getChapter(): string
    {
        return $this->chapter;
    }

    /**
     * @param string $chapter
     *
     * @return Page
     */
    public function setChapter(string $chapter): Page
    {
        $this->chapter = $chapter;
        return $this;
    }

    /**
     * @return array
     */
    public function getContentElements(): array
    {
        return $this->contentElements;
    }

    /**
     * @param array $contentElements
     *
     * @return Page
     */
    public function setContentElements(array $contentElements): Page
    {
        $this->contentElements = $contentElements;
        return $this;
    }

    protected function createContentElementObjectArray(array $contentObjects): array
    {
        return array_map(function($value) {
            return new ContentElement($value['bodytext'], $value['type']);
        }, $contentObjects);
    }

}