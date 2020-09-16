<?php
use Symfony\Component\Debug\Debug;

umask(0000);

function symfony($id)
{
    static $container;

    if ($id instanceof \Symfony\Component\DependencyInjection\ContainerInterface) {
        $container = $id;
        return;
    }

    return $container->get($id);
}

$loader = require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../AppKernel.php';

// load the environmental variables
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();
$env = $_SERVER['SYMFONY_ENV'];
$debug = $_SERVER['SYMFONY_DEBUG'];

if ($debug) {
    Debug::enable();
}

$kernel = new AppKernel($env, $debug);
$kernel->boot();

symfony($kernel->getContainer());
