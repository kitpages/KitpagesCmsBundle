<?php
namespace Kitpages\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreateZoneType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('slug', 'text');
        $builder->add(
            'canonicalUrl',
            'text',
            array(
                'required' => false,
                'attr' => array('class'=>'kit-cms-advanced'),
            )
        );
        $builder->add(
            'title',
            'text',
            array(
                'required' => false,
                'attr' => array('class'=>'kit-cms-advanced'),
            )
        );
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Kitpages\CmsBundle\Entity\Zone'
        ));
    }

    public function getName()
    {
        return 'kitpagesCmsCreateZone';
    }
}
