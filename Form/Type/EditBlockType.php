<?php
namespace Kitpages\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'slug',
            'text',
            array(
                'attr' => array('class'=>'kit-cms-advanced'),
                'error_bubbling' => true
            )
        );
        $builder->add('template', 'choice',array(
            'attr' => array('class'=>'kit-cms-advanced'),
            'choices' => $options['templateList'],
            'required' => true
        ));

        $builder->add(
            'canonicalUrl',
            'text',
            array(
                'attr' => array('class'=>'kit-cms-advanced'),
                'required' => false
            )
        );
        if ($options['formTypeCustom'] != null) {
            $builder->add('data', 'collection', array(
                'type' => $options['formTypeCustom'],
            ));
        }
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Kitpages\CmsBundle\Entity\Block',
            'templateList' => array(),
            'formTypeCustom' => null
        ));
    }

    public function getName()
    {
        return 'kitpagesCmsEditBlock';
    }
}
