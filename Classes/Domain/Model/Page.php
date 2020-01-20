<?php

namespace Graphodata\GdPdfimport\Domain\Model;

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

    public function __construct(string $title)
    {
        $this->title = $title;
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

}