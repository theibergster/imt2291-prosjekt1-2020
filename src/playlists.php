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


if ($user->loggedIn()) {
    $data = [
        'title' => 'My Playlists',
        'loggedIn' => true,
        'userData' => $_SESSION,
        'get' => $_GET
    ];

    echo $twig->render('main/playlistsPage.html', $data);
} else {
    header('Location: index.php?loggedIn=false');
}