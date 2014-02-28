<?php
namespace Kitpages\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreateBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'slug',
            'text',
            array(
                'required' => false,
                'attr' => array('class'=>'kit-cms-advanced'),
            )
        );
        $builder->add('zone_id','hidden',array(
            'mapped' => false
        ));
        $builder->add('position','hidden',array(
            'required' => false,
            'mapped' => false
        ));
        $builder->add('template', 'choice',array(
            'choices' => $options['templateList'],
            'required' => true
        ));
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Kitpages\CmsBundle\Entity\Block',
            'templateList' => array()
        ));
    }

    public function getName()
    {
        return 'kitpagesCmsCreateBlock';
    }
}
