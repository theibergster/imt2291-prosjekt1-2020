<?php
require_once('User.php');

/**
 * Admin class for handling displaying, removing and validating users.
 */
class Admin extends User {
    public function __construct($db) {
        parent::__construct($db);
    }

    public function getUsers() {}

    public function deleteUser() {}

    public function validateUser() {}

}