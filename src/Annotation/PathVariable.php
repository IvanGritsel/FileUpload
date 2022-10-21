<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 *
 * @Target("METHOD")
 */
class PathVariable
{
    /**
     * @Required
     */
    public string $variableName;
}
