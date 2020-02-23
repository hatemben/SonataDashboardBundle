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


    /**
     * @MongoDB\ReferenceOne(targetDocument="App\Application\Sonata\DashboardBundle\Document\Block")
     */
    protected $parent;

    /**
     * @MongoDB\ReferenceMany(targetDocument="App\Application\Sonata\DashboardBundle\Document\Block")
     */
    protected $children;
}