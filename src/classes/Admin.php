<?php
/**
 * Admin class > probably don't need it. Delete i guess
 */
class Admin extends User {
    public function __construct($db, $type) {
        parent::__construct($db);
    }
}