<?php

/*
 * Dans cette page, nous allons placer le formulaire d'inscription à notre espace membre.

Ce formulaire sera composé de 3 champs :

    un champ login contenant le login que l'utilisateur voudra utiliser pour se connecter à l'espace membre
    un champ pass contenant son mot de passe 
    (que nous devrons hacher via la fonction md5 de PHP avant de le stocker dans la base de données)
    un champ pass_confirm demandant à l'utilisateur de retaper son mot de passe 
    (ceci, afin d'être bien sur que l'utilisateur tape bien le mot de passe qu'il souhaite taper ^^)



Une fois le formulaire soumis, il nous reste à vérifier diverses choses avant d'enregistrer ce nouveau membre.

Déjà, nous testons si le formulaire est bien rempli.
Si il ne l'est pas, nous affichons un petit message en dessous du formulaire.
Si il l'est, nous passons au prochain test, testant si les deux mots de passes saisis par le visiteur sont identiques.


Ensuite, nous nous connectons à notre base de données, 
et nous vérifions si le login saisi par l'utilisateur n'existe pas déjà dans notre table membre.
Si il ne l'est pas, 
nous validons l'inscription tout en prenant soin de stocker le mot de passe haché du nouveau membre dans notre table, 
puis nous redirigeons notre visiteur vers notre espace membre.
 */
include ("Style/Style-css.php");
include ("Protected/Constantes.php");
// on teste si le visiteur a soumis le formulaire
if (isset($_POST['inscription']) && $_POST['inscription'] == 'Inscription') {
	// on teste l'existence de nos variables. On teste également si elles ne sont pas vides
	if ((isset($_POST['login']) && !empty($_POST['login'])) && (isset($_POST['pass']) && !empty($_POST['pass'])) && (isset($_POST['pass_confirm']) && !empty($_POST['pass_confirm']))) {
	// on teste les deux mots de passe
	if ($_POST['pass'] != $_POST['pass_confirm']) {
		$erreur = 'Les 2 mots de passe sont différents.';
	}
	else {
		$base = mysql_connect (HOST, USER, PASSWORD);
		/*$base = mysql_connect ('serveur', 'login', 'password');*/
		mysql_select_db (BASE, $base);

		// on recherche si ce login est déjà utilisé par un autre membre
		$sql = 'SELECT count(*) FROM membre WHERE login="'.mysql_escape_string($_POST['login']).'"';
		$req = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
		$data = mysql_fetch_array($req);

		if ($data[0] == 0) {
		$sql = 'INSERT INTO membre VALUES("", "'.mysql_escape_string($_POST['login']).'", "'.mysql_escape_string(md5($_POST['pass'])).'")';
		mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());

		session_start();
		$_SESSION['login'] = $_POST['login'];
		header('Location: membre.php');
		exit();
		}
		else {
		$erreur = 'Un membre possède déjà ce login.';
		}
	}
	}
	else {
	$erreur = 'Au moins un des champs est vide.';
	}
}
?>
<html>
<head>
<title>Inscription</title>
</head>

<body>
Inscription à l'espace membre :<br />
<form action="Inscription.php" method="post">
Login : <input type="text" name="login" value="<?php if (isset($_POST['login'])) echo htmlentities(trim($_POST['login'])); ?>"><br />
Mot de passe : <input type="password" name="pass" value="<?php if (isset($_POST['pass'])) echo htmlentities(trim($_POST['pass'])); ?>"><br />
Confirmation du mot de passe : <input type="password" name="pass_confirm" value="<?php if (isset($_POST['pass_confirm'])) echo htmlentities(trim($_POST['pass_confirm'])); ?>"><br />
<input type="submit" name="inscription" value="Inscription">
</form>
<?php
if (isset($erreur)) echo '<br />',$erreur;
?>
</body>
</html>