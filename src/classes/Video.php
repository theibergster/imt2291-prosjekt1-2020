<?php
/**
 * Display, Add, edit, delete videos.
 */
class Video {
    private $db;
    private $valid_extentions = array('mp4','avi','3gp','mov','mpeg', 'webm');


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

        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/src/uploads/';
        $target_file = $target_dir . $name;
        $location = 'src/uploads/' . $name;

        // Check extension
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($file_type, $this->valid_extentions)) {

            if (is_uploaded_file($_FILES['file-upload']['tmp_name'])) {

                if (move_uploaded_file($_FILES['file-upload']['tmp_name'], $target_file)) {
                    $sql = 'INSERT INTO videos (title, location, description, subject, uploaded_by, thumbnail)
                        VALUES (:title, :location, :description, :subject, :uploaded_by, :thumbnail)';

                    $title = htmlspecialchars($_POST['title']);
                    $description = htmlspecialchars($_POST['description']);
                    $subject = htmlspecialchars($_POST['subject']);
                    $thumnail = htmlspecialchars($_POST['thumbnail']);

                    $sth = $this->db->prepare($sql);
                    $sth->bindParam(':title', $title);
                    $sth->bindParam(':location', $location); //TODO: change when adding folders for each user
                    $sth->bindParam(':description', $description);
                    $sth->bindParam(':subject', $subject);
                    $sth->bindParam(':uploaded_by', $_SESSION['uid']);
                    $sth->bindParam(':thumbnail', $thumnail);
                    $sth->execute();
                    
                    if ($sth->rowCount() == 1) {
                        $tmp['status'] = 'Success';
                        $tmp['id'] = $this->db->lastInsertId(); // remove later i guess
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

    public function addThumbnail($e) {
        // $image = imagecreatetruecolor(200, 300);
        // imagefill($image);

        if (!empty($_POST['thumbnail'])) {
            // Add thumbnail to db.
        } else {
            // Make thumbnail from video and add to db.
            
        }

        $sec = 5;
        $movie = $e;
        $thumbnail = 'thumbnail.png';

        $ffmpeg = FFMpeg\FFMpeg::create();
        $video = $ffmpeg->open($movie);
        $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($sec));
        $frame->save($thumbnail);

        $tmp['thumbnail'] = $thumbnail;
        // echo '<img src="'.$thumbnail.'">';
    }

    public function videoSearch($data) {
        if (isset($_POST['search-submit'])) {
        // Checks if the search field is not empty
            if (!empty($_POST['search-query'])) {
                header('Location: ../index.php?search='.$_POST['search-query']);

                $query = htmlspecialchars("%{$_POST['search-query']}%");

                $sql = 'SELECT videos.*, users.email, users.name
                    FROM users 
                    INNER JOIN videos ON users.id = videos.id 
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