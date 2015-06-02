<?php

 /*
ACCES A UNE BASE DE DONNEE
if(isset($_POST['Nom']))
{
	$bdd = mysql_connect ('localhost:81', 'root', 'TheEvilBrooD666');
	mysql_select_db ('interfaceweb', $bdd) ;
	
	$sql = 'INSERT INTO ideesite (nom,idee) VALUES '.$_POST['Nom'].', '.$_POST['Idee'].'';
	mysql_query($sql);
}


if($id = mysql_connect(HOST,USER,PASSWORD))//Si j'arrive à me connecter avec ses paramêtres
{ if($id_db = mysql_select_db('interfaceweb'))//Puis à cette base de données
 { //echo "Acces a la base de donnée : Succès !";//Ça roule !
 $sql = 'INSERT INTO ideesite (nom,idee) VALUES '.$_POST['Nom'].', '.$_POST['Idee'].'';
 mysql_query($sql);
 $sql->query('SELECT * FROM ideesite');
 }else{
 die("Echec Ou impossible de se connecter à la base :( ");
 }
mysql_close($id);
}else{
die("vous n'êtes même pas arriver à vous connecter !");
}



FORMULAIRE DE CONNEXION
<head>
        <title>Se connecter : Portail de Dylan</title>
        <link rel="icon" type="image/png" href="favicon.png" />
        <meta charset="utf-8" />
        </head>
        
        
<form method="post" action="ConnexionPost.php">
    <fieldset>
    <legend>Connexion</legend>
    <p>
    <label for="pseudo">Pseudo :</label><input name="pseudo" type="text" id="pseudo" /><br />
    <label for="password">Mot de Passe :</label><input type="password" name="password" id="password" />
    </p>
    </fieldset>
    <p><input type="submit" value="Connexion" /></p>
</form>
  
<a href="Inscription.php">Pas encore inscrit ?</a>
 
*
*
*<li class="couleur{rand(0,15)}"
*
*
*
*
*
*
*
*
*
*
*
*
*
*/



?>