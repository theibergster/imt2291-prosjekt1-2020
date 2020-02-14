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
    public function getVideos($user, $limit) {
        if ($users == 'all') {
            $sql = 'SELECT * FROM videos ORDER BY upload_time DESC LIMIT ' . $limit;
        } else {
            $sql = 'SELECT * FROM videos WHERE uploaded_by=? ORDER BY upload_time DESC LIMIT ' . $limit;
        }

        $sth = $this->db->prepare($sql);
        $sth->execute(array($user));

        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $tmp['title'] = $row['title'];
            $tmp['location'] = $row['location'];
            $tmp['description'] = $row['description'];
            $tmp['subject'] = $row['subject'];
            $tmp['rating'] = $row['rating'];
            $tmp['upload_time'] = $row['upload_time'];
            $tmp['thumbnail'] = $row['thumbnail'];
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
                    // Insert record in db
                    $sql = 'INSERT INTO videos (title, location, description, subject, uploaded_by, thumbnail)
                        VALUES (:title, :location, :description, :subject, :uploaded_by, :thumbnail)';

                    $sth = $this->db->prepare($sql);
                    $sth->bindParam(':title', $_POST['title']);
                    $sth->bindParam(':location', $target_file);
                    $sth->bindParam(':description', $_POST['description']);
                    $sth->bindParam(':subject', $_POST['subject']);
                    $sth->bindParam(':uploaded_by', $_SESSION['uid']);
                    $sth->bindParam(':thumbnail', $_POST['thumbnail']);
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

}