<?php
namespace Kitpages\CmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class BlockTemplateEditStandardType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add(
            'title',
            'text',
            array(
                'required' => false,
                'attr' => array(
                    "size" => "50"
                )
            )
        );
        $builder->add(
            'mainContent',
            'textarea',
            array(
                'required' => false,
                'attr' => array(
                    "class" => "kit-cms-rte-advanced"
                )
            )
        );
        $builder->add('media_mainImage', 'hidden');

        $builder->add(
            'imagePosition',
            'choice',
            array(
                'required' => false,
                'choices'   => array(
                    'left' => 'Left',
                    'right' => 'Right',
                    'top' => 'Top',
                    'bottom' => 'Bottom',
                ),
                'label' => 'Image position'
            )
        );

        $builder->add(
            'subContent',
            'textarea',
            array(
                'required' => false,
                'attr' => array(
                    "class" => "kit-cms-rte-simple"
                )
            )
        );

        $builder->add(
            'displaySeparator',
            'checkbox',
            array(
                'required' => false,
                'value' => 'YES',
                'label' => 'Display separation bar ?'
            )
        );
    }

    public function getName() {
        return 'BlockTemplateEditStandardType';
    }

}
