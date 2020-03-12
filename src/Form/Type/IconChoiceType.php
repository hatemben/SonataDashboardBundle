<?php

namespace Sonata\DashboardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IconChoiceType extends AbstractType
{
    /**
     * Cache for multiple icon fields or sub-requests.
     *
     * @var array
     */
    private $choices;

    private $kernelRootDir;

    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Pass this flag is necessary to render the label as raw.
        // See below the twig field template for more details.
        $view->vars['raw_label'] = true;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                // It's the key of the solution and can be done in many ways.
                // Now, the rendered <select> element will have a new font.
                'style' => "font-family: 'FontAwesome';"
            ],
            'choices' => $this->getFontAwesomeIconChoices(),
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    protected function getFontAwesomeIconChoices()
    {
        if (null !== $this->choices) {
            // don't to load again for optimal performance.
            // useful for multi-icon fields and sub-requests.
            return $this->choices;
        }

        // BTW we could configure the path to the "font-awesome.css".
        $fontAwesome = file_get_contents($this->kernelRootDir.'/../public/bundles/sonatacore/vendor/components-font-awesome/css/font-awesome.css');

        // this regular expression only works with uncompressed version (not works with "font-awesome.min.css")
        $pattern = '/\.(fa-(?:\w+(?:-)?)+):before\s+{\s*content:\s*"\\\\(.+)";\s+}/';

        if (preg_match_all($pattern, $fontAwesome, $matches, PREG_SET_ORDER)) {
            foreach ($matches as list(, $class, $code)) {
                // this may vary depending on the version of Symfony,
                // if the class name is displayed instead of the icon then swap the key/value
                $this->choices[str_replace('fa-','',$class).' &#x'.$code.';'] = $class;
            }
        }
        return $this->choices;

    }
}