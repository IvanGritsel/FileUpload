<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 *@Annotation
 *
 * @Target("METHOD")
 */
class RequestMapping
{
    /**
     * @Required
     *
     * @Enum({"GET","POST","PUT","DELETE"})
     */
    public string $method;

    /**
     * @Required
     */
    public string $path;
}
