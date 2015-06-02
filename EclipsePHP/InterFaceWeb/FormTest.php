<?php include("Fonctions.php"); ?>

<html>
    <head><title>Formulaire de saisie utilisateur </title></head>
    <body>
        <h1>Inscrivez-vous !</h1>
        <h2>Entrez les données demandées :</h2>
        <form name="inscription" method="post" action="FormTest.php">
            Entrez votre pseudo : <input type="text" name="pseudo"/> <br/>
            Garçon ou fille ? 	<input type="radio" name="sexe" value="G"/>Garçon<input type="radio" name="sexe" value="F"/>Fille<br/>
            Entrez votre age : <input type="text" name="age"/><br/>
            <input type="submit" name="valider" value="OK"/>
        </form>
    </body>
</html>

