<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/boot.php';

(function () {
    global $kernel;
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
})();
