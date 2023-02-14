<?php
include_once "../db.php";
include_once "../models/Model.php";
include_once "../models/Film.php";
include_once "../models/Genre.php";
include_once "../models/Actor.php";
include_once "../models/Director.php";
include_once "../models/User.php";

if (!isset($_SESSION["films"])) {
    $_SESSION["films"] = json_encode(Film::all());
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syndle</title>
    <link rel="stylesheet" href="style.css">
</head>
<a href="../">Retour</a>
<h1>Syndle</h1>

<div id="playBox">
</div>
<h3>Guesses :</h3>
<div id="guessBox">
    <table>
        <thead class="guessCats">
            <tr>
                <th class="title">Title</th>
                <th class="type">Type</th>
                <th class="year">Year</th>
                <th class="length">Length</th>
                <th class="directors">Director(s)</th>
                <th class="actors">Actor(s)</th>
                <th class="genres">Genre(s)</th>
                <th class="location">Location</th>
                <th class="score">Score</th>
            </tr>
        </thead>
        <tbody id="guesses">

        </tbody>
    </table>
</div>

<script defer>
    function create(tag, parent, text, attributes = null) {
        let element = document.createElement(tag)
        element.appendChild(document.createTextNode(text))
        parent.appendChild(element)
        if (attributes) {
            attributes.forEach(attribute => {
                if (attribute.split('-')[0] == 'src' || attribute.split('-')[0] == 'action' || attribute.split('-')[0] == 'value') {
                    var link = "";
                    attribute.split('-').forEach(function callback(value, index) {
                        if (index != 0) {
                            if (index == 1) {
                                link += value;
                            } else {
                                link += "-" + value;
                            }
                        }
                    });
                    element.setAttribute(attribute.split('-')[0], link);
                } else {
                    element.setAttribute(attribute.split('-')[0], attribute.split('-')[1])
                }
            });
        }
        return element
    }


    function searchNormalize(string) {
        return string.toLowerCase().normalize("NFD").replace(/\p{Diacritic}/gu, "");
    }

    function hideList(input) {
        var datalist = document.querySelector("datalist");
        if (input.value) {
            datalist.id = "films";
        } else {
            datalist.id = "";
        }
    }

    var films = <?php echo $_SESSION["films"]; ?>;
    var filmKey = Math.floor(Math.random() * films.length);
    var film = films[filmKey];

    var tries = 0;

    var bigBox = document.getElementById("playBox");

    var input = create('input', bigBox, '', ["type-text", "name-guess", "id-filmGuess", "list-films", "placeholder-Guess the movie"]);

    var datalist = create('datalist', bigBox, '', ["id-films"]);

    films.forEach(function callback(value, index) {
        if (value.localtitle != null) {
            create('option', datalist, '', ["value-" + value.localtitle + " (" + value.title + ")", "id-" + index]);
        } else {
            create('option', datalist, '', ["value-" + value.title, "id-" + index]);
        }
    });

    hideList(input);

    input.addEventListener('keyup', function(element) {
        hideList(input);
    });

    input.addEventListener('change', function(element) {
        var filmselected = false;
        var options = Array.from(input.list.options).map(function(el) {
            return el.value;
        });
        var relevantOptions = options.filter(function(option) {
            return searchNormalize(option).includes(searchNormalize(input.value));
        });
        if (options.includes(input.value)) {
            filmselected = true;
        } else if (relevantOptions.length > 0) {
            input.value = relevantOptions.shift();
            filmselected = true;
        }

        var datalist = document.querySelector("datalist");

        if (filmselected) {
            var guessBox = document.querySelector("#guesses");
            var guess = films[Array.from(input.list.options).filter(op => op.value == input.value)[0].id];
            datalist.children[Object.keys(options).find(key => options[key] == input.value)].remove();
            input.value = "";
            var filmRow = document.createElement('tr');
            guessBox.insertBefore(filmRow, guessBox.firstChild);
            var nameWords = guess.comparename.split(' ');
            var commonWord = false;
            nameWords.forEach(word => {
                word = searchNormalize(word).replace(/\W/g, "");
                var commons = ["le", "les", "the", "a", "un", "le", "to", "i", "in", "of", "de", "from"];
                if (word.length >= 2 && !commons.includes(word)) {
                    filmWords = searchNormalize(film.comparename).split(' ');
                    filmWords.forEach(word => {
                        word = word.replace(/\W/g, "");
                    });
                    if (filmWords.includes(word)) {
                        commonWord = true;
                    }
                }
            });
            create('td', filmRow, guess.comparename, [(guess.comparename == film.comparename ? "class-correct" : (commonWord ? "class-partial" : "class-incorrect"))]);
            create('td', filmRow, guess.type, [(guess.type == film.type ? "class-correct" : "class-incorrect")]);
            create('td', filmRow, guess.year, [(guess.year > film.year ? "class-higher" : (guess.year == film.year ? "class-correct" : "class-lower"))]);
            create('td', filmRow, guess.length, [(guess.lengthMins > film.lengthMins ? "class-higher" : (guess.lengthMins == null ? "class-incorrect" : (guess.lengthMins == film.lengthMins ? "class-correct" : "class-lower")))]);
            var directors = create('td', filmRow, '', ["class-directors"]);
            var directorList = create('ul', directors, '');
            var correct = 0;
            guess.directors.forEach(director => {
                if (film.directors.find(e => e.name == director.name)) {
                    correct++;
                }
                create('li', directorList, director.name);
            });
            directors.setAttribute('class', (correct == 0 ? "incorrect" : (correct == guess.directors.length && correct == film.directors.length ? "correct" : "partial")));
            correct = 0;
            var actors = create('td', filmRow, '', ["class-actors"]);
            var actorList = create('ul', actors, '');
            guess.actors.forEach(actor => {
                if (film.actors.find(e => e.name == actor.name)) {
                    correct++;
                }
                create('li', actorList, actor.name);
            });
            actors.setAttribute('class', (correct == 0 ? "incorrect" : (correct == guess.actors.length && correct == film.actors.length ? "correct" : "partial")));
            correct = 0;
            var genres = create('td', filmRow, '', ["class-genres"]);
            var genreList = create('ul', genres, '');
            guess.genres.forEach(genre => {
                if (film.genres.find(e => e.genreName == genre.genreName)) {
                    correct++;
                }
                create('li', genreList, genre.genreName);
            });
            genres.setAttribute('class', (correct == 0 ? "incorrect" : (correct == guess.genres.length && correct == film.genres.length ? "correct" : "partial")));
            create('td', filmRow, guess.location, [(guess.location == film.location ? "class-correct" : (film.location.includes(guess.location.split(" ")[0]) ? "class-partial" : "class-incorrect"))]);
            create('td', filmRow, (guess.score == null) ? '-' : guess.score, [(guess.score == film.score ? "class-correct" : (film.score == null ? "class-incorrect" : (guess.score == null ? "class-incorrect" : (guess.score > film.score ? "class-higher" : "class-lower"))))]);
            tries++;
            if (guess == film) {
                bigBox.innerHTML = "";
                if (tries > 1) {
                    create('h3', bigBox, 'Correct ! You guessed the film in ' + tries + ' tries !');
                } else {
                    create('h3', bigBox, 'Correct ! You guessed the film first try ! (Skill issue)');
                }
            }
        }

    });
</script>