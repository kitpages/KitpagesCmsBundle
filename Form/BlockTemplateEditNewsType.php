<?php
namespace Kitpages\CmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class BlockTemplateEditNewsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('date', 'text');        
        $builder->add(
            'content',
            'textarea',
            array(
                'required' => false,
                'attr' => array(
                    "class" => "kit-cms-rte-simple"
                )
            )
        );

    }
    
    public function getName() {
        return 'BlockTemplateEditNewsType';
    }
    
}
