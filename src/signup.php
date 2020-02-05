<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$props = array();
$props['title'] = 'Signup';

echo $twig->render('signup.html', $props);