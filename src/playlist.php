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

// edit playlist description
if (isset($_POST['edit-description-submit'])) {
    $desc['desc'] = $_POST['new-description'];
    $desc['pid'] = $_POST['description-pid'];

    $playlist->editPlaylistDescription($desc);
}


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

    // Edit playlist description
    if (isset($_POST['go-to-edit-description-submit'])) {
        $data['description'] = [
            'data' => 'data',
        ];
        echo $twig->render('editPlaylistDescription.html', $data);
    } else {

        echo $twig->render('playlist.html', $data);
    }
} else {
    header('Location: index.php?loggedIn=false');
}