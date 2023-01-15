<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="pop-corn.png" />
    <link rel="stylesheet" href="style.css">
    <!-- TODO: Remettre un management lightmode/darkmode -->
    <title>Ciné-Saxel</title>
</head>

<script>
    function create(tag, parent, text, attributes = null) {
        let element = document.createElement(tag)
        element.appendChild(document.createTextNode(text))
        parent.appendChild(element)
        if (attributes) {
            attributes.forEach(attribute => {
                if (attribute.split('-')[0] == 'src' || attribute.split('-')[0] == 'action') {
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
</script>

<body>
    <header>
        <h1><img src="pop-corn.png" alt="pop corn logo" width="30px"> Ciné Saxel</h1>
        <?php
        if (isset($_SESSION["user"]))
            echo "<h4 id='logInfo'>Utilisateur : " . $_SESSION["user"] . "</h4>";
        else
            echo "<h4 id='logInfo'>Aucun utilisateur sélectionné</h4>"; ?>
    </header>
    <nav>
        <ul>
            <li class="menuNav"><a href=".?page=home">Accueil</a></li>
            <li class="menuNav"><a href=".?page=search">Ajouter un Film/une Série</a></li>
            <li class="menuNav"><a href=".?page=view">Voir les films</a></li>
            <li class="menuNav"><a href=".?page=connect">Connection</a></li>
            <li class="menuNav"><a href="./syndle">Syndle</a></li>
        </ul>
    </nav>

    <?php
    switch (explode('-', $_GET["page"])[0]) {
        case 'home':
            include_once "views/home.php";
            break;
        case 'view':
            include_once "views/filmlist.php";
            break;
        case 'search':
            include_once "views/search.php";
            break;
        case 'searchresults':
            echo include_once "views/search-results.php";
            break;
        case 'connect':
            include_once "views/connectPage.php";
            break;
        case (preg_match("#^(tt[0-9]*)$#m", $_GET["page"]) ? true : false):
            include_once "views/a-film.php";
            break;
        default:
            include_once "views/404.php";
            break;
    }
    ?>
</body>

</html>