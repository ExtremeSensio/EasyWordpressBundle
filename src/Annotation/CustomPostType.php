<?php

namespace EasyWordpressBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class CustomPostType
{
    /**
     * @Required
     *
     * @var string
     */
    public $name;
}
