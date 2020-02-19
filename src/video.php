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

// which class tho..
$sql = 'SELECT videos.*, users.name
        FROM videos
        LEFT JOIN users ON videos.uploaded_by = users.id
        WHERE videos.id = ?';

$id = htmlspecialchars($_GET['id']);

$sth = $db->prepare($sql);
$sth->execute(array($id));

$row = $sth->fetch(PDO::FETCH_ASSOC);

// rate

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
        'video' => $row,
        'rate' => [
            'total' => $rating->getTotalRating($_GET['id']),
            'user' => $rating->getUserRating($_GET['id']),
        ],
    ];
    
    echo $twig->render('video.html', $data);
} else {
    header('Location: signup.php?loggedIn=false');
}