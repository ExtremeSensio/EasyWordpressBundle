<?php

namespace EasyWordpressBundle\Service;


class WordpressNav
{
    public function registerNavMenus($navList = [])
    {
        register_nav_menus($navList);
    }
}
