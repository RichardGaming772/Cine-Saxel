<?php
class User extends Model implements \JsonSerializable
{
    use JsonSerializer;
    protected $username;
    protected $nbSeen;
    protected $nbAdded;
    protected $avgScore;
    protected $filmScore = null;

    const DBNAME = 'user';

    public function __construct($username)
    {
        $this->username = $username;

        global $db;

        $query = 'select count("idFilm") from "userSeen" where username=\'' . $this->username . '\' and seen=TRUE';
        $seencount = $db->prepare($query);
        $seencount->execute();
        $this->nbSeen = $seencount->fetch(PDO::FETCH_ASSOC)["count"];

        $query = 'select round(avg("score"),2) as avg from "userScore" where username=\'' . $this->username . '\'';
        $avgscore = $db->prepare($query);
        $avgscore->execute();
        $this->avgScore = $avgscore->fetch(PDO::FETCH_ASSOC)["avg"];

        $query = 'select count("idFilm") from filmadded where username=\'' . $this->username . '\'';
        $added = $db->prepare($query);
        $added->execute();
        $this->nbAdded = $added->fetch(PDO::FETCH_ASSOC)["count"];
    }

    public function __toString()
    {
        return $this->username;
    }

    public function StatBlock()
    {
        echo "<div class='userStats'><h4>" . $this->username . "</h4>";
        echo "<p>Film(s) vu(s): $this->nbSeen</p>";
        echo "<p>Film(s) ajouté(s): $this->nbAdded</p>";
        echo "<p>Score Moyen: " . (!empty($this->avgScore) ? $this->avgScore : "-") . "</p>";
        echo "</div>";
    }

    public static function all()
    {
        global $db;
        $st = $db->prepare("select * from film.user");
        $st->execute();

        $users = array();

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($row["username"]);
            $users[] = $user;
        }

        return $users;
    }

    public static function SetUserScore($idfilm,$user, $score)
    {
        global $db;
        $st = $db->prepare("INSERT INTO \"userScore\" (\"idFilm\",username,score) VALUES(:id, :username, :score)
        ON CONFLICT (\"idFilm\",username) DO UPDATE set score = :score");
        $st->bindParam(':id', $idfilm, PDO::PARAM_STR);
        $st->bindParam(':username', $user, PDO::PARAM_STR);
        $st->bindParam(':score', $score, PDO::PARAM_INT);
        $st->execute();
    }

    public static function SetUserSeen($idfilm,$user,$seen)
    {
        global $db;
        $st = $db->prepare("INSERT INTO \"userSeen\" (\"idFilm\",username,seen) VALUES(:id, :username, :seen)
        ON CONFLICT (\"idFilm\",username) DO UPDATE set seen = :seen");
        $st->bindParam(':id', $idfilm, PDO::PARAM_STR);
        $st->bindParam(':username', $user, PDO::PARAM_STR);
        $st->bindParam(':seen', $seen, PDO::PARAM_BOOL);
        $st->execute();
    }

    public static function getFilmUsers($idfilm)
    {
        global $db;
        $query = "select username from \"userSeen\" where \"idFilm\" = '$idfilm' and seen=TRUE";
        $see = $db->prepare($query);
        $see->execute();
        $users = array();
        while ($row = $see->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($row["username"]);
            $user->filmScore = $user->getFilmUserScore($idfilm);
            $users[] = $user;
        }
        return $users;
    }

    public static function AllUsernames()
    {
        global $db;
        $query = "select username from film.user";
        $see = $db->prepare($query);
        $see->execute();
        $users = array();
        while ($row = $see->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $row["username"];
        }
        return $users;
    }

    public static function CreateUser($username)
    {
        global $db;
        $username = strip_tags(preg_replace("#\'#", "''", $username));
        $st = $db->prepare("INSERT INTO film.user (username) VALUES(:username)");
        $st->bindParam(':username', $username, PDO::PARAM_STR);
        $st->execute();
    }

    public function getFilmUserScore($idfilm)
    {
        global $db;
        $query = "select score from \"userScore\" where \"idFilm\" = '$idfilm' and username='" . $this->username . "'";
        $score = $db->prepare($query);
        $score->execute();
        $score = $score->fetch(PDO::FETCH_ASSOC);
        if (gettype($score) == "array") {
            return $score["score"];
        } else {
            return null;
        }
    }
}
