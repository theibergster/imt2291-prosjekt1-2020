<?php

session_start();

require_once '../../vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/User.php';

$loader = new \Twig\Loader\FilesystemLoader('./views');
$twig = new \Twig\Environment($loader, [
    /* 'cache' => './compilation_cache', // Only enable cache when everything works correctly */
]);

$db = DB::getDBConnection();
$user = new User($db);

// Validate and signup
if (isset($_POST['signup-submit'])) {
    $validate_response = $user->validateUserSignup($_POST['name'], $_POST['email'], $_POST['pwd'], $_POST['pwd-repeat']);
    if ($validate_response['status'] == 'Success') {
        $response = $user->addUser(array(
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'pwd' => $_POST['pwd'],
            'type' => $_POST['user-type']));
    } else {
        $response = $validate_response;
    }
}

// Render
if ($user->loggedIn()) {
    header('Location: index.php?loggedIn=true');
} else {
    $data = [
        'title' => 'Create New User',
        'response' => $response,
        'get' => $_GET
    ];
    
    echo $twig->render('main/signupPage.html', $data);
}