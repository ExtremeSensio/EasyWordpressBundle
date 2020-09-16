<?php

namespace EasyWordpressBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class WordpressController extends Controller
{
    protected function getThemeDir()
    {
        return $this->getParameter('easy_wordpress.theme_directory');
    }

    protected function getPosts($itemCallback = null)
    {
        return $this->get('wordpress.helper')->getPosts($itemCallback);
    }

    protected function render($view, array $parameters = array(), Response $response = null)
    {
        if (isset($parameters['meta_title'])) {
            $meta_title = $parameters['meta_title'];
        } else if (isset($GLOBALS['post'])) {
            $meta_title = $GLOBALS['post']->post_title;
        }

        $renderCallback = function () use ($view, $parameters) {
            echo $this->get('templating')->render($view, $parameters);
        };

        $this->get('wordpress.data_collector')->setViewParameters($parameters);

        return $this->get('wordpress.helper')->getResponse(
            $meta_title,
            $renderCallback,
            $response
        );
    }
}
