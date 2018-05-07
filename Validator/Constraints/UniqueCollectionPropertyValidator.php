<?php

namespace Bkstg\ScheduleBundle\Validator\Constraints;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueCollectionPropertyValidator extends ConstraintValidator
{
    private $property_accessor;

    public function __construct(PropertyAccessorInterface $property_accessor)
    {
        $this->property_accessor = $property_accessor;
    }

    public function validate($value, Constraint $constraint)
    {
        $item_values = [];
        foreach ($value as $item) {
            $item_value = $this->property_accessor->getValue($item, $constraint->property);
            if (isset($item_values[$item_value])) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $constraint->property)
                    ->addViolation();
            }
            $item_values[$item_value] = true;
        }
    }
}
