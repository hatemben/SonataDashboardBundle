<?php

namespace Sonata\DashboardBundle\Block;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\DashboardBundle\Form\Type\IconChoiceType;
use Sonata\Form\Type\ImmutableArrayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Show block with Document counter
 *
 * @author HBY <me@hby.me>
 */
final class CounterBlockService extends AbstractAdminBlockService
{

    public function __construct($name, EngineInterface $templating, ManagerRegistry $container)
    {
        parent::__construct($name, $templating);
        $this->container = $container;
    }

    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'color' => 'aqua',
            'icon' => 'pie-graph',
            'object' => 'User',
            'template' => '@SonataDashboard/Block/counter.html.twig',
        ]);
    }

    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper
            ->add('settings', ImmutableArrayType::class, [
                'keys' => [
                    ['color', ChoiceType::class, ['required' => false,
                        'choices' => [
                            'aqua' => 'aqua',
                            'yellow' => 'yellow',
                            'green' => 'green',
                            'red' => 'red',
                        ]]],
                    ['icon', IconChoiceType::class, ['required' => false]],
                    ['object', ChoiceType::class, ['required' => false,
                        'choices' =>[
                            'User'=>'App\Document\User',
                            'Media'=>'App\Application\Sonata\MediaBundle\Document\Media',
                            'Page'=>'App\Application\Sonata\PageBundle\Document\Page',
                            'Post'=>'App\Application\Sonata\NewsBundle\Document\Post',
                            'Site'=>'App\Application\Sonata\PageBundle\Document\Site']]],
                ]
            ])
        ;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        // merge settings
        $settings = $blockContext->getSettings();

        $count = sizeof($this->container->getManagerForClass($settings['object'])->getRepository($settings['object'])->findAll());

        $classNames = explode('\\',$settings['object']);
        $className = $classNames[(sizeof($classNames)-1)];

        return $this->renderResponse($blockContext->getTemplate(), [
            'className'     => $className,
            'count'     => $count,
            'block'     => $blockContext->getBlock(),
            'settings'  => $settings
        ], $response);
    }

    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', [
            'class' => 'fa fa-pie-chart',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Counter (Dashboard)';
    }
}
