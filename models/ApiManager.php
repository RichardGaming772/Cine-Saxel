<?php

class ApiManager
{
    public static function getRemainingCalls()
    {
        global $db;
        $st = $db->prepare('select "apiCount" FROM "apiTracker" LIMIT 1');
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return 100 - $row["apiCount"];
    }

    public static function findIMDBID($title, $year, $type)
    {
        $title = rawurlencode($title);
        $pattern = '#<a href=\"\/title\/(tt[0-9]*)\/\?ref_=adv_li_tt\".>([^<]*?)</a>\n    <span class="lister-item-year text-muted unbold">(.*?)</span>#ms';
        if (!empty($year))
            $link = 'https://www.imdb.com/search/title/?title=' . $title . $type . '&release_date=' . $year . '-01-01,' . $year . '-12-31';
        else {
            $link = 'https://www.imdb.com/search/title/?title=' . $title . $type;
        }
        if ($page = file_get_contents($link)) {
            if (preg_match_all($pattern, $page, $matches)) {
                $result = array();
                $result[0] = $matches[1][0];
                $result[1] = $matches;
                return $result;
            } else {
                echo "<div class='warning topPage'>Aucun résultat <a class=\"link\" href=\".?page=search\">Retour</a></div>";
            }
        } else
            echo "<div class='warning topPage'>IMDB ne répond pas <a class=\"link\" href=\".?page=search\">Retour</a></div>";
    }

    public static function showOthers($imdbID,$others){
        echo "<div class='autres'>";
        if (isset($others[0][1])) {
            if (in_array(explode('-', $_GET["page"])[1], $others[1])) {
                echo "\n<h4>Autres résultat(s) :</h4>";
                echo "\n<ul>";
                $resultIndex = 0;
                foreach ($others[2] as $element) {
                    if ($resultIndex <= 5 && isset($others[2][$resultIndex])) {
                        if (!Film::inDB($others[1][$resultIndex]) && $others[1][$resultIndex] != $imdbID) {
                            echo "<li><a class='link' href=\".?page=searchresults-" . $others[1][$resultIndex] . "\">" . $others[2][$resultIndex] . " " . $others[3][$resultIndex] . "</a></li>";
                        }
                        $resultIndex += 1;
                    }
                }
                echo "\n</ul>";
            } else {
                echo "<h4>Autres résultats non disponible pour cette recherche.</h4>";
            }
        } else {
            echo "<h4>Aucun autres résultats.</h4>";
        }
        echo "\n</div>";
    }

    public static function getFilmPreview($page, $imdbID)
    {
        $tempfilm = array();

        $regex = '#ref_=tt_cl_t[^>]*>([^<]*)#';
        preg_match_all($regex, $page, $matches);
        $tempactors = array_slice($matches[1], 0, 3);

        $regex = '#ref_=tt_cl_dr[^>]*>([^<]*)#';
        preg_match_all($regex, $page, $matches);
        $tempdirectors = array_slice($matches[1], 0, 3);

        $regex = '#;ref_=tt_ov_inf"><span class="ipc-chip__text">(.*?)</span></a>#';
        preg_match_all($regex, $page, $matches);
        $tempgenres = array_slice($matches[1], 0, 3);

        $regex = '#<div class="ipc-media ipc-media--poster-27x40 ipc-image-media-ratio--poster-27x40 ipc-media--baseAlt ipc-media--poster-l ipc-poster__poster-image ipc-media__img(?:.*?)src(?:s|S)et="(.*?),#';
        preg_match_all($regex, $page, $matches);
        $tempphoto = $matches[1][0];

        $regex = '#data-testid="hero-title-block__title" class="(?:.*?)">(.*?)</h1>#';
        preg_match_all($regex, $page, $matches);
        $temptitle = $matches[1][0];

        $regex = '#ref_=tt_ov_rdat">([0-9]*?)(?:–(?: |[0-9]*?)|)<#';
        preg_match_all($regex, $page, $matches);
        $tempyear = $matches[1][0];

        $regex = '#Runtime</button><div class="ipc-metadata-list-item__content-container">(.*?)</div>#';
        preg_match_all($regex, $page, $matches);
        $templength = strip_tags($matches[1][0]);
        $regex = '#([0-9]) hour(?:s | )([0-9]*) minute(?:s|)#m';
        preg_match_all($regex, $templength, $matches, PREG_SET_ORDER, 0);
        if(!empty($matches)){
            $templength = $matches[0];
            $templength[0] = preg_replace($regex, "$1h $2min", $templength[0]);
            $tempfilm["length"] = $templength[0];
            $tempfilm["lengthmins"] = $templength[1] * 60 + $templength[2];
        } else {
            $tempfilm["length"] = null;
            $tempfilm["lengthmins"] = null;
        }

        if (preg_match('#<li role="presentation" class="ipc-inline-list__item">TV Series#', $page) == 1) {
            $temptype = "TV Series";
        } else {
            $temptype = "Movie";
        }

        $regex = '#<span role="presentation" data-testid="plot-xl" class="(?:.*?)">(.*?)(?:(?:</span>)|(?:<!--))#';
        preg_match_all($regex, $page, $matches);
        $tempplot = $matches[1][0];

        $tempfilm["id"] = $imdbID;
        $tempfilm["title"] = $temptitle;
        $tempfilm["poster"] = $tempphoto;
        $tempfilm["type"] = $temptype;
        $tempfilm["year"] = $tempyear;
        $tempfilm["actors"] = $tempactors;
        $tempfilm["directors"] = $tempdirectors;
        $tempfilm["genres"] = $tempgenres;
        $tempfilm["plot"] = $tempplot;

        return $tempfilm;
    }
}