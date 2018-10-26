<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Validator\Constraints;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueCollectionPropertyValidator extends ConstraintValidator
{
    private $property_accessor;

    /**
     * Create a new unique property validator.
     *
     * @param PropertyAccessorInterface $property_accessor The property accessor service.
     */
    public function __construct(PropertyAccessorInterface $property_accessor)
    {
        $this->property_accessor = $property_accessor;
    }

    /**
     * Validate a constraint.
     *
     * @param mixed      $value      The value being passed in.
     * @param Constraint $constraint The constraint to be checked.
     *
     * @return void
     */
    public function validate($value, Constraint $constraint): void
    {
        // Build a list of values.
        $item_values = [];
        foreach ($value as $item) {
            $item_value = $this->property_accessor->getValue($item, $constraint->property);

            // If any value is repeated build a violation.
            if (isset($item_values[$item_value])) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $constraint->property)
                    ->addViolation();
            }
            $item_values[$item_value] = true;
        }
    }
}
