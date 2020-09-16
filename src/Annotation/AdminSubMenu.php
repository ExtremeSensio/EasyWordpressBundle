<?php

namespace EasyWordpressBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class AdminSubMenu
{
    /**
     * @Required
     *
     * @var string
     */
    public $name;

    public $menu_name;

    public $parent;

    public $permission;

    public $slug;

    public $icon;

    public $priority;
}
