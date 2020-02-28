<?php
/**
 * Class for handling the rating and likes of all videos.
 */
class Rating {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Function that returns how many users that have rated a video, and the average rating of the video.
     * @param string vid — video id.
     * @return array tmp — assoc array with rows from db, or status message if unsuccessful.
     */
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

    /**
     * Function for getting the current user's rating of a single video.
     * @param string vid — video id.
     * @return array tmp — assoc array with rows from db, or error message if no rows are returned.
     */
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
            $tmp = $row;
            $tmp['is_rated'] = $sth->rowCount() > 0;
            if ($tmp['user_rating'] == null) {
                $tmp['user_rating'] = 'Not rated';
            }

        } else {
            $tmp['user_rating'] = 'Not rated';
        }

        return $tmp;
    }

    /**
     * Function for setting the current user's rating value on a single video.
     * @param array data — video id, and rating value.
     * @return array — assoc array with status.
     */
    public function rateVideo($data) {
        $video_id = htmlspecialchars($data['vid']);
        $rating = htmlspecialchars($data['rating-value']);

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
    
    /**
     * Function for setting current user's like-value to 1(true) on video.
     * Uses getUserRating() method to check if there is already an existing row.
     * The result decides which query to use.
     * @param string vid — video id.
     * @return array — assoc array with status.
     */
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

    /**
     * Function for checking if current user has liked a video.
     * @param string vid — video id.
     * @return bool — true or false.
     */
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

    /**
     * Function for setting current user's like-value to 0(false) on a video.
     * @param string vid — video id.
     * @return array — assoc array with status.
     */
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

    /**
     * Function returning a videos total number of likes.
     * @param string vid — video id.
     * @return array row — rows from db, or error message if no rows are returned.
     */
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