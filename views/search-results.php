<?php
if (empty(explode('-', $_GET["page"])[1])) {
    return include_once 'views/404.php';
} else if (explode('-', $_GET["page"])[1] == 'noresult') {
    return "<div class='autres'>Aucun résultats <a class=\"link\" href=\".?page=search\">Retour</a></div>";
} else {
    $imdbID = explode('-', $_GET["page"])[1];
    $others = $_SESSION["others"];
}
if (Film::inDB($imdbID)) {
    $film = Film::load($imdbID, "s");
    echo "<div class=\"autres\"><h4>Le film \"" . $film->title . "\" est déjà dans la base de données.</h4>";
    echo "<a class='link' href=\".?page=" . $imdbID . "\">Voir la page</a>";
    echo "</div>";
    ApiManager::showOthers($imdbID, $others);
} else {
    $context = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "Accept-languange: en\r\n" .
                "Cookie: lc-main=en_US\r\n"
        ]
    ]);
    if ($page = file_get_contents('https://www.imdb.com/title/' . $imdbID, false, $context)) {
        $preview = ApiManager::getFilmPreview($page, $imdbID);
        echo "<form action=\"./?page=view-w\" method=\"post\" id=\"addfilmform\"><div class='add'>\n";
        echo "<input type=\"hidden\" name=\"film[idFilm]\" value=\"" . $imdbID . "\">";
        echo "<ul>\n";
        echo "<li>Titre : " . (!empty($preview["title"]) ? $preview["title"] : "(Non Disponible)") . "</li>\n";
        echo "<input type=\"hidden\" name=\"film[title]\" value=\"" . $preview["title"] . "\">";
        echo "<li>Type : " . (!empty($preview["type"]) ? $preview["type"] : "(Non Disponible)") . "</li>\n";
        echo "<input type=\"hidden\" name=\"film[type]\" value=\"" . $preview["type"] . "\">";
        echo "<li>Année : " . (!empty($preview["year"]) ? $preview["year"] : "(Non Disponible)") . "</li>\n";
        echo "<input type=\"hidden\" name=\"film[year]\" value=\"" . $preview["year"] . "\">";
        echo "<li>Longueur : " . (!empty($preview["length"]) ? $preview["length"] : "(Non Disponible)") . "</li>\n";
        if(!empty($preview["length"])){
            echo "<input type=\"hidden\" name=\"film[length]\" value=\"" . $preview["length"] . "\">";
            echo "<input type=\"hidden\" name=\"film[lengthmins]\" value=\"" . $preview["lengthmins"] . "\">";
        }
        echo "<li><img src=\"" . $preview["poster"] . "\" width='100'></li>\n";
        echo "<input type=\"hidden\" name=\"film[poster]\" value=\"" . $preview["poster"] . "\">";
        echo "<li>Directeur(s) : " . (!empty($preview["directors"]) ? "" : "(Non Disponible)");
        foreach ($preview["directors"] as $value) {
            echo $value . ((next($preview["directors"]) == true) ? ", " : "");
            echo "<input type=\"hidden\" name=\"film[directors][]\" value=\"" . $value . "\">";
        }
        echo "</li>\n";
        echo "<li>Acteur(s) : " . (!empty($preview["actors"]) ? "" : "(Non Disponible)");
        foreach ($preview["actors"] as $value) {
            echo $value . ((next($preview["actors"]) == true) ? ", " : "");
            echo "<input type=\"hidden\" name=\"film[actors][]\" value=\"" . $value . "\">";
        }
        echo "</li>\n";
        echo "<li>Genre(s) : " . (!empty($preview["genres"]) ? "" : "(Non Disponible)");
        foreach ($preview["genres"] as $value) {
            echo $value . ((next($preview["genres"]) == true) ? ", " : "");
            echo "<input type=\"hidden\" name=\"film[genres][]\" value=\"" . $value . "\">";
        }
        echo "</li>\n";
        echo "<li>Synopsis : " . $preview["plot"] . "</li>\n";
        echo "<input type=\"hidden\" name=\"film[plot]\" value=\"" . $preview["plot"] . "\">";
        echo "</ul>\n"; ?>
        <div id="addtext">
            Ajouter à la base de données ?
            <div class="buttoned" id="filmadd">Ajouter</div>
        </div>
        </div>
<?php
        ApiManager::showOthers($imdbID, $others);
    } else
        echo "<div class='warning topPage'>IMDB ne répond pas <a class=\"link\" href=\".?page=search\">Retour</a></div>";
}
?>
<script>
    var button = document.getElementById('filmadd');
    if (button != null) {
        var film = <?php echo json_encode($preview); ?>;
        var locations = <?php echo json_encode(Film::getLocations()); ?>;
        var user = '<?php echo (isset($_SESSION["user"]) ? $_SESSION["user"] : 'null'); ?>';
        var autres = document.querySelector('.autres');
        var form = document.querySelector('#addfilmform');
        var texti = document.querySelector('#addtext');
        button.addEventListener('click', element => {
            autres.innerHTML = "";
            texti.innerHTML = "";
            button.innerHTML = "";
            button.remove();
            create('h3', form, 'Autres Infos: ');
            create('label', form, 'Localisations: ');
            var locationinput = create('input', form, '', ["list-locations", "name-film[location]", "class-locationinput", "autocomplete-off"]);
            var datalist = create('datalist', locationinput, '', ["id-locations"]);
            locations.forEach(element => {
                create('option', datalist, '', ["value-" + element]);
            });
            create('label', form, 'Commentaire:');
            create('input', form, '', ["name-film[comment]", "type-text", "autocomplete-off"]);
            create('label', form, 'Titre Français (si applicable):');
            create('input', form, '', ["name-film[localtitle]", "type-text", "autocomplete-off"]);
            if (user != "null") {
                create('label', form, 'Score (0-5) : ');
                create('input', form, '', ["name-film[score]", "type-number", "min-0", "max-5"]);
                create('label', form, 'Vu ? ');
                create('input', form, '', ["name-film[seen]", "type-checkbox"]);
                create('input', form, '', ["name-film[user]", "type-hidden", "value-" + user]);
            }
            create('input', form, '', ["name-action", "type-submit", "value-Ajouter", "id-addConfirm"]);
        });
    }
</script>
</form>
<?php return ""; ?>