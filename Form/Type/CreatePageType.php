<?php
namespace Kitpages\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreatePageType extends AbstractType
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
        $builder->add(
            'title',
            'text',
            array(
                'required' => false,
                'attr' => array('size'=>'40')
            )
        );
        $builder->add('parent_id','hidden',array(
            'mapped' => false
        ));
        $builder->add('layout', 'choice',array(
            'choices' => $options['layoutList'],
            'required' => true
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
            'data_class' => 'Kitpages\CmsBundle\Entity\Page',
            'layoutList' => array()
        ));
    }

    public function getName()
    {
        return 'kitpagesCmsCreatePage';
    }
}
