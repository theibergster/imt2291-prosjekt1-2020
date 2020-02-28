<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/video.php';
require_once 'classes/Rating.php';
require_once 'classes/Playlist.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$video = new Video($db);
$rating = new Rating($db);
$playlist = new Playlist($db);

// Rate video
if (isset($_POST['rate-submit'])) {
    $rating->rateVideo(array(
        'vid' => $_GET['id'],
        'rating-value' => $_POST['rating-value']
    ));
}

// Like / Dislike video
if (isset($_POST['like-submit'])) {
    $rating->likeVideo($_GET['id']);
} elseif (isset($_POST['dislike-submit'])) {
    $rating->dislikeVideo($_GET['id']);
}

// Video comments
if (isset($_POST['comment-submit'])) {
    $response = $video->addComment($_GET['id']); 
}

// Delete comment
if (isset($_POST['delete-comment-submit'])) {
    $video->deleteComment(array(
        'uid' => $_POST['comment-uid'],
        'vid' => $_POST['comment-vid'],
        'time' => $_POST['comment-time']
    ));
} 

// Add video to playlist
if (isset($_POST['add-to-playlist-submit'])) {
    $playlist->addToPlaylist(array(
        'vid' => $_POST['video-id'],
        'pid' => $_POST['playlist-id']
    ));
}

// Render
if ($user->loggedIn()) {
    $data = [
        'title' => 'title',
        'loggedIn' => true,
        'response' => $response,
        'userData' => $_SESSION,
        'get' => $_GET,
        'video' => $video->getVideoInfo($_GET['id']),
        'rating' => [
            'total' => $rating->getTotalRating($_GET['id']),
            'user' => $rating->getUserRating($_GET['id']),
        ],
        'liked' => [
            'check_user' => $rating->checkLike($_GET['id']),
            'get_total' => $rating->getTotalLikes($_GET['id']),
        ],
        'playlists' => $playlist->getPlaylists(array('user' => $_SESSION['uid'], 'limit' => '100')),
        'comments' => $video->getComments($_GET['id']),
        'search_page' => 'index',
    ];
    
    echo $twig->render('video.html', $data);
} else {
    header('Location: signup.php?loggedIn=false');
}