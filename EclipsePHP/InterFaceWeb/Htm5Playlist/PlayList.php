<?php

/**
 * Cette class  sert à placer une playlist ou plusieurs playlists sur une page web.
 * Cette playlist fonctionne grace au html5, on place un lecteur avec les balises audio
 * que l'on rafraichit avec de l'ajax. On peut générer cette playliste de deux
 * façons differentes soit en plaçant vos musiques dans le dossier "musique" du dossier
 * "Htm5Playlist", soit en renseignants deux Array, un contenant les liens vers vos fichiers
 * un autre les titre de vos chansons. */
session_start();
include 'vu/Player.php';
include 'vu/List_play.php';
include 'traitement/DossierList.php';

class PlayList {

    private $path;

    /**
     * 
     * @param type $path
     *     Le chemin vers la racine du dossier "Htm5Playlist/"
     */
    public function __construct($path) {
        $this->path = $path;
    }

    /**
     * on créent une playListe avec les musiques contenus dans le dossier "musique/" 
     * du dossier "Htm5Playlist/".
     * @param type $id
     *     Un id html pour votre playlist
     * @return string
     *     La playlist html5
     */
    public function getDossierMusique($id) {
        $_SESSION[$id . '_player_chemin'] = -1;
        $_SESSION[$id . '_player_titre'] = -1;
        $player = new Player($this->path, 0, false, $id . "_player", false, -1, -1);
        $list = new List_play($id . "_player", $this->path, -1, -1);
        $player = $player->getElmnt();
        $list = $list->getElmnt();
        $str = "";
        $str .= "<div style=\"width:100%;overflow:hidden;\" class=\"player_cls\" id=\"$id\">";
        $str .= "<div style=\"\" class=\"player\" id=\"" . $id . "_player\">";
        $str .= $player;
        $str .= "</div>";
        $str .= "<div style=\"padding:1%;\" class=\"list\" id=\"" . $id . "_list\">";
        $str .= $list;
        $str .= "</div>";
        $str .= "</div>";
        return $str;
    }

    /**
 * on créent une playListe avec les musiques et les titres contenus dans les Arrays renseignés.
     * @param type $id
     *     Un id html pour votre playlist
     * @param type $chemin
     *     Un array contenant les chemins vers les fichiers musicaux.
     * @param type $titre
     *     Un array contenant les titres de vos fichiers musicaux.
     * @return string
     */
    public function getArray($id, $chemin, $titre) {
        $_SESSION[$id . '_player_chemin'] = $chemin;
        $_SESSION[$id . '_player_titre'] = $titre;
        $player = new Player($this->path, 0, false, $id . "_player", false, $chemin, $titre);
        $list = new List_play($id . "_player", $this->path, $chemin, $titre);
        $player = $player->getElmnt();
        $list = $list->getElmnt();
        $str = "";
        $str .= "<div style=\"width:100%;overflow:hidden;\" class=\"player_cls\" id=\"$id\">";
        $str .= "<div style=\"\" class=\"player\" id=\"" . $id . "_player\">";
        $str .= $player;
        $str .= "</div>";
        $str .= "<div style=\"max-height:175px;overflow:auto;padding:1%;\" class=\"list\" id=\"" . $id . "_list\">";
        $str .= $list;
        $str .= "</div>";
        $str .= "</div>";
        return $str;
    }

}
