    <div class="box">
    <h2>Stats :</h2>
    <div class="stats">
        <?php
        include_once "models/User.php";
        foreach(User::all() as $user){
            $user->StatBlock();
        }
        ?>
    </div>

    <h2>Utilisation :</h2>

    <p>Pour ajouter un film à la base de données allez dans l'onglet "<a class="linkInText" href=".?page=search">Ajouter un Film/une Série</a>" puis entrez le nom du film ou de la série que vous souhaitez ajouter (la date est optionnelle).</p>
    <p>Si le film ou la série que vous cherchez n'apparait pas et n'est pas dans la liste des autres résultats vous pouvez entrer la date de sortie pour rendre la recherche plus précise.</p>
    <p>Quand le site affiche le film ou la série désiré(e) cliquez sur le bouton "Ajouter" et ajouter les informations additionnelles (ces informations sont optionnelles mais renseigner la localisation est fortement recommandé). Si vous êtes connectés vous pouvez cocher si vous avez vu le film et entrer un score (de 0 à 5).</p>
    <p>Pour se connecter allez dans l'onglet "<a class="linkInText" href=".?page=connect">Connection</a>" et cliquez sur le bouton avec votre pseudo. Si vous n'avez pas encore de pseudo vous pouvez le créer en fournissant le pseudo désiré dans la zone fournie.</p>
    <p>Pour visionner les films et séries de la base de données allez dans l'onglet "<a class="linkInText" href=".?page=view">Voir les films</a>". Depuis cet onglet vous pouvez feuilleter à travers les films et séries en les triant selon votre choix. On peut aussi effectuer une recherche dans les résultats affichés, par exemple sur les titres, les directeurs, les acteurs, les genres, la localisation et les commentaires. Cette recherche compare avec écriture fixe (mais sans importance sur les majuscules et minuscules). Exemple: rechercher "VINCI" donne comme résultat: "The Da Vinci Code" (parmi d'autres résultats possibles), mais rechercher "vincy" ne donne pas ce résultat. Pour une recherche plus tolérante sur le titre du film on peut utiliser la recherche de l'onglet "<a class="linkInText" href=".?page=search">Ajouter un Film/une Série</a>" pour utiliser les filtres de recherche de IMDB, si le film que IMDB propose est dans la base de données vous pourrez être redirigés vers la page de ce film.</p>
    <p>On peut aussi sélectionner uniquement les films d'un certain genre/acteur/directeur en cliquant dessus depuis la liste ou la page d'un film.</p>
    <p>Pour épargner les yeux le soir, un mode "sombre" du site est disponible en cliquant sur le bouton avec la lune en haut à droite de cette page. Pour l'éteindre il suffit de rappuyer sur le même bouton (qui montre maintenant un soleil).</p>
    <p>N'oubliez pas de me notifier des potentiels bugs ou disfonctionnements que vous rencontrez.</p>
    </div>