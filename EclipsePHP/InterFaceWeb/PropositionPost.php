
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

// Insertion du message à l'aide d'une requête préparée
$req = $bdd->prepare('INSERT INTO ideesite (Nom, Idee) VALUES(?, ?)');
$req->execute(array($_POST['Nom'], $_POST['Idee']));

// Redirection du visiteur vers la page du minichat
header('Location: Proposition.php');
?>

