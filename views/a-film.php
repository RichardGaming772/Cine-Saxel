<?php
if (!Film::inDB($_GET["page"])){
    echo "<h4 class='autres'>Aucun film avec cet ID n'est présent dans la base de donnés.</h4>";
    return false;
}
$film = Film::load($_GET["page"],"s");
$film->FilmPage();
?>
<form class='filmMod' method="post" action=".?page=view" autocomplete="off">
<input type="hidden" name="filmid" value="<?php echo $_GET["page"]?>">
<div id="modButton">
    <label>Localisation : </label>
    <?php
    echo "<input list='locations' name='Location' value='". $film->location ."' class='locationinput'>";
    echo "<datalist id='locations'>";
    foreach(Film::getLocations() as $loc)
    {
        echo "<option value=\"".$loc."\">";
    }
    echo "</datalist>";
    ?>
    <label>Commentaire : </label>
    <input type="text" name="Commentaire" value="<?php echo $film->comment;?>">
    <label>Titre Français : </label>

    <input type="text" name="TitreFR" value="<?php echo $film->localtitle;?>">
    <?php
    if(isset($_SESSION["user"]))
    {
        $userlist = array_filter($film->seenby, function($obj){
            if ($obj->username == $_SESSION["user"]) {
                return true;
            }
            return false;
        });
        $user = reset($userlist);
        echo "<label>Mon Score (0-5): </label>\n";
        echo "<input type=\"number\" name=\"Score\" value=\"".(empty($user)?"":(gettype($user->filmScore) != "integer"?"":$user->filmScore))."\" max=\"5\" min=\"0\">";
        echo "<label> Film vu : </label>\n";
        echo "<input type=\"checkbox\" name=\"Seen\" ".(empty($user)?"":"checked").">";
        echo "<input type=\"hidden\" name=\"user\" value=\"".$_SESSION["user"]."\">";
    }
    ?>
    <input type="submit" name="action" value="Modifier">
    <input type="submit" name="action" value="Enlever">
    <p>(Pour voir toute les options de localisation, vider la zone de text)</p>
</div>
</form>