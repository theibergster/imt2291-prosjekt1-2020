<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';
require_once 'classes/video.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);
$docTitle = 'Videos';

$video = new Video($db);
if (isset($_POST['file-submit'])) {
    $response = $video->uploadVideo();
}

if ($user->loggedIn()) {
    echo $twig->render('includes/uploadVideos.html', ['title' => $docTitle, 'loggedIn' => true, 'response' => $response, 'userData' => $_SESSION]);
} else {
    header('Location: signup.php?loggedIn=false');
}