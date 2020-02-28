<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/Admin.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$admin = new Admin($db);

if ($admin->loggedIn()) {
    if ($_SESSION['type'] == 'admin') {
        $data = [
        'title' => 'Profile Page',
        'loggedIn' => true,
        'userData' => $_SESSION,
        'users' => $admin->getUsers(),
        ];
        
        echo $twig->render('main/usersPage.html', $data);
    } else {
        header('Location: index.php?access=false');
    }
} else {
    header('Location: index.php?loggedIn=false');
}