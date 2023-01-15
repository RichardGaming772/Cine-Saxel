<form class='search' method="post" action=".?page=searchresults">
    <div>
        <label>Titre *</label>
        <input type="text" name="title">
    </div>
    <div>
        <label>Année</label>
        <input type="number" name="year">
    </div>
    <div>
        <label>Type : </label>
        <select name="type">
            <option value="">Tous</option>
            <option value="&title_type=feature" selected>Films</option>
            <option value="&title_type=tv_series">Séries</option>
        </select>
    </div>
    <?php if (isset($_SESSION["user"]) && $_SESSION["user"] == "Killian") {
        echo "<div><label>Force ID IMDB </label><input type=\"text\" name=\"forceID\" autocomplete=\"off\"></div>";
    } ?>
    <div>
        <input type="submit" name="action" value="Rechercher">
    </div>
</form>