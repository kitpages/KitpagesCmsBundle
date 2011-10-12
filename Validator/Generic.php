<?php

namespace Kitpages\CmsBundle\Validator;

use Symfony\Component\Validator\Constraint;


class Generic extends Constraint
{
    public $message = 'Error : For the value of "%property%".';
    public $property = null;
    public $method = null;
    public $service = null;

    public function initialize()
    {
        
    }

    public function getDefaultOption()
    {
        return 'property';
    }

    public function getRequiredOptions()
    {
        return array('property');
    }

    public function validatedBy()
    {
        return $this->service;
    }

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
