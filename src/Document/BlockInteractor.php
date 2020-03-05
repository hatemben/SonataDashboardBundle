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

//use Doctrine\ORM\EntityManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\BSON\ObjectId;
use Sonata\DashboardBundle\Model\BlockInteractorInterface;
use Sonata\DashboardBundle\Model\BlockManagerInterface;
use Sonata\DashboardBundle\Model\DashboardBlockInterface;
use Sonata\DashboardBundle\Model\DashboardInterface;
//use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * This class interacts with blocks.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BlockInteractor implements BlockInteractorInterface
{
    /**
     * @var bool[]
     */
    private $dashboardBlocksLoaded = [];

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var BlockManagerInterface
     */
    private $blockManager;

    /**
     * @var string
     */
    private $defaultContainer;

    /**
     * @param RegistryInterface     $registry         Doctrine registry
     * @param BlockManagerInterface $blockManager     Block manager
     * @param string                $defaultContainer
     */
    public function __construct(ManagerRegistry $registry, BlockManagerInterface $blockManager, $defaultContainer)
    {
        $this->blockManager = $blockManager;
        $this->registry = $registry;
        $this->defaultContainer = $defaultContainer;
    }

    public function getBlock(int $id): ?DashboardBlockInterface
    {
        return $this->getDocumentManager()->createQueryBuilder($this->blockManager->getClass())
            ->field('_id')->equals($id)
            ->getQuery()
            ->getSingleResult();
    }

    public function getBlocksById(DashboardInterface $dashboard)
    {
        return $this->getDocumentManager()->getRepository($this->blockManager->getClass())
            ->findBy(['dashboard.$id' => new ObjectId($dashboard->getId())]);
    }

    public function saveBlocksPosition(array $data = [], bool $partial = true): void
    {
        $em = $this->getDocumentManager();

        try {
            foreach ($data as $block) {
                if (!$block['id'] or !\array_key_exists('position', $block) or !$block['parent_id'] or !$block['dashboard_id']) {
                    continue;
                }

                $this->blockManager->updatePosition($block['id'], $block['position'], $block['parent_id'], $block['dashboard_id'], $partial);
            }

            $em->flush();
        } catch (\Exception $e) {
            $em->rollBack();

            throw $e;
        }
    }

    public function createNewContainer(array $values = [], \Closure $alter = null): DashboardBlockInterface
    {
        $container = $this->blockManager->create();

        if (!$container instanceof DashboardBlockInterface) {
            throw new \InvalidArgumentException('Invalid block created');
        }

        $container->setEnabled($values['enabled'] ?? true);
        $container->setCreatedAt(new \DateTime());
        $container->setUpdatedAt(new \DateTime());
        $container->setType($this->defaultContainer);

        if (isset($values['dashboard'])) {
            $container->setDashboard($values['dashboard']);
        }

        if (isset($values['name'])) {
            $container->setName($values['name']);
        } else {
            $container->setName($values['code'] ?? 'No name defined');
        }

        $container->setSettings(['code' => $values['code'] ?? 'no code defined']);
        $container->setPosition($values['position'] ?? 1);

        if (isset($values['parent'])) {
            $container->setParent($values['parent']);
        }

        if ($alter) {
            $alter($container);
        }

        $this->blockManager->save($container);

        return $container;
    }

    public function loadDashboardBlocks(DashboardInterface $dashboard)
    {
        if (isset($this->dashboardBlocksLoaded[$dashboard->getId()])) {
            return [];
        }

        $blocks = $this->getBlocksById($dashboard);

        $dashboard->disableBlockLazyLoading();

        foreach ($blocks as $block) {
            $parent = $block->getParent();

            $block->disableChildrenLazyLoading();
            if (!$parent) {
                $dashboard->addBlocks($block);

                continue;
            }

            $blocks[$block->getParent()->getId()]->disableChildrenLazyLoading();
            $blocks[$block->getParent()->getId()]->addChildren($block);
        }

        $this->dashboardBlocksLoaded[$dashboard->getId()] = true;

        return $blocks;
    }

    private function getDocumentManager(): DocumentManager
    {
        return $this->registry->getManagerForClass($this->blockManager->getClass());
    }
}
