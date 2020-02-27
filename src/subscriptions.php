<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/Playlist.php';
require_once 'classes/Search.php';
require_once 'classes/Subscription.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$playlist = new Playlist($db);
$search = new Search($db);
$subscription = new Subscription($db);

// Search -> subscriptionSearch
if (isset($_POST['search-submit']) && !empty($_POST['search-query'])) {
    $getSubscriptions = $search->playlistSearch($_POST['search-query']);
} else {
    // subscriptions -> getSubscriptions
    $getSubscriptions = $subscription->getSubscriptions(array('user' => $_SESSION['uid'], 'limit' => '30'));
}

// removeSubscription ->
if (isset($_POST['delete-playlist-submit'])) {
    $playlist->deletePlaylist($_POST['playlist-id']);
}

// Render
if ($user->loggedIn()) {
    $data = [
        'title' => 'Recent Playlists',
        'loggedIn' => true,
        'userData' => $_SESSION,
        'get' => $_GET,
        'subscriptions' => $getSubscriptions,
        'search_page' => 'subscriptions', // TODO: Add search for subs
    ];

    echo $twig->render('main/subscriptionsPage.html', $data);
} else {
    header('Location: index.php?loggedIn=false');
}