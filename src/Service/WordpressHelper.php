<?php

namespace EasyWordpressBundle\Service;

use EasyWordpressBundle\Annotation\AdminMenu;
use EasyWordpressBundle\Annotation\AdminSubMenu;
use EasyWordpressBundle\Annotation\CustomPostType;
use EasyWordpressBundle\Annotation\Functions;
use EasyWordpressBundle\Annotation\TemplateName;
use EasyWordpressBundle\Wordpress\WordpressResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

class WordpressHelper
{
    /**
     * @var Kernel
     */
    private $kernel;

    private $wpLoaded = false;
    private $wpRouted = false;

    private $templates;
    private $functions;

    /**
     * @var Response The Response of the sub Request passed to Wordpress.
     */
    private $wpResponse;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function get($id)
    {
        return $this->kernel->getContainer()->get($id);
    }

    public function getPosts($itemCallback = null)
    {
        $posts = [];
        global $post;
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                $posts[] = $post;
                if (is_callable($itemCallback)) {
                    $itemCallback($post);
                }
            }
            wp_reset_postdata();
        }
        return $posts;
    }

    public function getTemplatesList()
    {
        if ($this->templates === null) {
            $this->parseAnnotations();
        }

        return $this->templates;

    }

    public function loadControllersFunctions()
    {
        if ($this->functions === null) {
            $this->parseAnnotations();
        }

        foreach ($this->functions as $callback) {
            call_user_func($callback);
        }
    }

    public function getResponse($title = null, $renderCallback = null, WordpressResponse $response = null)
    {
        global $post;
        $html = $this->getContent($renderCallback);

        $yoastOverride = $this->kernel->getContainer()->getParameter('easy_wordpress.yoast_title_override');

        // Checking if $post exists, as during 404 it doesn't.
        if ($post) {
            ($yoastOverride === true) ? $title =  get_post_meta($post->ID, '_yoast_wpseo_title', true) : $title = $title;
        }

        if ($title) {
            $html = preg_replace('/<title>(.*)<\/title>/', "<title>{$title}</title>", $html);
        }

        $is404 = false;
        if ($this->wpRouted == false) {
            // Symfony Routing : Remove body class because wordpress generate 404
            $html = str_replace('error404 ', '', $html);
        } else {
            $is404 = $GLOBALS['wp_query']->is_404();
        }

        if (null === $response) {
            $response = $this->wpResponse ?? new WordpressResponse();
        }

        if ($is404 || !($response->isSuccessful() || $response->isRedirection())) {
            $statusCode = $is404 ? 404 : $response->getStatusCode();

            throw new HttpException($statusCode, null, null, iterator_to_array($response->headers));
        }

        $response->setContent($html);
        $response->setStatusCode($is404 ? 404 : $response->getStatusCode());

        return $response;
    }

    public function registerControllerRouting()
    {
        add_action('template_include', function ($templateFile) {
            $this->wpRouted = true;
            $templates = $this->getTemplatesList();
            $filename = basename($templateFile);
            $action = isset($templates[$filename]) ? $templates[$filename] : false;

            global $post;
            $this->get('wordpress.data_collector')->setPost($post);

            if ($action) {
                $controllerClass = $templates[$filename]['controllerClass'];
                $method = $templates[$filename]['methodName'];
                $this->get('wordpress.data_collector')->setController(sprintf('%s::%s', $controllerClass, $method));

                /** @var Request $masterRequest */
                $masterRequest = $this->get('request_stack')->getMasterRequest();
                $request = $masterRequest->duplicate();
                $request->attributes->set('_controller', sprintf('%s::%s', $controllerClass, $method));
                $request->attributes->set('post', $post);

                $httpKernel = $this->get('http_kernel');
                $response = $httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
                $this->wpResponse = $response;
                $response->sendContent();
            } else {
                $this->get('wordpress.data_collector')->setController($filename);
                return $templateFile;
            }
        });
    }

    public function registerStylesheets()
    {
        add_action('wp_enqueue_scripts', function () {
            $parent_style = 'parent-style';

            wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
            wp_enqueue_style('child-style',
                get_stylesheet_directory_uri() . '/style.css',
                array($parent_style)
            );
        });
    }

    public function catchAllAction()
    {
        if ( ! defined('WP_USE_THEMES')) {
            define('WP_USE_THEMES', true);
        }

        return $this->getResponse();
    }

    public function boot()
    {
        if ($this->wpLoaded == false) {
            $this->parseAnnotations();
            $wordpressDir = $this->kernel->getContainer()->getParameter('easy_wordpress.wordpress_directory');

            require_once($wordpressDir . 'wp-load.php');
            $this->registerControllerRouting();
            $this->loadControllersFunctions();

            $this->wpLoaded = true;

        }
    }

    private function getContent($renderCallback = null)
    {
        $wordpressDir = $this->kernel->getContainer()->getParameter('easy_wordpress.wordpress_directory');

        ob_start();
        require_once($wordpressDir . 'wp-blog-header.php');
        if ($renderCallback) {
            $renderCallback();
        }
        $html = ob_get_contents();
        ob_clean();

        return $html;
    }

    private function parseAnnotations()
    {
        $this->functions = [];
        $this->templates = [];

        $controllerNamespace = $this->kernel->getContainer()->getParameter('easy_wordpress.controllers_namespace');
        $reader = $this->get('annotation_reader');

        $controllerDir = sprintf('%s/../src/%s', $this->kernel->getRootDir(), str_replace('\\\\', '/', $controllerNamespace));
        $controllerNamespace = str_replace('\\\\', '\\', $controllerNamespace);

        foreach (glob($controllerDir . '*Controller.php') as $controllerFile) {
            $controllerName = substr(basename($controllerFile), 0, -4);
            $controllerClass = $controllerNamespace . $controllerName;
            $classReflection = new \ReflectionClass($controllerClass);

            foreach ($classReflection->getMethods() as $method) {
                $methodReflection = new \ReflectionMethod($controllerClass, $method->name);
                $annotations = $reader->getMethodAnnotations($methodReflection);

                if (count($annotations) > 0) {
                    if (defined('WP_ADMIN') && WP_ADMIN === true) {
                        foreach ($annotations as $annotation) {
                            if ($annotation instanceof AdminMenu) {
                                add_action('admin_menu', function() use ($annotation, $controllerClass, $method) {
                                    add_menu_page($annotation->name,
                                        $annotation->menu_name,
                                        $annotation->permission,
                                        $annotation->slug,
                                        [$controllerClass, $method->name],
                                        $annotation->icon,
                                        $annotation->priority);
                                });
                            } elseif ($annotation instanceof AdminSubMenu) {
                                add_action('admin_menu', function() use ($annotation, $controllerClass, $method) {
                                    add_submenu_page($annotation->parent,
                                        $annotation->name,
                                        $annotation->menu_name,
                                        $annotation->permission,
                                        $annotation->slug,
                                        [$controllerClass, $method->name]);
                                });
                            }
                        }
                    }

                    foreach ($annotations as $annotation) {
                        if ($annotation instanceof CustomPostType
                            || $annotation instanceof TemplateName
                            || $annotation instanceof Functions
                        ) {
                            if ($annotation instanceof Functions) {
                                $this->functions[] = [$this->get("easy_wordpress.controllers.{$controllerName}"), $method->name];
                                continue;
                            } else if ($annotation instanceof CustomPostType) {
                                $filename = 'single-'.strtolower($annotation->name) . '.php';
                            } else if ($annotation instanceof TemplateName) {
                                $slugName = str_replace(' ', '-', strtolower($annotation->name));
                                $filename = 'page_' . $slugName . '.php';
                            }
                            $this->templates[$filename] = [
                                'name' => $annotation->name,
                                'controllerClass' => $controllerClass,
                                'methodName' => $method->name,
                            ];
                        }
                    }
                }
            }
        }

        return $this;
    }
}
