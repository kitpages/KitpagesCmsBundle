<?php
namespace Kitpages\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditPageType extends AbstractType
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
        $builder->add(
            'forcedUrl',
            'text',
            array(
                'label' => 'Forced Url',
                'required' => false,
                'attr' => array(
                    'class'=>'kit-cms-advanced',
                    'size' => '100'
                ),
                'error_bubbling' => true
            )
        );
        $builder->add(
            'title',
            'text',
            array(
                'label' => "Title of the page",
                'attr' => array("size" => '100')
            )
        );
        $builder->add(
            'isInNavigation',
            'checkbox',
            array(
                'label' => "Display in navigation ?",
                'required' => false
            )
        );
        $builder->add(
            'menuTitle',
            'text',
            array(
                'label' => 'Page name in the navigation',
                'required' => false
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

        $builder->add(
            'language',
            'text',
            array(
                'label' => "Page language",
                'attr' => array('class'=>'kit-cms-advanced')
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
        return 'kitpagesCmsEditPage';
    }
}
