<?php

class Film extends Model implements \JsonSerializable
{
    use JsonSerializer;

    protected $idFilm;
    protected $title;
    protected $length;
    protected $year;
    protected $image;
    protected $location;
    protected $comment;
    protected $type;
    protected $plot;
    protected $lengthMins;
    protected $localtitle;
    protected $actors;
    protected $directors;
    protected $genres;
    protected $seenby;
    protected $score;
    protected $namehtml;
    protected $comparename;
    protected $added;

    const DBNAME = 'films';

    public function __construct($idFilm, $title, $length, $year, $image, $location, $comment, $type, $plot, $lengthMins, $localtitle)
    {
        $this->idFilm = $idFilm;
        $this->title = $title;
        $this->length = $length;
        $this->year = $year;
        $this->image = $image;
        $this->location = $location;
        $this->comment = $comment;
        $this->type = $type;
        $this->plot = $plot;
        $this->lengthMins = $lengthMins;
        $this->localtitle = $localtitle;
        $this->actors = Actor::getFilmActors($this->idFilm);
        $this->directors = Director::getFilmDirectors($this->idFilm);
        $this->genres = Genre::getFilmGenres($this->idFilm);
        $this->seenby = User::getFilmUsers($this->idFilm);
        $this->score = Film::getFilmScore($this->idFilm);
        $this->namehtml = $this->FilmNameLink();
        $this->comparename = $this->__toString();
        $this->added = $this->GetAddedBy();
    }

    public function __toString()
    {
        return empty($this->localtitle) ? $this->title : $this->localtitle;
    }

    public static function getFilmScore($idfilm)
    {
        global $db;
        $query = 'select round(avg(score),2) as avg from "userScore" where "idFilm"=\'' . $idfilm . '\'';
        $score = $db->prepare($query);
        $score->execute();
        return $score->fetch(PDO::FETCH_ASSOC)["avg"];
    }

    public function FilmNameLink()
    {
        if (!empty($this->localtitle)) {
            return "<td><a class='link' href=\".?page=" . $this->idFilm . "\">" . $this->localtitle . "</a></td>";
        } else {
            return "<td><a class='link' href=\".?page=" . $this->idFilm . "\">" . $this->title . "</a></td>";
        }
    }

    public function GetAddedBy()
    {
        global $db;
        $query = 'select date, username from filmadded where "idFilm"=\'' . $this->idFilm . '\'';
        $added = $db->prepare($query);
        $added->execute();
        $added = $added->fetch(PDO::FETCH_ASSOC);
        if (gettype($added) == "array") {
            return $added;
        } else {
            return null;
        }
    }

    public static function getLocations()
    {
        $list = [];
        $sqlLocs = "select distinct location from films order by location";
        global $db;
        $locs = $db->prepare($sqlLocs);
        $locs->execute();
        while ($row = $locs->fetch(PDO::FETCH_ASSOC)) {
            $list[] = $row['location'];
        }
        return $list;
    }

    public static function removeFilm($idfilm)
    {
        global $db;
        $steven = $db->prepare("DELETE FROM films WHERE \"idFilm\"='" . $idfilm . "'");
        $steven->execute();
    }
    public static function addFilm($filminfo)
    {
        global $db;
        $filminfo["comment"] = strip_tags($filminfo["comment"]);
        $filminfo["location"] = strip_tags($filminfo["location"]);
        $filminfo["localtitle"] = strip_tags($filminfo["localtitle"]);
        $filminfo["title"] = strip_tags($filminfo["title"]);
        $filminfo["plot"] = strip_tags($filminfo["plot"]);

        $filminfo["type"] = preg_replace('/\s+/', '', $filminfo["type"]);

        $data = [
            'idFilm' => $filminfo["idFilm"],
            'title' => $filminfo["title"],
            'length' => (empty($filminfo["length"]) ? "" : $filminfo["length"]),
            'year' => $filminfo["year"],
            'image' => $filminfo["poster"],
            'location' => $filminfo["location"],
            'comment' => $filminfo["comment"],
            'type' => $filminfo["type"],
            'plot' => $filminfo["plot"],
            'lengthmins' => (empty($filminfo["lengthmins"]) ? null : $filminfo["lengthmins"]),
            'localtitle' => (empty($filminfo["localtitle"]) ? null : $filminfo["localtitle"])
        ];
        $sql = "INSERT INTO films (\"idFilm\", title, length, year, image, location, comment, type, plot, \"lengthMins\", localtitle) VALUES (:idFilm, :title, :length, :year, :image, :location, :comment, :type, :plot, :lengthmins, :localtitle)";
        $stmt = $db->prepare($sql);
        $stmt->execute($data);

        foreach ($filminfo["actors"] as $key => $value) {
            $filminfo["actors"][$key] = strip_tags(preg_replace("#\'#", "''", $value));
            if (!Actor::inDB($filminfo["actors"][$key])) {
                $sql = "INSERT INTO actors (name) VALUES (?)";
                $db->prepare($sql)->execute([$value]);
            }
            $getid = $db->query("SELECT \"idActor\" FROM actors WHERE name='" . $filminfo["actors"][$key] . "' LIMIT 1");
            $actorId = $getid->fetch()["idActor"];
            $sql = "INSERT INTO \"filmActors\" (\"idFilm\",\"idActor\") VALUES (?,?)";
            $db->prepare($sql)->execute([$filminfo["idFilm"], $actorId]);
        }
        if (!empty($filminfo["directors"])) {
            foreach ($filminfo["directors"] as $key => $value) {
                $filminfo["directors"][$key] = strip_tags(preg_replace("#\'#", "''", $value));
                if (!Director::inDB($filminfo["directors"][$key])) {
                    $sql = "INSERT INTO directors (name) VALUES (?)";
                    $db->prepare($sql)->execute([$value]);
                }
                $getid = $db->query("SELECT \"idDirector\" FROM directors WHERE name='" . $filminfo["directors"][$key] . "' LIMIT 1");
                $directorId = $getid->fetch()["idDirector"];
                $sql = "INSERT INTO \"filmDirectors\" (\"idFilm\",\"idDirector\") VALUES (?,?)";
                $db->prepare($sql)->execute([$filminfo["idFilm"], $directorId]);
            }
        }
        foreach ($filminfo["genres"] as $key => $value) {
            $filminfo["genres"][$key] = strip_tags(preg_replace("#\'#", "''", $value));
            if (!Actor::inDB($filminfo["genres"][$key])) {
                $sql = "INSERT INTO genre (\"genreName\") VALUES (?)";
                $db->prepare($sql)->execute([$value]);
            }
            $getid = $db->query("SELECT \"idGenre\" FROM genre WHERE \"genreName\"='" . $filminfo["genres"][$key] . "' LIMIT 1");
            $genreid = $getid->fetch()["idGenre"];
            $sql = "INSERT INTO \"filmGenre\" (\"idFilm\",\"idGenre\") VALUES (?,?)";
            $db->prepare($sql)->execute([$filminfo["idFilm"], $genreid]);
        }

        if (isset($filminfo["user"])) {
            User::SetUserScore($filminfo["idFilm"],$filminfo["user"],$filminfo["score"]);
            if (isset($filminfo["seen"]) && $filminfo["seen"] == "on") {
                $seen = true;
            } else {
                $seen = false;
            }
            User::SetUserSeen($filminfo["idFilm"],$filminfo["user"],$seen);
            $sql = "INSERT INTO filmadded (\"idFilm\", username, date) VALUES (?,?,?)";
            $db->prepare($sql)->execute([$filminfo["idFilm"], $filminfo["user"], date('Y-m-d')]);
        } else {
            $sql = "INSERT INTO filmadded (\"idFilm\", username, date) VALUES (?,?,?)";
            $db->prepare($sql)->execute([$filminfo["idFilm"], PDO::PARAM_NULL, date('Y-m-d')]);
        }
    }

    public static function inDB($idfilm)
    {
        $sql = "select \"idFilm\" from films where \"idFilm\"='" . $idfilm . "'";
        global $db;
        $check = $db->prepare($sql);
        $check->execute();
        if ($check->fetch(PDO::FETCH_ASSOC) == false) {
            return false;
        } else {
            return true;
        }
    }

    public function FilmAsTableRow()
    {
        echo "<tr>\n";
        if (!empty($this->localtitle)) {
            echo "<td><a class='link' href=\".?page=" . $this->idFilm . "\">" . $this->localtitle . "</a></td>";
        } else {
            echo "<td><a class='link' href=\".?page=" . $this->idFilm . "\">" . $this->title . "</a></td>";
        }
        echo "<td class=\"hideOnPhone\">" . $this->type . "</td>\n";
        echo "<td class=\"hideOnPhone\">" . $this->year . "</td>\n";
        echo "<td>" . $this->length . "</td>\n";
        echo "<td class=\"hideOnPhone\"><ul>\n";
        foreach ($this->directors as $d)
            echo "<li>" . $d->DirectorNameLink() . "</li>";
        echo "</ul></td>\n";
        echo "<td class=\"hideOnPhone\"><ul>\n";
        foreach ($this->actors as $a)
            echo "<li>" . $a->ActorNameLink() . "</li>";
        echo "</ul></td>\n";
        echo "<td class=\"hideOnPhone\"><ul>\n";
        foreach ($this->genres as $g)
            echo "<li>" . $g->GenreNameLink() . "</li>";
        echo "</ul></td>\n";
        echo "<td class=\"hideOnPhone\">" . $this->location . "</td>\n";
        if (isset($this->score))
            echo "<td>" . $this->score . "</td>\n";
        else
            echo "<td> - </td>\n";
        echo "</tr>\n";
    }

    public function FilmPage()
    {
        echo "<div class=\"film\">";
        echo "\n<div class='poster'><img src='" . $this->image . "'></div>";
        echo "<div id=\"infoDiv\">";
        if (isset($this->score))
            echo "\n<p class=\"infos\" id=\"filmScore\"><span>Score : </span>" . $this->score  . "</p>";
        else
            echo "\n<p class=\"infos\" id=\"noFilmScore\"><span>Pas de score entré</span></p>";
        echo "\n<p class=\"infos\"><span>Titre : </span> " . $this->title  . "</p>";
        if (!empty($this->localtitle)) {
            echo "\n<p class=\"infos\"><span>Titre Français: </span> " . $this->localtitle  . "</p>";
        }
        echo "\n<p class=\"infos\"><span>Type : </span> " . $this->type  . "</p>";
        echo "\n<p class=\"infos\"><span>Année : </span> " . $this->year  . "</p>";
        if (!empty($this->length))
            echo "\n<p class=\"infos\"><span>Durée : </span> " . $this->length  . "</p>";
        else
            echo "\n<p class=\"infos\"><span>Durée non renseigné</span></p>";
        echo "<a class='link' href=\"https://www.imdb.com/title/" . $this->idFilm . "\" target='_blank'>IMDB</a>";
        echo "\n<div class=\"list\"><span>Directeur(s) : </span>\n<ul>";
        foreach ($this->directors as $d)
            echo "\n<li>" . $d->DirectorNameLink() . "</li>";
        echo "\n</ul></div>";
        echo "\n<div class=\"list\"><span>Acteur(s) Principaux :</span>\n<ul>";
        foreach ($this->actors as $a)
            echo "\n<li>" . $a->ActorNameLink() . "</li>";
        echo "\n</ul></div>";
        echo "\n<div class=\"list\"><span>Genre(s) : </span>\n<ul>";
        foreach ($this->genres as $g)
            echo "\n<li>" . $g->GenreNameLink() . "</li>";
        echo "\n</ul></div>";
        echo "\n<p class=\"infos\" ><span>Synopsis :</span></p><p id=\"filmSynopsis\">" . $this->plot  . "</p>";
        echo "\n<p class=\"infos\"><span>Localisation : </span>" . $this->location  . "</p>";
        if (!empty($this->added) && $this->added != "null")
            echo "\n<p class=\"infos\"><span>Ajouté par : </span>" . (empty($this->added["username"]) ? "Inconnu" : $this->added["username"]) . " le " . date('d/m/Y', strtotime($this->added["date"])) . " </p>";
        if (!empty($this->comment))
            echo "\n<p class=\"infos\"><span>Commentaire : </span></p><p id=\"filmComment\">" . $this->comment  . "</p>";
        else
            echo "\n<p class=\"infos\"><span>Pas de commentaire entré</span></p>";
        echo "\n</div>";
        echo "\n</div>";
    }
}
