<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/video.php';
require_once 'classes/Rate.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$video = new Video($db);
$rate = new Rate($db);

// Rate TODO:
if (isset($_POST['rate-submit'])) {
    $rate->rateVideo($_GET['id']);
}

// Render
if ($user->loggedIn()) {
    $data = [
        'title' => 'title',
        'loggedIn' => true,
        'response' => $response,
        'userData' => $_SESSION,
        'get' => $_GET,
        'video' => $video->getVideoInfo($_GET['id']), // video_info ?
        'rate' => [
            // 'total' => $rating->getTotalRating($_GET['id']),
            // 'user' => $rating->getUserRating($_GET['id']),
        ],
    ];
    
    echo $twig->render('video.html', $data);
} else {
    header('Location: signup.php?loggedIn=false');
}