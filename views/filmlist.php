<?php
$fromAdd = false;
if (isset(explode('-', $_GET["page"])[1])) {
    switch (explode('-', $_GET["page"])[1]) {
        case 'a':
            if (isset(explode('-', $_GET["page"])[2])) {
                $actor = Actor::load(explode('-', $_GET["page"])[2], "s");
                echo "<div class='autres'><h4>Films contenant l'acteur/actrice " . $actor . "</h4><a class=\"link\" href=\".?page=view\">Retour</a></div>";
            }
            break;
        case 'd':
            if (isset(explode('-', $_GET["page"])[2])) {
                $director = Director::load(explode('-', $_GET["page"])[2], "s");
                echo "<div class='autres'><h4>Films dirigés par " . $director . "</h4><a class=\"link\" href=\".?page=view\">Retour</a></div>";
            }
            break;
        case 'g':
            if (isset(explode('-', $_GET["page"])[2])) {
                $genre = Genre::load(explode('-', $_GET["page"])[2]);
                echo "<div class='autres'><h4>Films du genre " . $genre . "</h4><a class=\"link\" href=\".?page=view\">Retour</a></div>";
            }
            break;
        case 'w':
            $fromAdd = true;
        default:
            break;
    }
}
?>
<div class="filtres">
    <input type="text" id="res-search" name="dbsearch" value="" autocomplete="off" placeholder="Recherche">
    Films :
    <select name="viewed" id="viewfilter">
        <option value="all" selected>Tous</option>
        <option value="seen">Vu</option>
        <option value="not-seen">Non vu</option>
    </select>
    par :
    <select name="viewedby" id="viewuserfilter">
        <option value="all">Tout le monde</option>
        <?php
        foreach (json_decode($_SESSION["users"]) as $user) {
            echo "<option value=\"$user->username\">$user->username</option>";
        } ?>
    </select>
    <div class="filterButton noselect">Wishlist</div>
</div>
<p id="count"></p>
<button id="newOnes" class="ordertab <?php echo ($fromAdd?"ordereddown":"unordered"); ?>">Tri par date d'ajout</button>
<button id="newOnes" class="ordertab unordered">Tri par type</button>
<div class='data'>
    <table>
        <thead>
            <th class="ordertab unordered" value="title">Titre</th>
            <th class="hideOnPhone ordertab unordered">Année</th>
            <th class="ordertab unordered">Longueur</th>
            <th class="hideOnPhone">Directeur(s)</th>
            <th class="hideOnPhone">Acteurs Principaux</th>
            <th class="hideOnPhone">Genre(s)</th>
            <th class="hideOnPhone ordertab unordered">Localisation</th>
            <th class="ordertab unordered">Score</th>
            <?php echo (isset($_SESSION["user"]) ? "<th class='ordertab unordered'>Mon score</th>" : ""); ?>

        </thead>
        <tbody id="filmtable">
        </tbody>
    </table>
</div>
<script>
    function searchNormalize(string) {
        return string.toLowerCase().normalize("NFD").replace(/\p{Diacritic}/gu, "");
    }

    var table = document.querySelector('#filmtable');
    var films = <?php echo $_SESSION["films"]; ?>;
    var view = '<?php echo $_GET["page"]; ?>';
    var user = <?php echo (isset($_SESSION["user"]) ? "\"" . $_SESSION["user"] . "\"" : "null"); ?>;
    var users = <?php echo $_SESSION["users"]; ?>;

    var orients = document.querySelectorAll('.ordertab');

    var order = "<?php echo ($fromAdd?"Tri par date d'ajout":"none"); ?>";

    var amount = document.querySelector("#count");

    var searchblock = document.querySelector("#res-search");

    var viewblock = document.querySelector("#viewfilter");

    var viewuserblock = document.querySelector("#viewuserfilter");

    var wishlistfilter = document.querySelector(".filterButton");

    var searchterm = ((searchblock.value == null) ? "" : searchblock.value);

    var showWishlist = false;

    var wishlist = [];

    wishlist = films.filter(function(element) {
        if (element.location == 'Wishlist') {
            return element;
        }
    });

    wishlist.forEach(element => {
        const index = films.indexOf(element);
        if (index > -1) {
            films.splice(index, 1);
        }

    });

    searchblock.addEventListener('keyup', function() {
        searchterm = ((searchblock.value == null) ? "" : searchblock.value);
        showFilms();
    });

    viewblock.addEventListener('change', function() {
        showFilms();
    });
    viewuserblock.addEventListener('change', function() {
        showFilms();
    });

    wishlistfilter.addEventListener('click', function() {
        if (wishlistfilter.classList.contains('selected')){
            wishlistfilter.classList.remove('selected');
            showWishlist = false;
            showFilms();
        } else {
            wishlistfilter.classList.add('selected');
            showWishlist = true;
            showFilms();
        }
    });

    orients.forEach(clickable => {
        clickable.addEventListener('click', function() {
            orients.forEach(clicky => {
                if (clicky != clickable) {
                    if (clicky.getAttribute('class').includes("hideOnPhone")) {
                        clicky.setAttribute("class", "hideOnPhone ordertab unordered");
                    } else {
                        clicky.setAttribute("class", "ordertab unordered");
                    }
                }
            });
            if (clickable.getAttribute('class').includes("unordered")) {
                if (clickable.getAttribute('class').includes("hideOnPhone")) {
                    clickable.setAttribute("class", "hideOnPhone ordertab ordereddown");
                } else {
                    clickable.setAttribute("class", "ordertab ordereddown");
                }
                order = clickable.innerText + "-down";
            } else if (clickable.getAttribute('class').includes("ordereddown")) {
                if (clickable.getAttribute('class').includes("hideOnPhone")) {
                    clickable.setAttribute("class", "hideOnPhone ordertab orderedup");
                } else {
                    clickable.setAttribute("class", "ordertab orderedup");
                }
                order = clickable.innerText + "-up";
            } else {
                if (clickable.getAttribute('class').includes("hideOnPhone")) {
                    clickable.setAttribute("class", "hideOnPhone ordertab unordered");
                } else {
                    clickable.setAttribute("class", "ordertab unordered");
                }
                order = "none";
            }
            showFilms();
        });
    });

    showFilms();

    function showFilms() {
        table.innerHTML = "";
        table.scrollTop = 0;

        var sortedFilms = [];
        var data = [];

        if (showWishlist) {
            data = wishlist;
        } else {
            data = films;
        }

        switch (view.split('-')[1]) {
            case 'a':
                sortedFilms = data.filter(f => f.actors.some(a => a.idActor == view.split('-')[2]) ? true : false);
                break;
            case 'd':
                sortedFilms = data.filter(f => f.directors.some(d => d.idDirector == view.split('-')[2]) ? true : false);
                break;
            case 'g':
                sortedFilms = data.filter(f => f.genres.some(g => g.idGenre == view.split('-')[2]) ? true : false);
                break;
            default:
                sortedFilms = data;
                break;
        }

        if (viewuserblock.value == 'all') {
            switch (viewblock.value) {
                case 'not-seen':
                    sortedFilms = sortedFilms.filter(f => f.seenby.length === 0);
                    break;
                case 'seen':
                    sortedFilms = sortedFilms.filter(f => f.seenby.length === users.length);
                    break;
                default:
                    break;
            }
        } else {
            switch (viewblock.value) {
                case 'not-seen':
                    sortedFilms = sortedFilms.filter(f => f.seenby.find(u => u.username == viewuserblock.value) ? false : true);
                    break;
                case 'seen':
                    sortedFilms = sortedFilms.filter(f => f.seenby.find(u => u.username == viewuserblock.value) ? true : false);
                    break;
                default:
                    break;
            }

        }

        function defaultSort() {
            sortedFilms.sort(function(a, b) {
                return a.idFilm > b.idFilm ? -1 : 1;
            });
        }
        switch (order.split('-')[0]) {
            case 'none':
                sortedFilms.sort(function(a, b) {
                    return a.idFilm > b.idFilm ? -1 : 1;
                });
                break;
            case 'Titre':
                defaultSort();
                if (order.split('-')[1] == 'up') {
                    sortedFilms.sort(function(a, b) {
                        return searchNormalize(a.comparename) > searchNormalize(b.comparename) ? -1 : 1;
                    });
                } else {
                    sortedFilms.sort(function(a, b) {
                        return searchNormalize(b.comparename) > searchNormalize(a.comparename) ? -1 : 1;
                    });
                }
                break;
            case 'Tri par type':
                defaultSort();
                if (order.split('-')[1] == 'up') {
                    sortedFilms.sort(function(a, b) {
                        return searchNormalize(a.type) > searchNormalize(b.type) ? -1 : 1;
                    });
                } else {
                    sortedFilms.sort(function(a, b) {
                        return searchNormalize(b.type) > searchNormalize(a.type) ? -1 : 1;
                    });
                }
                break;
            case 'Année':
                defaultSort();
                if (order.split('-')[1] == 'up') {
                    sortedFilms.sort(function(a, b) {
                        return a.year - b.year;
                    });
                } else {
                    sortedFilms.sort(function(a, b) {
                        return b.year - a.year;
                    });
                }
                break;
            case 'Longueur':
                defaultSort();
                if (order.split('-')[1] == 'up') {
                    sortedFilms.sort(function(a, b) {
                        if (a.lengthMins === null) {
                            return 1;
                        }
                        if (b.lengthMins === null) {
                            return -1;
                        }
                        if (a.lengthMins === b.lengthMins) {
                            return 0;
                        }
                        return a.lengthMins - b.lengthMins;
                    });
                } else {
                    sortedFilms.sort(function(a, b) {
                        if (a.lengthMins === null) {
                            return 1;
                        }
                        if (b.lengthMins === null) {
                            return -1;
                        }
                        if (a.lengthMins === b.lengthMins) {
                            return 0;
                        }
                        return b.lengthMins - a.lengthMins;
                    });
                }
                break;
            case 'Localisation':
                defaultSort();
                if (order.split('-')[1] == 'up') {
                    sortedFilms.sort(function(a, b) {
                        return searchNormalize(a.location) > searchNormalize(b.location) ? -1 : 1;
                    });
                } else {
                    sortedFilms.sort(function(a, b) {
                        return searchNormalize(b.location) > searchNormalize(a.location) ? -1 : 1;
                    });
                }
                break;
            case 'Score':
                defaultSort();
                if (order.split('-')[1] == 'up') {
                    sortedFilms.sort(function(a, b) {
                        if (a.score === null) {
                            return 1;
                        }
                        if (b.score === null) {
                            return -1;
                        }
                        if (a.score === b.score) {
                            return 0;
                        }
                        return a.score - b.score;
                    });
                } else {
                    sortedFilms.sort(function(a, b) {
                        if (a.score === null) {
                            return 1;
                        }
                        if (b.score === null) {
                            return -1;
                        }
                        if (a.score === b.score) {
                            return 0;
                        }
                        return b.score - a.score;
                    });
                }
                break;
            case 'Mon score':
                defaultSort();
                if (order.split('-')[1] == 'up') {
                    sortedFilms.sort(function(a, b) {
                        if (a.seenby.find(u => u.username == user) == null || a.seenby.find(u => u.username == user).filmScore == null) {
                            return 1;
                        }
                        if (b.seenby.find(u => u.username == user) == null || b.seenby.find(u => u.username == user).filmScore == null) {
                            return -1;
                        }
                        if (a.seenby.find(u => u.username == user).filmScore == b.seenby.find(u => u.username == user).filmScore) {
                            return 0;
                        }
                        return a.seenby.find(u => u.username == user).filmScore - b.seenby.find(u => u.username == user).filmScore;
                    });
                } else {
                    sortedFilms.sort(function(a, b) {
                        if (a.seenby.find(u => u.username == user) == null || a.seenby.find(u => u.username == user).filmScore == null) {
                            return 1;
                        }
                        if (b.seenby.find(u => u.username == user) == null || b.seenby.find(u => u.username == user).filmScore == null) {
                            return -1;
                        }
                        if (a.seenby.find(u => u.username == user).filmScore == b.seenby.find(u => u.username == user).filmScore) {
                            return 0;
                        }
                        return b.seenby.find(u => u.username == user).filmScore - a.seenby.find(u => u.username == user).filmScore;
                    });
                }
                break;
            case 'Tri par date d\'ajout':
                defaultSort();
                if (order.split('-')[1] == 'up') {
                    sortedFilms.sort(function(a, b) {
                        if (a.added === null) {
                            return -1;
                        }
                        if (b.added === null) {
                            return 1;
                        }
                        if (a.added === b.added) {
                            return 0;
                        }
                        return new Date(a.added.date) - new Date(b.added.date);
                    });
                } else {
                    sortedFilms.sort(function(a, b) {
                        if (a.added === null) {
                            return 1;
                        }
                        if (b.added === null) {
                            return -1;
                        }
                        if (a.added === b.added) {
                            return 0;
                        }
                        return new Date(b.added.date) - new Date(a.added.date);
                    });
                }
                break;
        }

        var shownFilms = 0;

        sortedFilms.forEach(film => {
            function showFilmRow() {
                shownFilms++;
                var tr = create('tr', table, '');
                var titletd = create('td', tr, '');
                var titlebox = create('div', titletd, '', ["class-titlebox"]);
                create('div', titlebox, '').innerHTML = film.namehtml;
                if (film.type == "TVSeries") {
                    create("img", titlebox, '', ["src-https://cdn-icons-png.flaticon.com/512/6482/6482155.png", "height-20px", "class-noselect"]);
                } else if (film.type == "Movie") {
                    create("img", titlebox, '', ["src-https://cdn-icons-png.flaticon.com/512/108/108884.png", "height-20px", "class-noselect"]);
                }
                create('td', tr, film.year, ["class-hideOnPhone"]);
                create('td', tr, film.length);
                var directors = create('td', tr, '', ["class-hideOnPhone"]);
                var directorList = create('ul', directors, '');
                film.directors.forEach(director => {
                    create('li', directorList, '').innerHTML = director.namehtml;
                });
                var actors = create('td', tr, '', ["class-hideOnPhone"]);
                var actorList = create('ul', actors, '');
                film.actors.forEach(actor => {
                    create('li', actorList, '').innerHTML = actor.namehtml;
                });
                var genres = create('td', tr, '', ["class-hideOnPhone"]);
                var genreList = create('ul', genres, '');
                film.genres.forEach(genre => {
                    create('li', genreList, '').innerHTML = genre.namehtml;
                });
                create('td', tr, film.location, ["class-hideOnPhone"]);
                create('td', tr, (film.score == null) ? '-' : film.score);
                if (user != null) {
                    create('td', tr, (film.seenby.find(u => u.username == user) == null ? '-' : film.seenby.find(u => u.username == user).filmScore == null ? '-' : film.seenby.find(u => u.username == user).filmScore));
                }
            };
            if (searchterm != "") {
                if (searchNormalize(film.title).includes(searchNormalize(searchterm))) {
                    showFilmRow();
                } else if (film.localtitle != null && searchNormalize(film.localtitle).includes(searchNormalize(searchterm))) {
                    showFilmRow();
                } else if (film.directors != null &&
                    film.directors.map(element => {
                        return searchNormalize(element.name).includes(searchNormalize(searchterm));
                    }).includes(true)) {
                    showFilmRow();
                } else if (
                    film.actors.map(element => {
                        return searchNormalize(element.name).includes(searchNormalize(searchterm));
                    }).includes(true)) {
                    showFilmRow();
                } else if (
                    film.genres.map(element => {
                        return searchNormalize(element.genreName).includes(searchNormalize(searchterm));
                    }).includes(true)) {
                    showFilmRow();
                } else if (searchNormalize(film.location).includes(searchNormalize(searchterm))) {
                    showFilmRow();
                } else if (film.comment != null && searchNormalize(film.comment).includes(searchNormalize(searchterm))) {
                    showFilmRow();
                } else if (film.plot != null && searchNormalize(film.plot).includes(searchNormalize(searchterm))) {
                    showFilmRow();
                }
            } else {
                showFilmRow();
            }
        });
        amount.innerText = shownFilms + " sur " + data.length + " films séléctionnés";
    }
</script>