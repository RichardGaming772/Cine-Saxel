<div class='box'>
    <form class='connect' method="post" action="./?page=home">
        <h3>Utilisateurs :</h3>
        <?php
        foreach (json_decode($_SESSION["users"]) as $user) {
            echo "<div class=\"userButton\">\n<input type=\"submit\" name=\"connect\" value=\"" . $user->username . "\"></div>";
        }
        if (isset($_SESSION["user"]))
            echo "<div class=\"userButton\">\n<input type=\"submit\" name=\"action\" value=\"Déconnection\"></div>";
        ?>
        <div>
            <label>Nouvel Utilisateur:</label>
            <input type="text" name="username" id="newUserBox">
            <input type="submit" id="createButton" name="new-user" value="Créer">
        </div>
    </form>
</div>
<script>
    document.getElementById('newUserBox').addEventListener("keydown", function(event) {
        if (event.code === 'Enter') {
            event.preventDefault();
            document.getElementById("createButton").click();
        }
    });
</script>