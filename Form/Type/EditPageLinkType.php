<?php
namespace Kitpages\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditPageLinkType extends AbstractType
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


        $builder->add(
            'linkUrl',
            'text',
            array(
                'label' => 'Url link',
                'required' => false
            )
        );
        $builder->add(
            'isLinkUrlFirstChild',
            'checkbox',
            array(
                'required' => false,
                'label' => "Link automatic on first child",
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
        return 'kitpagesCmsEditPageLink';
    }
}
