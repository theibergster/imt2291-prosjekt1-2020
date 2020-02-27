<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/Playlist.php';
require_once 'classes/Subscription.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$playlist = new Playlist($db);
$subscription = new Subscription($db);

// edit playlist description
if (isset($_POST['edit-description-submit'])) {
    $playlist->editPlaylistDescription(array(
        'desc' => $_POST['new-description'],
        'pid' => $_POST['description-pid']
    ));
}

// remove video from playlist
if (isset($_POST['remove-from-playlist-submit'])) {
    $removeData['pid'] = $_POST['playlist-id'];
    $removeData['vid'] = $_POST['video-id'];
    $playlist->removeFromPlaylist($removeData);
}

// Subscribe / unsubscribe to playlist
if (isset($_POST['subscribe-submit'])) {
    $subscription->subscribe(array(
        'pid' => $_POST['sub-playlist-id'],
        'uid' => $_SESSION['uid']
    ));
} elseif (isset($_POST['unsubscribe-submit'])) {
    $subscription->unsubscribe(array(
        'pid' => $_POST['sub-playlist-id'],
        'uid' => $_SESSION['uid']
    ));
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