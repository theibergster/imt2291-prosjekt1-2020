<?php
/**
 * Class Rating for handling rating and likes of videos.
 * getTotalRating for a single video, getUserRating for the logged in
 */
class Rate {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTotalRating($video) {
        $video_id = htmlspecialchars($video);

        $sql = 'SELECT AVG(rating) AS avg_rating
            FROM rating
            WHERE video_id = ?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($video_id));


        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            return array('status' => 'failed');
        }
    }

    public function getUserRating($video) {
        $video_id = htmlspecialchars($video);

        $sql = 'SELECT rating AS user_rating
            FROM rating
            WHERE video_id = ?
            AND user_id = ?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($video_id, $_SESSION['uid']));

        if ($row = $sth->fetch(PDO::FETCtmpH_ASSOC)) {
            $tmp['user_rating'] = $row['user_rating'];
            $tmp['is_rated'] = true;

        } else {
            $tmp['user_rating'] = 'Not rated';
        }

        return $tmp;
    }

    public function rateVideo($video) {
        $video_id = htmlspecialchars($video);
        $rating = htmlspecialchars($_POST['rating-value']);

        if ($this->getUserRating()['is_rated']) {
            $sql = 'UPDATE rating
                SET rating = ?
                WHERE video_id = ? 
                AND user_id = ?';

        } else {
            $sql = 'INSERT INTO rating (video_id, user_id, rating)
                VALUES (?,?,?)';
        }

        $sth = $this->db->prepare($sql);
        $sth->execute(array($video_id, $SESSION['uid'], $rating));

        if ($sth->rowCount() == 1) {
            return array('status' => 'Success');
        } else {
            return array('status' => 'Failed');
        }
    }
    
    public function likeVideo($video) {

    }

    public function dislikeVideo($video) {

    }

    public function getTotalLikes($video) {
        $video_id = htmlspecialchars($video);

        // SUM(CAST(liked AS INT)) AS tot_likes
        $sql = 'SELECT COUNT(*) AS tot_likes
            FROM rating
            WHERE video_id = ?
            AND liked = 1';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($video_id));


        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            return array('status' => 'failed');
        }
    }

}