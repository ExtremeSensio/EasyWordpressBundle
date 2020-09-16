<?php

namespace EasyWordpressBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Functions
{
    /**
     * @var string
     */
    public $name;
}
