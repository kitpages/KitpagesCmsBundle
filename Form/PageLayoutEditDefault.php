<?php
namespace Kitpages\CmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class PageLayoutEditDefault extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('backgroundColor', 'text');
    }
    
    public function getName() {
        return 'PageLayoutEditDefault';
    }
    
}
