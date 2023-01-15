<?php

class Genre extends Model implements \JsonSerializable
{
    use JsonSerializer;
    protected $idGenre;
    protected $genreName;
    protected $namehtml;

    const DBNAME = 'genre';

    public function __construct($idGenre, $genreName)
    {
        $this->idGenre = $idGenre;
        $this->genreName = $genreName;
        $this->namehtml = $this->GenreNameLink();

    }

    public function __toString()
    {
        return $this->genreName;
    }

    public static function inDB($name)
    {
        $sql = "select \"name\" from genre where \"genreName\"='" . $name . "'";
        global $db;
        $check = $db->prepare($sql);
        $check->execute();
        if ($check->fetch(PDO::FETCH_ASSOC) == false) {
            return false;
        } else {
            return true;
        }
    }

    public function GenreNameLink()
    {
        return "<a class='link' href=\".?page=view-g-" . $this->idGenre . "\">" . $this->genreName . "</a>";
    }

    public static function getFilmGenres($idfilm){
        global $db;
        $query = "select genre.\"idGenre\", \"genreName\" from genre join \"filmGenre\" on \"filmGenre\".\"idGenre\" = genre.\"idGenre\" where \"filmGenre\".\"idFilm\" = '$idfilm'";
        $gen = $db->prepare($query);
        $gen->execute();
        $genres = array();
        while ($row = $gen->fetch(PDO::FETCH_ASSOC)) {
            $genre = new Genre($row["idGenre"],$row["genreName"]);
            $genres[] = $genre;
        }
        return $genres;
    }

    public static function all()
    {
        global $db;
        $st = $db->prepare("select * from genre");
        $st->execute();

        $genres = array();

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $genre = new Genre($row["idGenre"],$row["genreName"]);
            $genres[] = $genre;
        }
        return $genres;
    }
}
