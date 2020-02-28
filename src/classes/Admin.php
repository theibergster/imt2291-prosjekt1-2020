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
     * Get a complet list of users from database.
     * @return array row — rows from db.
     */
    public function getUsers() {
        $sql = 'SELECT users.*
                FROM users';
        
        $sth = $this->db->prepare($sql);
        $sth->execute();

        if ($row = $sth->fetchAll(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            return array('error' => 'error');
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser() {}

    /**
     * Validate a user's teacher type
     */
    public function validateUser() {}

}