<?php

/*
Ici, nous allons tuer la session en cours, 
et rediriger le visiteur vers le formulaire de connexion à l'espace membre.
Peu de chose à dire.
*/


session_start();
session_unset();
session_destroy();
header('Location: Connexion.php');
exit();
?>