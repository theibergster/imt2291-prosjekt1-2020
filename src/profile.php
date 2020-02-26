<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/Playlist.php';
require_once 'classes/video.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$playlist = new Playlist($db);
$video = new Video($db);


if ($user->loggedIn()) {
    $data = [
        'title' => 'Profile Page',
        'loggedIn' => true,
        'userData' => $_SESSION,
        'search_page' => 'index',
        'playlists' => $playlist->getPlaylists(array('user' => $_SESSION['uid'], 'limit' => '30')),
        'videos' => $video->getvideos(array('user' => $_SESSION['uid'], 'limit' => '50')),
    ];

    echo $twig->render('profile/profilePage.html', $data);
} else {
    header('Location: index.php?loggedIn=false');
}