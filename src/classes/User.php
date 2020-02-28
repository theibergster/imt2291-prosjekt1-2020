<?php
/**
 * Class for handling user login / logout, and user signup.
 */
class User {
    protected $db;
    protected $uid = -1;
    /**
     * Constructor handling access to db, and login / logout.
     */
    public function __construct($db) {
        $this->db = $db;
        // Login handler.
        if (isset($_POST['login-submit'])) {
            if (!empty($_POST['email']) || !empty($_POST['pwd'])) {
                $_SESSION = array();
                $this->login();
            }
        // Logout handler
        } else if (isset($_POST['logout-submit'])) {
            $this->uid = -1;
            session_start();
            session_destroy();
            session_unset();
            $_SESSION = array();
            header('Location: index.php?loggout=success');
        } else if (isset($_SESSION['uid'])) {
            $this->uid = $_SESSION['uid'];
        }
    }

    /**
     * Function for checking if user is logged in.
     * @return {bool}
     */
    public function loggedIn() {
        return $this->uid > -1;
    }

    /**
     * Function for logging in, and setting session values.
     * Checks if email exists in db, and if password is correct.
     * @return {array} assoc array with status.
     */
    public function login() {
        $email = htmlspecialchars($_POST['email']);
        $pwd = htmlspecialchars($_POST['pwd']);

        $sql = 'SELECT * FROM users 
                WHERE email=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($email));

        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($pwd, $row['password'])) {
                session_start();
                $_SESSION['uid'] = $row['id'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['type'] = $row['type'];
                $this->uid = $row['id'];
                return array('status'=>'Login success');
            } else {
                return array('status'=>'Failed', 'errorMsg'=>'Wrong password');
            }
        } else {
        return array('status'=>'Failed', 'errorMsg'=>'User does not exist');
        }
    }

    public function addUser() {
        $sql = 'INSERT INTO users (name, email, password, type) 
                VALUES (?,?,?,?)';
                
        $hashed_pwd = password_hash($_POST['pwd'], PASSWORD_DEFAULT);
        $sth = $this->db->prepare($sql);
        $sth->execute([$_POST['name'], $_POST['email'], $hashed_pwd, $_POST['user-type']]);

        if ($sth->rowCount() == 1) {
            $tmp['status'] = 'Created user: ' . $_POST['email'];
            $tmp['id'] = $this->db->lastInsertId();
        } else {
            $tmp['status'] = 'Failed';
            $tmp['error'] = 'User already exists';
        }

        if ($this->db->errorInfo()[1]!=0) { // Error in SQL??????
            $tmp['errorMessage'] = $this->db->errorInfo()[2];
        }

        return $tmp;
    }

    public function checkUserType($uid) {
        $uid = htmlspecialchars($uid);

        $sql = 'SELECT type
                FROM users
                WHERE id = ?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($uid));

        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            return array('error' => 'error');
        }
    }

    public function validateUserSignup($name, $email, $pwd, $pwd_repeat) {
        if (isset($_POST['signup-submit'])) {
            if (empty($name) || empty($email) || empty($pwd) || empty($pwd_repeat)) {
                $tmp['status'] = 'Failed';
                $tmp['error'] = 'Empty fields';
            } else {
                if ($pwd != $pwd_repeat) {
                    $tmp['status'] = 'Failed';
                    $tmp['error'] = 'Passwords does not match';
                } else {
                    $tmp['status'] = 'Success';
                }
            }
        }
        
        return $tmp;
    }
}