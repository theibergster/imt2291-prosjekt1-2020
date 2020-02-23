<?php
/**
 * Playlist class
 */
class Playlist {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getPlaylists($data) {
        if ($data['user'] == 'all') {
            $sql = 'SELECT playlists.*, users.name, users.email, users.id AS uid
                FROM playlists
                LEFT JOIN users ON playlists.created_by = users.id
                ORDER BY playlists.created_by DESC LIMIT ' . $data['limit'];
        } else {
            $sql = 'SELECT playlists.*, users.name, users.email
                FROM users
                RIGHT JOIN playlists ON users.id = playlists.created_by
                WHERE users.id = ?
                ORDER BY playlists.time_created DESC LIMIT ' . $data['limit'];
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
     * Method for getting info on single playlist
     */
    public function getPlaylistInfo($vid) {
        $id = htmlspecialchars($vid);

        $sql = 'SELECT playlists.*, users.name AS uname
            FROM playlists
            LEFT JOIN users ON playlists.created_by = users.id
            WHERE playlists.id = ?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($id));

        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            // Something bad TODO:
        }
    }
    
    public function createPlaylist() {
        $title = htmlspecialchars($_POST['title']);
        $description = htmlspecialchars($_POST['description']);
        $subject = htmlspecialchars($_POST['subject']); // TODO: add subject and thumbnail

        $sql = 'INSERT INTO playlists (title, description)
            VALUES (:title, :description)';

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':title', $title);
        $sth->bindParam(':description', $description);
        $sth->execute();

        if ($sth->rowCount() == 1) {
            $tmp['status'] = 'Success';
            $tmp['id'] = $this->db->lastInsertId();
        } else {
            $tmp['status'] = 'Error';
            $tmp['error'] = 'Could not create playlist';
        }

        return $tmp;
    }

    public function addToPlaylist($data) {
        $sql = 'INSERT INTO playlists_videos (playlist_id, video_id)
            VALUES (:playlist, :video)';
        

    }

    public function getVideosInPlaylist($pid) {
        $id = htmlspecialchars($pid);

        $sql = 'SELECT videos.*, users.name AS uname
            FROM playlist_videos
            LEFT JOIN videos ON playlist_videos.video_id = videos.id
            INNER JOIN users ON videos.uploaded_by = users.id
            WHERE playlist_videos.playlist_id = ?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($id));
        
        if ($row = $sth->fetchAll(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            $tmp['status'] = 'Error';
            $tmp['error'] = 'No videos found';
        }
        return $tmp;
    }
    
}