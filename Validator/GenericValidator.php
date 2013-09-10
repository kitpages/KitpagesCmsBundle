<?php

namespace Kitpages\CmsBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * GenericValidator
 */
class GenericValidator extends ConstraintValidator
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param Manager $manager
     */
    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    /**
     * Sets the manager
     *
     * @param Manager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * Gets the manager
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Indicates whether the constraint is valid
     *
     * @param Entity     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $method = $constraint->method;
        if (!$this->getManager()->$method($value, $constraint)) {
            $this->setMessage($constraint->message, array(
                '%property%' => $constraint->property
            ));
            return false;
        }

        return true;
    }
}
