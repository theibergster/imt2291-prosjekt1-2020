<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/Playlist.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$playlist = new Playlist($db);


// Render
if ($user->loggedIn()) {
    $data = [
        'title' => 'Playlist',
        'loggedIn' => true,
        'userData' => $_SESSION,
        'get' => $_GET,
        'playlist' => $playlist->getPlaylistInfo($_GET['id']),
        'playlist_videos' => $playlist->getVideosInPlaylist($_GET['id']),
        'search_page' => 'playlists',
    ];

    echo $twig->render('playlist.html', $data);
} else {
    header('Location: index.php?loggedIn=false');
}