<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$props = array();
$props['title'] = 'Welcome';

if (isset($_POST['username'])) {
    $props['username'] = $_POST['username'];
}

if (isset($_SESSION['uid'])) {
    $props['logggedIn'] = true;
}

$props['loggedIn'] = true;
$props['username'] = 'admin';

echo $twig->render('index.html', $props);