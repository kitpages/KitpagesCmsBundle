<?php

/*
 * This file is part of the Kitpages.
 *
 * (c) Philippe Le Van (@plv)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kitpages\CmsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

use Kitpages\CmsBundle\Entity\Block;

class BlockAdmin extends Admin
{

    protected $list = array(
        'id' => array('identifier' => true),
        'label',
        'isPublished',
        'isActive',
        'realModificationDate'
    );

    protected $form = array(
        'label',
        'template'
    );

    protected $formGroups = array(
        'General' => array(
            'fields' => array('label', 'template')
        )
    );

    protected $filter = array(
        'label'
    );

    public function getBatchActions()
    {
        return array(
            'enabled'   => 'enable_comments',
            'disabled'  => 'disabled_comments',
        );
    }
}