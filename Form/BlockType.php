<?php
namespace Kitpages\CmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class BlockType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('label', 'text');
        $builder->add('template', 'text');
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Kitpages\CmsBundle\Entity\Block',
        );
    }
}
