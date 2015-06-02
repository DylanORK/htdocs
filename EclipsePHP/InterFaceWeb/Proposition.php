<?php include ('Fonctions.php'); ?>
<?php include ('Protected/Constantes.php'); ?>
<?php require("Style/Style-css.php"); ?>
<form method="post" action="PropositionPost.php">
    <fieldset>
    <legend>Boite a idée</legend>
    <p>
    Votre Nom : 
    <input type="text" name="Nom" /><br/>
    Votre Idée : 
    <input type="text" name="Idee" />
    </p>
    </fieldset>
    <p><input type="submit" value="Proposer" /></p>
</form>

<?php
// Connexion à la base de données
try
{
	$bdd = new PDO('mysql:host=localhost;dbname=interfaceweb;charset=utf8', 'root', 'TheEvilBrooD666');
}
catch(Exception $e)
{
        die('Erreur : '.$e->getMessage());
}

// Récupération des 100 derniers messages
$reponse = $bdd->query('SELECT  Nom, Idee FROM ideesite ORDER BY Nom ');

// Affichage de chaque message (toutes les données sont protégées par htmlspecialchars)
while ($donnees = $reponse->fetch())
{
	echo '<p><strong>' . htmlspecialchars($donnees['Nom']) . '</strong> : ' . htmlspecialchars($donnees['Idee']) . '</p>';
}

$reponse->closeCursor();

?>

<?php include ('Pied_De_Page.php'); ?>