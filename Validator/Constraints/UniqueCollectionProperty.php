<?php

namespace Bkstg\ScheduleBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueCollectionProperty extends Constraint
{
    public $message = 'The collection property "{{ string }}" must be unique.';
    public $property = null;
}
