<?php
namespace Kitpages\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditPageTechnicalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'slug',
            'text',
            array(
                'label' => 'Slug of the technical page'
            )
        );
        $builder->add(
            'language',
            'text',
            array(
                'label' => "Page language",
                'attr' => array('class'=>'kit-cms-advanced')
            )
        );
        $builder->add(
            'isInNavigation',
            'checkbox',
            array(
                'required' => false,
                'label' => "Is in navigation ?",
                'attr' => array('class'=>'kit-cms-advanced')
            )
        );
        $builder->add(
            'menuTitle',
            'text',
            array(
                'required' => false,
                'label' => "Name in navigation",
                'attr' => array('class'=>'kit-cms-advanced')
            )
        );
        $builder->add(
            'parent_id',
            'text',
            array(
                'label' => 'Id of the parent page',
                'attr' => array('class'=>'kit-cms-advanced'),
                'mapped' => false
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
            'data_class' => 'Kitpages\CmsBundle\Entity\Page',
            'formTypeCustom' => null
        ));
    }

    public function getName()
    {
        return 'kitpagesCmsEditPageTechnical';
    }
}
