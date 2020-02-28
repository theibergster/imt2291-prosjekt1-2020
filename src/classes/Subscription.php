<?php
/**
 * Subscription class for handling subscriptions
 */
class Subscription {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getSubscriptions($data) {
        $sql = 'SELECT playlists.*, COUNT(playlist_videos.video_id) AS tot_videos, users.id AS uid, users.name AS uname, subscriptions.playlist_id AS sub_pid, subscriptions.user_id AS sub_uid
                FROM playlists
                LEFT JOIN playlist_videos ON playlists.id = playlist_videos.playlist_id
                INNER JOIN users ON playlists.created_by = users.id
                LEFT JOIN subscriptions ON playlists.id = subscriptions.playlist_id
                WHERE subscriptions.user_id = ?
                GROUP BY playlists.id
                ORDER BY playlists.time_created DESC LIMIT ' . $data['limit'];

        $sth = $this->db->prepare($sql);
        $sth->execute(array($data['user']));

        if ($row = $sth->fetchAll(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            $tmp['status'] = 'Error';
            $tmp['error'] = 'You have no subscriptions..';
        }
        return $tmp;
    }

    public function subscribe($data) {        
        $sql = 'INSERT INTO subscriptions (playlist_id, user_id)
                VALUES (:pid, :uid)';
        
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':pid', $data['pid']);
        $sth->bindParam(':uid', $data['uid']);
        $sth->execute();

        if ($sth->rowCount() == 1) {
            $tmp['status'] = 'Success';
        } else {
            $tmp['status'] = 'Error';
        }

        return $tmp;
    }

    public function unsubscribe($data) {
        $sql = 'DELETE FROM subscriptions
                WHERE user_id = :uid
                AND playlist_id = :pid';

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':pid', $data['pid']);
        $sth->bindParam(':uid', $data['uid']);
        $sth->execute();

        if ($sth->rowCount() == 1) {
            $tmp['status'] = 'Success';
        } else {
            $tmp['status'] = 'Error';
        }

        return $tmp;
    }
    /**
     * @param {string} — playlist id.
     */
    public function subCheck($pid) {
        $sql = 'SELECT playlist_id, user_id
                FROM subscriptions
                WHERE playlist_id = :pid 
                AND user_id = :uid';

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':pid', $pid);
        $sth->bindParam(':uid', $_SESSION['uid']);
        $sth->execute();

        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if ($row['playlist_id'] > 0) {
            return true;
        } else {
            return false;
        }
    }

}