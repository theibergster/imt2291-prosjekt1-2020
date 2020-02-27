<?php
/**
 * Class for handling search
 */
class Search {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function videoSearch($query) {
        $query = htmlspecialchars("%{$query}%");

        $sql = 'SELECT videos.*, users.name, users.email, users.id AS uid, FORMAT(AVG(rating.rating), 1) as avg_rating
                FROM videos
                LEFT JOIN rating ON videos.id = rating.video_id
                INNER JOIN users ON videos.uploaded_by = users.id
                WHERE (videos.title LIKE :query) 
                OR (videos.description LIKE :query)
                OR (users.name LIKE :query)
                GROUP BY videos.id
                ORDER BY videos.upload_time DESC';
        
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':query', $query);
        $sth->execute();

        if ($row = $sth->fetchAll(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            $tmp['status'] = 'Error';
            $tmp['error'] = 'No results for \'' . trim($query, '%') . '\'';
        }
        
        return $tmp;
    }

    public function playlistSearch($query) {
        $query = htmlspecialchars("%{$query}%");

        $sql = 'SELECT playlists.*, users.name AS uname, COUNT(playlist_videos.video_id) AS tot_videos
                FROM users
                LEFT JOIN playlists ON users.id = playlists.created_by
                RIGHT JOIN playlist_videos ON playlists.id = playlist_videos.playlist_id
                WHERE (playlists.title LIKE :query) 
                OR (playlists.description LIKE :query)
                OR (users.name LIKE :query)
                GROUP BY playlists.id
                ORDER BY playlists.time_created DESC';

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':query', $query);
        $sth->execute();

        if ($row = $sth->fetchAll(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            $tmp['status'] = 'Error';
            $tmp['error'] = 'No results for \'' . trim($query, '%') . '\'';
        }
        
        return $tmp;
    }
}