<?php

namespace EasyWordpressBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;

/**
 * Class WordpressTwigExtension
 * @package EasyWordpressBundle\Twig\Extension
 */
class WordpressTwigExtension extends \Twig_Extension
{
    protected $em;

    /**
     * WordpressTwigExtension constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('do_shortcode', [$this, 'doShortcode']),
        ];
    }

    /**
     * @param $content
     * @param bool $ignoreHtml
     *
     * @return string
     */
    public function doShortcode($content, $ignoreHtml = false)
    {
        return do_shortcode($content, $ignoreHtml);
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            // Head
            new \Twig_SimpleFunction(
                'wp_head', function () {
                    do_action('wp_head');
                }
            ),

            // Footer
            new \Twig_SimpleFunction(
                'wp_footer', function () {
                    do_action('wp_footer');
                }
            ),

            // Content
            new \Twig_SimpleFunction('get_content', array($this, 'getContent'), array('is_safe' => ['html'])),

            // Nav
            new \Twig_SimpleFunction(
                'wp_nav_menu',
                function (
                    $themeLocation,
                    $menu,
                    $menuClass = '',
                    $menuId = '',
                    $container = '',
                    $containerClass = '',
                    $containerId = '',
                    $echo = true,
                    $fallback = 'wp_page_menu',
                    $before = '',
                    $after = '',
                    $linkBefore = '',
                    $linkAfter = '',
                    $depth = '',
                    $walker = ''
                ) {
                    $params = [
                        'theme_location'  => $themeLocation,
                        'menu'            => $menu,
                        'menu_class'      => $menuClass,
                        'menu_id'         => $menuId,
                        'container'       => $container,
                        'container_class' => $containerClass,
                        'container_id'    => $containerId,
                        'echo'            => $echo,
                        'fallback_cb'     => $fallback,
                        'before'          => $before,
                        'after'           => $after,
                        'link_before'     => $linkBefore,
                        'link_after'      => $linkAfter,
                        'depth'           => $depth,
                        'walker'          => $walker,
                    ];
                    wp_nav_menu($params);
                }
            ),

            // Translate
            new \Twig_SimpleFunction(
                '_e', function ($text, $domain = null) {
                    _e($text, $domain);
                }
            ),
            new \Twig_SimpleFunction(
                '__', function ($text, $domain = null) {
                    return __($text, $domain);
                }
            ),

            // Blog Infos
            new \Twig_SimpleFunction(
                'bloginfo', function ($show = '') {
                    bloginfo($show);
                }
            ),

            // ACF Get Fields
            new \Twig_SimpleFunction(
                'get_field', function ($fieldName, $postId = null, $formatValue = true) {
                    if (! $postId) {
                        $postId = $GLOBALS['post']->ID;
                    }

                    return get_field($fieldName, $postId, $formatValue);
                }
            ),

            // ACF Doctrine field
            new\Twig_SimpleFunction(
                'wp_entity', function ($fieldName, $postId = null, $formatValue = true) {
                    if (! $postId) {
                        $postId = $GLOBALS['post']->ID;
                    }

                    $field = get_field($fieldName, $postId, $formatValue);

                    $fieldArray = explode(':', $field);

                    list($rawName, $id) = $fieldArray;
                    $entityName = str_replace('_', '\\', $rawName);

                    $entity = $this->em
                        ->getRepository($entityName)
                        ->find($id);

                    return $entity;
                }
            ),

            // Get Thumbnail by id
            new \Twig_SimpleFunction(
                'wp_thumbnail', function ($id) {
                    $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($id));
                    $url   = $thumb['0'];

                    return $url;
                }
            ),

            // Excerpt
            new \Twig_SimpleFunction(
                'wp_excerpt', function ($limit) {
                    $excerpt = explode(' ', get_the_excerpt(), $limit);

                    if (count($excerpt) >= $limit) {
                        array_pop($excerpt);
                        $excerpt = implode(' ', $excerpt);
                    } else {
                        $excerpt = implode(' ', $excerpt);
                    }

                    $string = preg_replace('`\[[^\]]*\]`', '', $excerpt);

                    return $string;
                }
            ),

            // Truncate
            new \Twig_SimpleFunction(
                'wp_truncat', function ($text, $numb = 30) {
                    if (strlen($text) > $numb) {
                        $text = substr($text, 0, $numb);
                        $text = substr($text, 0, strrpos($text, " "));
                        $etc  = " ...";
                        $text = $text.$etc;
                    }

                    return $text;
                }
            ),

            // Date
            new \Twig_SimpleFunction(
                'wp_date', function ($date, $full = false) {
                    if ($full == false) {
                        $dateformatstring = "d M Y";
                    } else {
                        $dateformatstring = "D d M Y";
                    }
                    $unixTimestamp = strtotime($date);

                    return date_i18n($dateformatstring, $unixTimestamp);
                }
            ),

            // Get page ID with the current language
            new \Twig_SimpleFunction(
                'wp_get_pageId', function ($id) {
                    return apply_filters('wpml_object_id', $id, 'post');
                }
            ),

            // Returns URL of the page by id with the current language
            new \Twig_SimpleFunction(
                'wp_get_permalink', function ($id, $contentType = 'page') {
                    return get_permalink(apply_filters('wpml_object_id', $id, $contentType));
                }
            ),
        ];
    }

    /**
     * @return mixed|void
     */
    public function getContent()
    {
        return apply_filters('the_content', get_the_content());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wordpress_twig';
    }
}
