<?php
include("Entete.php");
/**
 * Ce code php sert à placer une playliste ou plusieurs playlist sur une page web.
 * Cette playlist fonctionne grace au html5, on place un lecteur avec les balises audio
 * que l'on rafraichit avec de l'ajax. On peut générer cette playliste de deux
 * façons differentes soit en plaçant vos musiques dans le dossier "musique" du dossier
 * "Htm5Playlist", soit en renseignant deux Array, un contenant les liens vers vos fichiers
 * un autre les titre de vos chansons.
 * 
 * Dans l'exemple qui va suivre ce fichier ce trouve dans le même dossier que le 
 * doosier "Htm5Playlist";
 */

//on inclut l'objet contenant la playliste, prenez garde à renseigner le chemin 
//exacte vers ce fichier
include 'Htm5Playlist/PlayList.php';

?>



<!DOCTYPE html>
<html>
    <head>
        <title>htm5Playlist</title>
        <meta charset="utf-8"/>
        <!-- On inclut un fichier javascript , prenez garde à renseigner le chemin 
        exacte vers ce fichier -->
        <script type="text/javascript" language="Javascript" src="Htm5Playlist/traitement/fonction.js"></script>
        <style>
            .player_cls{
                margin: auto;
                border-radius: 15px;
                background: rgb(90%,90%,90%);
                border: outset 2px gray;
                box-shadow: 3px 3px 10px black;
                width: 30%!important;
            }
        </style>
    </head>
    <body>
        
        <?php
        /* On déclare l'objet Playlist($path) avec comme paramètre le chemin
         * vers la racine du dossier Htm5Playlist/, prenez garde à renseigner le chemin 
          exact */
        $playList = new PlayList("Htm5Playlist/");

        //*******************************
        // varsion 1: depuis le dossier musique!
        //*******************************
        //on place simplement la playliste généré par le dossier "musique" grâce à 
        //la fonction getDossierMusique($id); , dans une 
        //variable tampon, on place simplement l'id de votre choix en paramètre.
        $tamp = $playList->getDossierMusique("playListDossier");
        //on écrit la playlist.
        echo $tamp;

        //*******************************
        // varsion 2: par des Array!
        //*******************************
        //on renseigne deux Arrays, un pour les chemins vers les musiques à lire, 
        //un deuxième pour les titres.
        /*$chemin[0] = "http://dariumis.fr/lesMp3/1412862486_4166.mp3";
        $titre[0] = "titre 1";
        $chemin[1] = "http://dariumis.fr/lesMp3/1411336191_6685.mp3";
        $titre[1] = "titre 2";
        //on place simplement la playliste généré par les Arrays grâce à 
        //la fonction getArray($id, $chemin, $titre) , dans une 
        //variable tampon. Les paramètres de cette fonction sont dans l'ordre:
        //---- l'id de votre playlist
        //---- l'Array contenant les chemins
        //---- l'Array contenant les titres
        $tamp=$playList->getArray("depuisArray", $chemin, $titre);
        //on écrit la playlist.
        echo $tamp;
        
        */
        
include ("Pied_De_Page.php");
        ?>

</body>
</html>
