<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 *
 * @Target("METHOD")
 */
class RequestBodyVariable
{
    /**
     * @Required
     */
    public string $variableName;
}
