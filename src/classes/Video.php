<?php
/**
 * Display, Add, edit, delete videos.
 */
class Video {
    private $db;
    private $valid_extensions = array('mp4','avi','3gp','mov','mpeg', 'webm');


    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Returns the videos of all users or a single user.
     * @param data {array} assoc array with either single 
     * or all users, and the limit of how many rows to show.
     * @return {array} Returns an assoc array of either rows return from db
     * or error msg.
     */
    public function getVideos($data) {
        if ($data['user'] == 'all') {
            $sql = 'SELECT videos.*, users.name, users.email, users.id AS uid
                FROM videos
                LEFT JOIN users ON videos.uploaded_by = users.id
                ORDER BY videos.upload_time DESC LIMIT ' . $data['limit'];
        } else {
            $sql = 'SELECT videos.*, users.name, users.email
                FROM users
                RIGHT JOIN videos ON users.id = videos.uploaded_by
                WHERE users.id = ?
                ORDER BY videos.upload_time DESC LIMIT ' . $data['limit'];
        }

        $sth = $this->db->prepare($sql);
        $sth->execute(array($data['user']));

        if ($row = $sth->fetchAll(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            $tmp['status'] = 'Error';
            $tmp['error'] = 'No videos available';
        }
        return $tmp;
    }

    /**
     * Method for getting info on single video
     */
    public function getVideoInfo($video) {
        $id = htmlspecialchars($video);

        $sql = 'SELECT videos.*, users.name
            FROM videos
            LEFT JOIN users ON videos.uploaded_by = users.id
            WHERE videos.id = ?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($id));

        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            // Something bad TODO:
        }
    }

    /**
     * AddVideo
     * @return tmp {array} status or error msg
     */
    public function uploadVideo() {
        $name = $_FILES['file-video']['name'];
        $mime = $_FILES['file-video']['type'];

        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/src/uploads/';
        $target_file = $target_dir . $name; // upload location
        $location = 'src/uploads/' . $name; // name of location in db

        // Check extension
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($file_type, $this->valid_extensions)) {

            if (is_uploaded_file($_FILES['file-video']['tmp_name'])) {

                if (move_uploaded_file($_FILES['file-video']['tmp_name'], $target_file)) {
                    $title = htmlspecialchars($_POST['title']);
                    $description = htmlspecialchars($_POST['description']);
                    $subject = htmlspecialchars($_POST['subject']);

                    $sql = 'INSERT INTO videos (title, location, mime, description, subject, uploaded_by)
                        VALUES (:title, :location, :mime, :description, :subject, :uploaded_by)';

                    $sth = $this->db->prepare($sql);
                    $sth->bindParam(':title', $title);
                    $sth->bindParam(':location', $location); //TODO: change when adding folders for each user
                    $sth->bindParam(':mime', $mime);
                    $sth->bindParam(':description', $description);
                    $sth->bindParam(':subject', $subject);
                    $sth->bindParam(':uploaded_by', $_SESSION['uid']);
                    $sth->execute();
                    
                    if ($sth->rowCount() == 1) {
                        $tmp['status'] = 'Success';
                        $tmp['id'] = $this->db->lastInsertId();
                        $tmp['thumbnail'] = $this->addThumbnail($tmp['id']);
                    } else {
                        $tmp['status'] = 'Error';
                        $tmp['error'] = 'Video was not added to db';
                    }

                    if ($this->db->errorInfo()[1]!=0) {
                        $tmp['errorMessage'] = $this->db->errorInfo()[2];
                    }

                    return $tmp;

                } else {
                    return array('error' => 'not moved');
                }
            } else {
                
                return array('error' => 'not uploaded');
            }
            
        } else {
            return array('error' => 'Invalid file extension');
        }
    }

    public function addThumbnail($id) {
        $ext_check = array('jpg', 'jpeg', 'png', 'gif'); // Array of accepted file types
        $name = $_FILES['file-thumbnail']['name'];

        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/src/uploads/'; // Upload location on server
        $target_file = $target_dir . $name; // name of file
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // file type
        $location = 'src/uploads/' . $id . '.' . $file_type; // location string stored in db

        // Extension check
        if (in_array($file_type, $ext_check)) {
            // Upload check
            if (is_uploaded_file($_FILES['file-thumbnail']['tmp_name'])) {
                if (move_uploaded_file($_FILES['file-thumbnail']['tmp_name'], $target_dir . $id . '.' . $file_type)) { 
                    $sql = 'UPDATE videos
                        SET thumbnail = :thumbnail
                        WHERE id  = :id';

                    $sth = $this->db->prepare($sql);
                    $sth->bindParam(':thumbnail', $location);
                    $sth->bindParam(':id', $id);
                    $sth->execute();

                    if ($sth->rowCount() == 1) {
                        return 'success';
                    } else {
                        return 'error';
                    }
                }
            }
        }


        // $image = imagecreatetruecolor(200, 300);
        // imagefill($image);

        // if (!empty($_POST['thumbnail'])) {
            // Add thumbnail to db.
        // } else {
            // Make thumbnail from video and add to db.
            
        // }

        // $sec = 5;
        // $movie = $e;
        // $thumbnail = 'thumbnail.png';

        // $ffmpeg = FFMpeg\FFMpeg::create();
        // $video = $ffmpeg->open($movie);
        // $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($sec));
        // $frame->save($thumbnail);

        // $tmp['thumbnail'] = $thumbnail;
        // echo '<img src="'.$thumbnail.'">';
    }

    public function getComments($vid) {
        $id = htmlspecialchars($vid);

        $sql = 'SELECT comments.*, users.name AS uname
                From comments
                LEFT JOIN users ON comments.user_id = users.id
                WHERE video_id = ?
                ORDER BY comments.time DESC';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($id));

        if ($row = $sth->fetchAll(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            return array('error' => 'Error');
        }
    }

    public function addComment($vid) {
        if (!empty($_POST['comment'])) {
            $id = htmlspecialchars($vid);

            $sql = 'INSERT INTO comments (user_id, video_id, comment)
                    VALUES (:uid, :vid, :comment)';

            $sth = $this->db->prepare($sql);
            $sth->bindParam(':uid', $_SESSION['uid']);
            $sth->bindParam(':vid', $id);
            $sth->bindParam(':comment', $_POST['comment']);
            $sth->execute();

            if ($sth->rowCount() == 1) {
                $tmp['status'] = 'Success';
                $tmp['id'] = $this->db->lastInsertId();
            } else {
                $tmp['status'] = 'Error';
                $tmp['error'] = 'Could not add comment';
            }
        } else {
            $tmp['error'] = 'Emtpy field';
        }

        return $tmp;
    }
}