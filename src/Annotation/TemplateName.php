<?php

namespace EasyWordpressBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class TemplateName
{
    /**
     * @Required
     *
     * @var string
     */
    public $name;
}
