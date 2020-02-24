<?php
/**
 * Class Rating for handling rating and likes of videos.
 * getTotalRating for a single video, getUserRating for the logged in
 */
class Rating {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTotalRating($vid) {
        $video_id = htmlspecialchars($vid);

        $sql = 'SELECT AVG(rating) AS avg_rating, COUNT(rating) AS tot_users_rate
                FROM rating
                WHERE video_id = ?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($video_id));


        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $tmp['avg_rating'] = number_format($row['avg_rating'], 1);
            $tmp['tot_users_rate'] = $row['tot_users_rate'];

        } else {
            $tmp['error'] = 'failed to fetch rating';
        }
        return $tmp;
    }

    public function getUserRating($vid) {
        $video_id = htmlspecialchars($vid);

        $sql = 'SELECT rating AS user_rating, liked
                FROM rating
                WHERE video_id = :vid
                AND user_id = :uid';

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':vid', $video_id);
        $sth->bindParam(':uid', $_SESSION['uid']);
        $sth->execute();

        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $tmp['user_rating'] = $row['user_rating'];
            $tmp['liked'] = $row['liked'];
            $tmp['is_rated'] = $sth->rowCount() > 0;

        } else {
            $tmp['user_rating'] = 'Not rated';
        }

        return $tmp;
    }

    public function rateVideo($vid) {
        $video_id = htmlspecialchars($vid);
        $rating = htmlspecialchars($_POST['rating-value']);

        if ($this->getUserRating($video_id)['is_rated']) {
            $sql = 'UPDATE rating
                    SET rating = :rating
                    WHERE video_id = :vid 
                    AND user_id = :uid';

        } else {
            $sql = 'INSERT INTO rating (video_id, user_id, rating)
                    VALUES (:vid, :uid, :rating)';
        }

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':rating', $rating);
        $sth->bindParam(':vid', $video_id);
        $sth->bindParam(':uid', $_SESSION['uid']);
        $sth->execute();

        if ($sth->rowCount() == 1) {
            return array('status' => 'Success');
        } else {
            return array('status' => 'Failed');
        }
    }
    
    public function likeVideo($vid) {
        $video_id = htmlspecialchars($vid);

        if ($this->getUserRating($video_id)['is_rated']) {
            $sql = 'UPDATE rating
                    SET liked = 1
                    WHERE video_id = :vid 
                    AND user_id = :uid';

        } else {
            $sql = 'INSERT INTO rating (video_id, user_id, liked)
                    VALUES (:vid, :uid, 1)';
        }

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':vid', $video_id);
        $sth->bindParam(':uid', $_SESSION['uid']);
        $sth->execute();

        if ($sth->rowCount() == 1) {
            return array('status' => 'Success');
        } else {
            return array('status' => 'Failed');
        }
    }

    public function checkLike($vid) {
        $video_id = htmlspecialchars($vid);

        $sql = 'SELECT liked
                FROM rating
                WHERE video_id = :vid 
                AND user_id = :uid';

        $sth = $this->db->prepare($sql);
        $sth->bindParam(':vid', $video_id);
        $sth->bindParam(':uid', $_SESSION['uid']);
        $sth->execute();

        $row = $sth->fetch(PDO::FETCH_ASSOC);

        if ($row['liked'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function dislikeVideo($vid) {
        $video_id = htmlspecialchars($vid);

        $sql = 'UPDATE rating
                SET liked = 0
                WHERE video_id = :vid 
                AND user_id = :uid';
        
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':vid', $video_id);
        $sth->bindParam(':uid', $_SESSION['uid']);
        $sth->execute();

        if ($sth->rowCount() == 1) {
            return array('status' => 'Success');
        } else {
            return array('status' => 'Failed');
        }
    }

    public function getTotalLikes($vid) {
        $video_id = htmlspecialchars($vid);

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