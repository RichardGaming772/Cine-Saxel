<?php

class Director extends Model implements \JsonSerializable
{
    use JsonSerializer;
    protected $idDirector;
    protected $name;
    protected $namehtml;

    const DBNAME = 'directors';

    public function __construct($idDirector, $name)
    {
        $this->idDirector = $idDirector;
        $this->name = $name;
        $this->namehtml = $this->DirectorNameLink();
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function inDB($name)
    {
        $sql = "select \"name\" from directors where name='" . $name . "'";
        global $db;
        $check = $db->prepare($sql);
        $check->execute();
        if ($check->fetch(PDO::FETCH_ASSOC) == false) {
            return false;
        } else {
            return true;
        }
    }

    public function DirectorNameLink()
    {
        return "<a class='link' href=\".?page=view-d-" . $this->idDirector . "\">" . $this->name . "</a>";
    }

    public static function getFilmDirectors($idfilm)
    {
        global $db;
        $query = "select directors.\"idDirector\", name from directors join \"filmDirectors\" on \"filmDirectors\".\"idDirector\" = directors.\"idDirector\" where \"filmDirectors\".\"idFilm\" = '$idfilm'";
        $act = $db->prepare($query);
        $act->execute();
        $directors = array();
        while ($row = $act->fetch(PDO::FETCH_ASSOC)) {
            $director = new Director($row["idDirector"], $row["name"]);
            $directors[] = $director;
        }
        return $directors;
    }
}
