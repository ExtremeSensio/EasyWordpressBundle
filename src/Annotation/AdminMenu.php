<?php

namespace EasyWordpressBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class AdminMenu
{
    /**
     * @Required
     *
     * @var string
     */
    public $name;

    public $menu_name;

    public $permission;

    public $slug;

    public $icon;

    public $priority;
}
