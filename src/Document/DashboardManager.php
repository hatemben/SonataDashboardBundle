<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DashboardBundle\Document;

use Sonata\DashboardBundle\Model\DashboardManagerInterface;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

/**
 * This class manages DashboardInterface persistency with the Doctrine ORM.
 *
 * @author Quentin Somazzi <qsomazzi@ekino.com>
 */
class DashboardManager extends BaseDashboard implements DashboardManagerInterface
{
    public function getClass()
    {
        // TODO: Implement getClass() method.
    }

    public function find($id)
    {
        // TODO: Implement find() method.
    }

    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        // TODO: Implement findOneBy() method.
    }

    public function save($entity, $andFlush = true)
    {
        // TODO: Implement save() method.
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function delete($entity, $andFlush = true)
    {
        // TODO: Implement delete() method.
    }

    public function getTableName()
    {
        // TODO: Implement getTableName() method.
    }

    public function getConnection()
    {
        // TODO: Implement getConnection() method.
    }

    public function getPager(array $criteria, $page, $limit = 10, array $sort = [])
    {
        $query = $this->getRepository()
            ->createQueryBuilder('d')
            ->select('d');

        $fields = $this->getEntityManager()->getClassMetadata($this->class)->getFieldNames();

        foreach ($sort as $field => $direction) {
            if (!\in_array($field, $fields, true)) {
                throw new \RuntimeException(sprintf("Invalid sort field '%s' in '%s' class", $field, $this->class));
            }
        }
        if (0 === \count($sort)) {
            $sort = ['name' => 'ASC'];
        }
        foreach ($sort as $field => $direction) {
            $query->orderBy(sprintf('d.%s', $field), strtoupper($direction));
        }

        $parameters = [];

        if (isset($criteria['enabled'])) {
            $query->andWhere('d.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        $query->setParameters($parameters);

        $pager = new Pager();
        $pager->setMaxPerPage($limit);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();

        return $pager;
    }


}
