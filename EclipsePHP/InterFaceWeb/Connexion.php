<?php 
require ("Style/Style-css.php");
require ("Protected/Constantes.php"); 

/*Cette page contient le formulaire de connexion à l'espace membre.
Ce formulaire est composé de deux champs :

    login, où l'utilisateur saisira son login
    pass, où l'utilisateur saisira son mot de passe



Une fois le formulaire soumis, nous testons si ces champs sont bien remplis, 
le cas échéant, 
nous affichons un petit message sur la page informant l'utilisateur qu'il a oublié de remplir un champ.

Dans le cas où le formulaire a été rempli correctement, 
nous allons interroger la base de données afin de vérifier que le couple login / mot de passe saisi 
par l'utilisateur existe bien dans notre base de données 
(ce qui veut dire que nous allons vérifier que le couple login / mot de passe correspond à un vrai membre).
*/
// on teste si le visiteur a soumis le formulaire de connexion
if (isset($_POST['connexion']) && $_POST['connexion'] == 'Connexion') {
	if ((isset($_POST['login']) && !empty($_POST['login'])) && (isset($_POST['pass']) && !empty($_POST['pass']))) {

	$base = mysql_connect (HOST, USER, PASSWORD);
	mysql_select_db (BASE, $base);

	// on teste si une entrée de la base contient ce couple login / pass
	$sql = 'SELECT count(*) FROM membre WHERE login="'.mysql_escape_string($_POST['login']).'" AND pass_md5="'.mysql_escape_string(md5($_POST['pass'])).'"';
	$req = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
	$data = mysql_fetch_array($req);

	mysql_free_result($req);
	mysql_close();

	// si on obtient une réponse, alors l'utilisateur est un membre
	if ($data[0] == 1) {
		session_start();
		$_SESSION['login'] = $_POST['login'];
		header('Location: Membre.php');
		exit();
	}
	// si on ne trouve aucune réponse, le visiteur s'est trompé soit dans son login, soit dans son mot de passe
	elseif ($data[0] == 0) {
		$erreur = 'Compte non reconnu.';
	}
	// sinon, alors la, il y a un gros problème :)
	else {
		$erreur = 'Probème dans la base de données : plusieurs membres ont les mêmes identifiants de connexion.';
	}
	}
	else {
	$erreur = 'Au moins un des champs est vide.';
	}
}
?>
<html>
<head>
<title>Se connecter</title>
</head>

<body>
Connexion à l'espace membre :<br />
<form action="Connexion.php" method="post">
Login : <input type="text" name="login" value="<?php if (isset($_POST['login'])) echo htmlentities(trim($_POST['login'])); ?>"><br />
Mot de passe : <input type="password" name="pass" value="<?php if (isset($_POST['pass'])) echo htmlentities(trim($_POST['pass'])); ?>"><br />
<input type="submit" name="connexion" value="Connexion">
</form>
<a href="Inscription.php">Vous inscrire</a>
<?php
if (isset($erreur)) echo '<br /><br />',$erreur;
?>
</body>
</html>