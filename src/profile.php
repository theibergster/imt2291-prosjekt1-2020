<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/Playlist.php';
require_once 'classes/video.php';
require_once 'classes/Subscription.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$playlist = new Playlist($db);
$video = new Video($db);
$subscription = new Subscription($db);

// Create new playlist
if (isset($_POST['new-playlist-submit'])) {
    $response = $playlist->createPlaylist($_POST['new-playlist-title']);
}

// Delete playlist
if (isset($_POST['delete-playlist-submit'])) {
    $playlist->deletePlaylist($_POST['playlist-id']);
}

if ($user->loggedIn()) {
    $data = [
        'title' => 'Profile Page',
        'loggedIn' => true,
        'userData' => $_SESSION,
        'search_page' => 'index',
        'playlists' => $playlist->getPlaylists(array('user' => $_SESSION['uid'], 'limit' => '30')),
        'videos' => $video->getVideos(array('user' => $_SESSION['uid'], 'limit' => '50')),
        'subscriptions' => $subscription->getSubscriptions(array('user' => $_SESSION['uid'], 'limit' => '30')),
        'likes' => $video->getLikedVideos(array('user' => $_SESSION['uid'], 'limit' => '50')),
        'response' => $response,
    ];

    echo $twig->render('profile/profilePage.html', $data);
} else {
    header('Location: index.php?loggedIn=false');
}