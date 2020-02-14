<?php
class User {
    protected $uid = -1;
    protected $db;

    public function __construct($db) {
        $this->db = $db;

        if (isset($_POST['login-submit'])) {
            if (!empty($_POST['email']) || !empty($_POST['pwd'])) {
                $this->login($_POST['email'], $_POST['pwd']);
            }
        } else if (isset($_POST['logout-submit'])) {
            $this->uid = -1;
            $_SESSION = array();
            session_destroy();
            header('Location: index.php');
        } else if (isset($_SESSION['uid'])) {
            $this->uid = $_SESSION['uid'];
        }
    }

    public function loggedIn() {
        return $this->uid > -1;
    }

    public function login($email, $pwd) {
        $sql = 'SELECT * FROM users WHERE email=?';
        $sth = $this->db->prepare($sql);
        $sth->execute(array($email));

        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($pwd, $row['password'])) {
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
        $sql = 'INSERT INTO users (name, email, password, type) VALUES (?,?,?,?)';
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