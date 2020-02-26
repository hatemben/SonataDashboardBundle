<?php

namespace App\Application\Sonata\DashboardBundle\Document;

use Sonata\DashboardBundle\Document\BaseBlock as BaseBlock;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Block extends BaseBlock
{
    /**
     * @MongoDB\Id(strategy="auto")
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @MongoDB\ReferenceOne(targetDocument="App\Application\Sonata\DashboardBundle\Document\Dashboard", inversedBy="blocks")
     */
    protected $dashboard;

    /**
     * @MongoDB\ReferenceOne(targetDocument="App\Application\Sonata\DashboardBundle\Document\Block",inversedBy="children")
     */
    protected $parent;

    /**
     * @MongoDB\ReferenceMany(targetDocument="App\Application\Sonata\DashboardBundle\Document\Block",mappedBy="parent",sort={"name": "asc"})
     */
    protected $children;
}