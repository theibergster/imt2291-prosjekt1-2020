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
            $sql = 'SELECT playlists.*, COUNT(playlist_videos.video_id) AS tot_videos, users.id AS uid, users.name AS uname
                    FROM playlists
                    LEFT JOIN playlist_videos ON playlists.id = playlist_videos.playlist_id
                    INNER JOIN users ON playlists.created_by = users.id
                    GROUP BY playlists.id
                    ORDER BY playlists.time_created DESC LIMIT ' . $data['limit'];
        } else {
            $sql = 'SELECT playlists.*, COUNT(playlist_videos.video_id) AS tot_videos, users.id AS uid, users.name AS uname
                    FROM playlists
                    LEFT JOIN playlist_videos ON playlists.id = playlist_videos.playlist_id
                    INNER JOIN users ON playlists.created_by = users.id
                    WHERE users.id = ?
                    GROUP BY playlists.id
                    ORDER BY playlists.time_created DESC LIMIT ' . $data['limit'];
        }

        $sth = $this->db->prepare($sql);
        $sth->execute(array($data['user']));

        if ($row = $sth->fetchAll(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            $tmp['status'] = 'Error';
            $tmp['error'] = 'No playlists found..';
        }
        return $tmp;
    }

    /**
     * Method for getting info on single playlist
     */
    public function getPlaylistInfo($vid) {
        $id = htmlspecialchars($vid);

        $sql = 'SELECT playlists.*, users.id AS uid, users.name AS uname, subscriptions.playlist_id AS sub_pid, subscriptions.user_id AS sub_uid
                FROM playlists
                LEFT JOIN subscriptions ON playlists.id = subscriptions.playlist_id
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
    
    public function createPlaylist($title) {
        $title = htmlspecialchars($title);
        // $description = htmlspecialchars($_POST['description']);
        // $subject = htmlspecialchars($_POST['subject']); // TODO: add subject and thumbnail

        $sql = 'INSERT INTO playlists (title, created_by)
                VALUES (:title, :uid)';

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':title', $title);
        $sth->bindParam(':uid', $_SESSION['uid']);
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

    public function editPlaylistDescription($data) {
        $pid = htmlspecialchars($data['pid']);
        $desc = htmlspecialchars($data['desc']);

        $sql = 'UPDATE playlists
                SET description = :desc 
                WHERE playlists.id = :pid';

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':desc', $desc);
        $sth->bindParam(':pid', $pid);
        $sth->execute();
        
        if ($sth->rowCount() == 1) {
            $tmp['status'] = 'Success';
            $tmp['id'] = $this->db->lastInsertId();
        } else {
            $tmp['status'] = 'Error';
        }

        return $tmp;
    }

    public function deletePlaylist($pid) {
        $sql = 'DELETE FROM playlist_videos
                WHERE playlist_id = ?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($pid));

        if ($sth->rowCount() == 1) {
            $tmp['status'] = 'Success';
        } else {
            $tmp['status'] = 'Error';
        }
        
        $this->deletePlaylistCleanup($pid);

        return $tmp;
    }

    public function deletePlaylistCleanup($pid) {
        $sql = 'DELETE FROM playlists
                WHERE id = ?';
        
        $sth = $this->db->prepare($sql);
        $sth->execute(array($pid));

        if ($sth->rowCount() == 1) {
            $tmp['status'] = 'Success';
        } else {
            $tmp['status'] = 'Error';
        }

        return $tmp;
    }

    public function addToPlaylist($data) {
        $sql = 'INSERT INTO playlist_videos (playlist_id, video_id)
                VALUES (:pid, :vid)';
        
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':pid', $data['pid']);
        $sth->bindParam(':vid', $data['vid']);
        $sth->execute();

        if ($sth->rowCount() == 1) {
            $tmp['status'] = 'Success';
        } else {
            $tmp['status'] = 'Error';
        }

        return $tmp;
    }

    public function removeFromPlaylist($data) {
        $sql = 'DELETE FROM playlist_videos
                WHERE video_id = :vid
                AND playlist_id = :pid';

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':pid', $data['pid']);
        $sth->bindParam(':vid', $data['vid']);
        $sth->execute();

        if ($sth->rowCount() == 1) {
            $tmp['status'] = 'Success';
        } else {
            $tmp['status'] = 'Error';
        }

        return $tmp;
    }

    public function getVideosInPlaylist($pid) {
        $id = htmlspecialchars($pid);

        $sql = 'SELECT videos.*, users.name AS uname
                FROM playlist_videos
                INNER JOIN videos ON playlist_videos.video_id = videos.id
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