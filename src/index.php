<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$docTitle = 'Front page';


if ($user->loggedIn()) {
    echo $twig->render('index.html', ['title' => $docTitle, 'loggedIn' => true, 'userData' => $_SESSION]);
} else {
    echo $twig->render('index.html', ['title' => $docTitle, 'loggedIn' => false]);
}