<?php
namespace Kitpages\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreatePageTechnicalType extends AbstractType
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
        if (empty($options['parent_id'])) {
            $builder->add(
                'language',
                'text',
                array(
                    'label' => "Page language",
                    'attr' => array('class'=>'kit-cms-advanced')
                )
            );
        }
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
            'data_class' => 'Kitpages\CmsBundle\Entity\Page',
            'parent_id' => null
        ));
    }

    public function getName()
    {
        return 'kitpagesCmsCreatePageTechnical';
    }
}
