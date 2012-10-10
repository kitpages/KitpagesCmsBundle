<?php
namespace Kitpages\CmsBundle\Form\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DefaultForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'backgroundColor',
            'text',
            array(
                'label' => "Background color",
                'required' => false
            )
        );

        $builder->add(
            'author',
            'text',
            array(
                'label' => 'Author',
                'required' => false
            )
        );
    }

    public function getName() {
        return 'PageLayoutEditDefault';
    }

}
