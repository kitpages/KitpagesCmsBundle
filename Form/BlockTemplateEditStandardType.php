<?php
namespace Kitpages\CmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class BlockTemplateEditStandardType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('body', 'textarea');
        $builder->add('media', 'hidden');
    }
    
    public function getName() {
        return 'BlockTemplateEditStandardType';
    }
    
}
