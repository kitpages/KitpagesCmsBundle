<?php
namespace Kitpages\CmsBundle\Form\Block;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Kitpages\CmsBundle\Validator\Generic;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\MaxLength;
use Symfony\Component\Validator\Constraints\Email;

class StandardForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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

    public function filterList() {
        return array(
            'subContent' => 'stripTagText'
        );
    }

    public function getName() {
        return 'BlockTemplateEditStandardType';
    }

}
