<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/Playlist.php';
require_once 'classes/Search.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$playlist = new Playlist($db);
$search = new Search($db);

// Search
if (isset($_POST['search-submit']) && !empty($_POST['search-query'])) {
    $playlist = $search->playlistSearch($_POST['search-query']);
} else {
    $playlist = $playlist->getPlaylists(array('user' => 'all', 'limit' => '30'));
}

// Render
if ($user->loggedIn()) {
    $data = [
        'title' => 'Recent Playlists',
        'loggedIn' => true,
        'userData' => $_SESSION,
        'get' => $_GET,
        'playlists' => $playlist,
        'search_page' => 'playlists',
    ];

    echo $twig->render('main/playlistsPage.html', $data);
} else {
    header('Location: index.php?loggedIn=false');
}