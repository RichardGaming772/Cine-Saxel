<?php

class Actor extends Model implements \JsonSerializable
{
    use JsonSerializer;
    protected $idActor;
    protected $name;
    protected $namehtml;

    const DBNAME = 'actors';

    public function __construct($idActor, $name)
    {
        $this->idActor = $idActor;
        $this->name = $name;
        $this->namehtml = $this->ActorNameLink();
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function inDB($name)
    {
        $sql = "select \"name\" from actors where name='" . $name . "'";
        global $db;
        $check = $db->prepare($sql);
        $check->execute();
        if ($check->fetch(PDO::FETCH_ASSOC) == false) {
            return false;
        } else {
            return true;
        }
    }

    public function ActorNameLink()
    {
        return "<a class='link' href=\".?page=view-a-" . $this->idActor . "\">" . $this->name . "</a>";
    }

    public static function getFilmActors($idfilm){
        global $db;
        $query = "select actors.\"idActor\", name from actors join \"filmActors\" on \"filmActors\".\"idActor\" = actors.\"idActor\" where \"filmActors\".\"idFilm\" = '$idfilm'";
        $act = $db->prepare($query);
        $act->execute();
        $actors = array();
        while ($row = $act->fetch(PDO::FETCH_ASSOC)) {
            $actor = new Actor($row["idActor"],$row["name"]);
            $actors[] = $actor;
        }
        return $actors;
    }
}
