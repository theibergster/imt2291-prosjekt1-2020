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
$docTitle = 'Sign up';

if (isset($_POST['signup-submit'])) {
    $response = $user->addUser();
}

if ($user->loggedIn()) {
    header('Location: index.php?loggedIn=true');
} else {
    echo $twig->render('signup.html', ['title' => $docTitle, 'response' => $response]);
}