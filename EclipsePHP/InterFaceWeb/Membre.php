<?php
/*
 * Le test de l'existence du compte de notre membre ayant déjà été fait dans la page Connexion.php, 
 * il nous reste peu de chose à faire pour notre espace membre.

En effet, dans Membre.php, il ne nous reste qu'à tester que la personne qui accède a cette page est bien passée
 par le formulaire de connexion de l'espace membre.

Pour ce faire, nous testons si la variable de session $_SESSION['login'] est bien définie :

    si elle ne l'est pas, nous redirigeons le visiteur vers le formulaire de connexion de l'espace membre
    sinon, on affiche le contenu de notre espace membre (dans cet ébauche, 
    remarquer que nous n'affichons pas grand-chose ^^ 
    à part un lien permettant de se déconnecter de l'espace membre)
 */

session_start();
if (!isset($_SESSION['login'])) {
	header ('Location: Connexion.php');
	exit();
}
?>

<html>
<head>
<title>Espace membre</title>
</head>

<body>
Bienvenue <?php echo htmlentities(trim($_SESSION['login'])); ?> !<br />
<a href="Deconnexion.php">Déconnexion</a>

<?php include ("Pied_De_Page.php");?>
</body>
</html>