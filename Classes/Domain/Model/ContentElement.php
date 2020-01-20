<?php

namespace Graphodata\GdPdfimport\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class ContentElement extends AbstractEntity
{
    /**
     * @var string
     */
    protected $bodytext;

    /**
     * @var string
     */
    protected $ctype;

    /**
     * @var int
     */
    protected $uid;

    /**
     * @return string
     */
    public function getBodytext(): string
    {
        return $this->bodytext;
    }

    /**
     * @param string $bodytext
     *
     * @return ContentElement
     */
    public function setBodytext(string $bodytext): ContentElement
    {
        $this->bodytext = $bodytext;
        return $this;
    }

    /**
     * @return string
     */
    public function getCtype(): string
    {
        return $this->ctype;
    }

    /**
     * @param string $ctype
     *
     * @return ContentElement
     */
    public function setCtype(string $ctype): ContentElement
    {
        $this->ctype = $ctype;
        return $this;
    }

}