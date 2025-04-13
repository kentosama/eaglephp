<?php

use Eagle\Router;


Router::scope('/', NULL, function()
{
    return [
        ['path' => '/', 'url' => ['controller' => 'pages', 'action' => 'home']],
    ];
});

Router::scope('/admin', NULL, function()
{
    return [
        ['path' => '/admin', 'url' => ['controller' => 'pages', 'action' => 'home']],
    ];
});