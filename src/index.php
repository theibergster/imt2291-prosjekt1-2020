<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/Admin.php';
require_once 'classes/Video.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$video = new Video($db);

// Render
if ($user->loggedIn()) {
    $data = [
        'title' => 'Recent Videos',
        'loggedIn' => true,
        'userData' => $_SESSION,
        'get' => $_GET,
        'videos' => $video->getVideos(array('user' => 'all', 'limit' => '50')),
    ];
    echo $twig->render('main/videosPage.html', $data);
} else {
    header('Location: signup.php?loggedIn=false');
}