<?php
namespace EasyWordpressBundle\Service;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class WordpressDataCollector extends DataCollector
{
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {

    }

    public function getPost()
    {
        return isset($this->data['post']) ? $this->data['post'] : null;
    }

    public function setPost($post)
    {
        $this->data['post'] = $post;
    }

    public function getController()
    {
        return isset($this->data['controller']) ? $this->data['controller'] : null;
    }

    public function setController($controller)
    {
        $this->data['controller'] = $controller;
    }

    public function getName()
    {
        return 'wordpress';
    }

    public function __call($methodName, $params)
    {
        $name = strtolower(substr($methodName, 3));
        if (substr($methodName, 0, 3) == 'set') {
            foreach ($params[0] as $key => $param)
            {
                if ($param instanceof FormView)
                {
                    $params[0][$key] = 'Error: Form cannot be serialized';
                }
            }
            $this->data[$name] = $params[0];
        } else if (substr($methodName, 0, 3) == 'get') {
            return isset($this->data[$name]) ? $this->data[$name] : null;
        } else {
            return isset($this->data[$methodName]) ? $this->data[$methodName] : null;
        }
    }
}
