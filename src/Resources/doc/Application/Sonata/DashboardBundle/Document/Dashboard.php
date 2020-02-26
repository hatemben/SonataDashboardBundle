<?php

namespace App\Application\Sonata\DashboardBundle\Document;

use Sonata\DashboardBundle\Document\BaseDashboard as BaseDashboard;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Dashboard extends BaseDashboard
{
    /**
     * @MongoDB\Id(strategy="auto")
     */
    protected $id;


    /**
     * @MongoDB\ReferenceMany(targetDocument="App\Application\Sonata\DashboardBundle\Document\Block", mappedBy="dashboard")
     */
    protected $blocks;
}