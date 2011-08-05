<?php
namespace Kitpages\CmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class BlockTemplateEditStandardType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('body', 'textarea', array('required' => false));
        $builder->add('media_1', 'hidden');
        $builder->add('media_2', 'hidden');
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
