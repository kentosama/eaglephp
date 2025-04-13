<?php

use Eagle\Router;


Router::scope('/', NULL, function()
{
    return [
        ['path' => '/', 'url' => ['controller' => 'pages', 'action' => 'index']],
        ['path' => '/avis', 'url' => ['controller' => 'reviews', 'action' => 'index']],
        ['path' => '/restaurants', 'url' => ['controller' => 'restaurants', 'action' => 'index']],
        ['path' => '/restaurants/:slug', 'url' => ['controller' => 'restaurants', 'action' => 'view'], 'pass' => ['slug']],
       
        ['path' => '/login', 'url' => ['controller' => 'users', 'action' => 'login']],
        ['path' => '/logout', 'url' => ['controller' => 'users', 'action' => 'logout']]
    ];
});

Router::scope('/admin', NULL, function()
{
    return [
        ['path' => '/admin', 'url' => ['controller' => 'pages', 'action' => 'index']],
        ['path' => '/admin/users', 'url' => ['controller' => 'users', 'action' => 'index']],
    ];
});