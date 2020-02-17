<?php
/**
 * Add, edit, delete videos.
 */
class Video {
    private $db;
    private $valid_extentions = array('mp4','avi','3gp','mov','mpeg', 'webm', 'jpg');


    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * get all vidoes from all users or a single user.
     * @param user {string} 
     * @param limit {string} number of rows to show.
     * @return tmp {array}
     */
    public function getVideos($data) {
        if ($data['user'] == 'all') {
            $sql = 'SELECT * FROM videos ORDER BY upload_time DESC LIMIT ' . $data['limit'];
        } else {
            $sql = 'SELECT * FROM videos 
                WHERE uploaded_by = ? 
                ORDER BY upload_time DESC LIMIT ' . $data['limit'];
        }

        $sth = $this->db->prepare($sql);
        $sth->execute(array($data['user']));

        if ($row = $sth->fetchAll(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            $tmp['status'] = 'Failed';
            $tmp['error'] = 'No videos found';
        }
        return $tmp;
    }

    /**
     * AddVideo
     * @return tmp {array} status or error msg
     */
    public function uploadVideo() {
        // TODO: ADD mime and size to video table
        $name = $_FILES['file-upload']['name'];
        $mime = $_FILES['file-upload']['type'];
        $size = $_FILES['file-upload']['size'];

        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/videos/';
        $target_file = $target_dir . $name;

        // For testing
        // return array('asd' => $_FILES['file-upload']['tmp_name']);
        // return array('asd' => getcwd());

        // Check extension
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($file_type, $this->valid_extentions)) {

            // if (is_uploaded_file($_FILES['file-upload']['name'])) {
                if (move_uploaded_file($_FILES['file-upload']['tmp_name'], $target_file)) {
                    $sql = 'INSERT INTO videos (title, location, description, subject, uploaded_by, thumbnail)
                        VALUES (:title, :location, :description, :subject, :uploaded_by, :thumbnail)';

                    $query['title'] = htmlspecialchars($_POST['title']);
                    $query['description'] = htmlspecialchars($_POST['description']);
                    $query['subject'] = htmlspecialchars($_POST['subject']);
                    $query['thumbnail'] = htmlspecialchars($_POST['thumbnail']);

                    $sth = $this->db->prepare($sqtitlel);
                    $sth->bindParam(':title', $query['title']);
                    $sth->bindParam(':location', $target_file);
                    $sth->bindParam(':description', $query['description']);
                    $sth->bindParam(':subject', $query['subject']);
                    $sth->bindParam(':uploaded_by', $_SESSION['uid']);
                    $sth->bindParam(':thumbnail', $query['thumbnail']);
                    $sth->execute();
                    
                    if ($sth->rowCount() == 1) {
                        $tmp['status'] = 'Smud';
                        $tmp['id'] = $this->db->lastInsertId();
                    } else {
                        $tmp['status'] = 'Usmud';
                        $tmp['error'] = 'Video was not added to db';
                    }

                    if ($this->db->errorInfo()[1]!=0) { // Error in SQL??????
                        $tmp['errorMessage'] = $this->db->errorInfo()[2];
                    }

                    return $tmp;

                } else {
                    return array('error' => 'not moved');
                }

            // } else {
            //     return array('error' => 'not uploaded');
            // }
            
        } else {
            return array('error' => 'Invalid file extension');
        }
    }

    public function videoSearch($data) {
        if (isset($_POST['search-submit'])) {
        // Checks if the search field is not empty
            if (!empty($_POST['search-query'])) {
                header('Location: ../index.php?search='.$_POST['search-query']);

                $query = htmlspecialchars("%{$_POST['search-query']}%");

                $sql = 'SELECT videos.*, users.email, users.name
                    FROM users 
                    INNER JOIN videos 
                    ON users.id = videos.id 
                    WHERE (video.title LIKE ?) 
                    OR (video.description LIKE ?)
                    OR (users.name LIKE ?)
                    ORDER BY videos.upload_time DESC';
                
                $sth = $this->db->prepare($sql);
                $sth->execute(array($query, $query, $query));

                if ($sth->rowCount >= 1) {
                    return $sth->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    return array('status' => 'No results');
                }                
                
            } 
        }
    }
}