<?php
namespace Kitpages\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreatePageLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'slug',
            'text',
            array(
                'required' => false,
                'attr' => array('class'=>'kit-cms-advanced'),
                'error_bubbling' => true
            )
        );
        $builder->add('title', 'text');
        $builder->add('isInNavigation', 'checkbox', array('required' => false));
        $builder->add('menuTitle', 'text', array('required' => false));
        $builder->add('linkUrl', 'text', array('required' => false));
        $builder->add(
            'isLinkUrlFirstChild',
            'checkbox',
            array(
                'required' => false,
                'label' => "Link automatic on first child",
            )
        );
        $builder->add('parent_id','hidden',array(
            'mapped' => false
        ));
        $builder->add('next_sibling_slug','hidden',array(
            'mapped' => false
        ));
        $builder->add('prev_sibling_slug','hidden',array(
            'mapped' => false
        ));

    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Kitpages\CmsBundle\Entity\Page'
        ));
    }

    public function getName()
    {
        return 'kitpagesCmsCreatePageLink';
    }
}
