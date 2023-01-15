<?php
if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case 'Ajouter':
            Film::addFilm($_POST["film"]);
            $_SESSION["films"] = json_encode(Film::all());
            break;
        case 'Rechercher':
            $search = ApiManager::findIMDBID($_POST["title"], $_POST["year"], $_POST["type"]);
            $imdbID = $search[0];
            $others = $search[1];
            $_SESSION["others"] = $others;
            if (empty($imdbID)) {
                header('Location: ./?page=searchresults-noresult');
            } else {
                header('Location: ./?page=searchresults-' . $imdbID);
            }
            break;
        case 'Déconnection':
            unset($_SESSION["user"]);
            break;
        case 'Enlever':
            Film::removeFilm($_POST["filmid"]);
            $_SESSION["films"] = json_encode(Film::all());
            break;
        case 'Modifier':
            $film = Film::load($_POST["filmid"], "s");

            $film->location = $_POST["Location"];
            $film->comment = $_POST["Commentaire"];
            $film->localtitle = $_POST["TitreFR"];
            if (!empty($_POST["user"])) {
                if (!empty($_POST["Seen"])) {
                    User::SetUserSeen($_POST["filmid"], $_POST["user"], true);
                } else {
                    User::SetUserSeen($_POST["filmid"], $_POST["user"], false);
                }
                if (!empty($_POST["Score"])) {
                    User::SetUserScore($_POST["filmid"], $_POST["user"], $_POST["Score"]);
                }
            }
            $_SESSION["films"] = json_encode(Film::all());
            $_SESSION["users"] = json_encode(User::all());
            break;
        default:
            break;
    }
}
if (isset($_POST["connect"])) {
    $_SESSION["user"] = $_POST["connect"];
}
if (isset($_POST["new-user"])) {
    if ($_POST["new-user"] == "Créer") {
        if (!in_array($_POST["username"], User::AllUsernames()) && $_POST["username"] != "") {
            User::CreateUser($_POST["username"]);
            $_SESSION["users"] = json_encode(User::all());
            $_SESSION["user"] = $_POST["username"];
        }
    }
}
