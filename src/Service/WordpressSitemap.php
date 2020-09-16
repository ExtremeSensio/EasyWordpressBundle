<?php

namespace EasyWordpressBundle\Service;


class WordpressSitemap
{
    /**
     * @param null|int $idPage
     * @param int $lvl
     */
    public function printLevel($idPage = null, $lvl = 1)
    {
        $parameters = [
            'post_type'   => 'page',
            'post_status' => 'publish',
            'nopaging'    => true,
            'orderby'     => 'menu_order',
            'order'       => 'ASC',
        ];

        $args    = http_build_query($parameters);
        $siteMap = [];

        if (! isset($idPage)) {
            $args .= '&post_parent=0';
        } else {
            $args .= "&post_parent=$idPage";
        }

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $excludeFromSitemap = get_field('excludeFromSitemap');

                if (isset($excludeFromSitemap) && $excludeFromSitemap === false) {
                    $siteMap[] = [
                        'level'     => $lvl,
                        'permalink' => $this->getPermalink(get_the_ID()),
                        'title'     => get_the_title(),
                    ];
                }
            }
            wp_reset_postdata();
        }

        return $siteMap;
    }

    /**
     * @param $id
     * @param string $contentType
     *
     * @return false|string
     */
    private function getPermalink($id, $contentType = 'page')
    {
        return get_permalink(apply_filters('wpml_object_id', $id, $contentType));
    }
}
