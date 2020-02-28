<?php
/**
 * Playlist class for handling everything related to playlists.
 */
class Playlist {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    /**
     * Function for returning all playlist data from db.
     * @param {array} — assoc array with user being either 'all' or uid of current user.
     * @return {array} — assoc array with rows from db, or an error message if no rows are returned.
     */
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
     * Returns info on one single playlist.
     * @param {string} — the playlist id.
     * @return {array} — assoc array with rows from db, or an error message.
     */
    public function getPlaylistInfo($pid) {
        $id = htmlspecialchars($pid);

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
            return array('error' => 'error getting playlist info..');
        }
    }
    
    /**
     * Function for creating a new playlist.
     * @param {string} — title of new playlist.
     * @return {array} — assoc array with status message from db.
     */
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

    /**
     * Function for editing the description of a single playlist.
     * @param {array} — assoc array containing the playlist id, and the new description.
     * @return {array} — assoc array with status message from db.
     */
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

    /**
     * Function for deleting a playlist.
     * Deletes the rows connecting the videos and playlist tables from db.
     * @param {string} — id of playlist to delete.
     * @return {array} — assoc array with status message.
     */
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

    /**
     * Cleanup function for deleting a playlist. 
     * Deletes playlist row from playlist table from db.
     * @param {string} — id of playlist to delete.
     * @return {array} — assoc array with status message.
     */
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

    /**
     * Function for adding video to playlist.
     * @param {array} — assoc array containing the playlist id, and video id.
     * @return {array} — assoc array with status message.
     */
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

    /**
     * Function for removing a video from playlist.
     * @param {array} — assoc array containing the playlist id, and video id.
     * @return {array} — assoc array with status message.
     */
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

    /**
     * Function for returning all videos in a single playlist from db.
     * @param {string} — playlist id.
     * @return {array} — assoc array of all rows from db, or an error message if no rows are returned.
     */
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