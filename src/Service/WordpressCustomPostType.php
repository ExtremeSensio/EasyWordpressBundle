<?php

namespace EasyWordpressBundle\Service;

/**
 * Class WordpressCustomPostType
 *
 * @package EasyWordpressBundle\Service
 */
class WordpressCustomPostType
{
    /**
     * @param $name
     * @param $slug
     * @param array $labels
     * @param array $args
     */
    function registerPostType($name, $slug, $labels = [], $args = [], $exclude = false)
    {
        $name   = ucwords(str_replace('_', ' ', $name));
        $plural = $name.'s';

        $labels = array_merge(
            [
                'name'               => _x($plural, 'post type general name'),
                'singular_name'      => _x($name, 'post type singular name'),
                'add_new'            => _x('Ajouter', strtolower($name)),
                'add_new_item'       => __('Ajouter'),
                'edit_item'          => __('Editer'),
                'new_item'           => __('Ajouter'),
                'all_items'          => __('Tous '),
                'view_item'          => __('Voir'.$name),
                'search_items'       => __('Rechercher '),
                'not_found'          => __('Pas de '.strtolower($plural).' trouvé(e)s'),
                'not_found_in_trash' => __('Pas de '.strtolower($plural).' trouvé(e)s dans la corbeille'),
                'parent_item_colon'  => '',
                'menu_name'          => $plural,
            ],
            $labels
        );
        $args   = array_merge(
            [
                'label'               => $plural,
                'labels'              => $labels,
                'public'              => true,
                'show_ui'             => true,
                'supports'            => ['title', 'editor', 'thumbnail', 'excerpt'],
                'show_in_nav_menus'   => true,
                '_builtin'            => false,
                'exclude_from_search' => $exclude,
            ],
            $args
        );
        register_post_type($slug, $args);
    }
}
