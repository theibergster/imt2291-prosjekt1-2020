<?php
require_once 'User.php';

/**
 * Admin class for handling displaying, removing and validating users.
 */
class Admin extends User {
    public function __construct($db) {
        parent::__construct($db);
    }

    /**
     * Get list of users
     */
    public function getUsers() {}

    /**
     * Delete a user
     */
    public function deleteUser() {}

    /**
     * Validate a user's teacher type
     */
    public function validateUser() {}

}