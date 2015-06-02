<?php
/*

    Copyright 2001, 2002, 2003, 2004 Jared Watkins <jared at @ watkins dot net>
    All rights reserved, All Responsibility Yours
    This program is distributed under the terms of the GNU General Public License

    Agatha is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

$LANG["error_no_config"] = "No Config File Found<br>.
                            Copy config.php.dist to config.php and put in your path and language choices";

if(file_exists("config.php")) require("config.php");
  else {
     error($LANG["error_no_config"]);
     exit;
}

/* Check Input For Bad Stuff ******************************************/

#    require_once("paranoia.inc.php");
#    if (isset($_REQUEST)&&!empty($_REQUEST)){
#        $par=new paranoia($_REQUEST);
#        if (!empty($par->wrongParams)) {
#            error("There Were Invalid Characters In Your Request");
#        }
#    }

/* Build BASE_URL ******************************************************/

    if (!isset($_REQUEST["play"]) || !isset($_REQUEST["selected"])) {
        $BASE_URL = $CONFIG["request_method"].$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"];
    } else {
        $BASE_URL = "http://zuricon.com/".$_SERVER["SCRIPT_NAME"];

    }


/* Deal With Cookies ***************************************************/


    if (isset($_GET["mycookie"])) {
        header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
        setcookie("uniqueid", $_GET["mycookie"], time()+157680000); // Plus 5 Years
        $uniqueid = $_GET["mycookie"];
        echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2;URL=$BASE_URL\">";
        echo $LANG["cookie_is_set"];
        exit;
    }

    if (isset($_GET["uniqueid"]) && isset($_GET["nextsong"])) {
        if (DEBUG) error_log("Inbound playlist req :$uniqueid: ".$_SERVER["HTTP_USER_AGENT"]);
    } elseif (!isset($_COOKIE["uniqueid"])) {
        header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
        $token = md5(uniqid(time()));
        $better_token = md5(uniqid(rand(),1));
        setcookie("uniqueid", $better_token, time()+157680000);
        $uniqueid = $better_token;
        if(DEBUG) error_log("Setting cookie :$uniqueid: ".$_SERVER["HTTP_USER_AGENT"]);
    } else {
        $uniqueid = $_COOKIE["uniqueid"];
        if(DEBUG) error_log("Using cookie :$uniqueid: ".$_SERVER["HTTP_USER_AGENT"]);
    }


/* Initialize Vars *****************************************************/

    $LANG = array();
    $dir_list = array();
    $file_list = array();
    $playlist_list = array();
    $sharedplaylist_list = array();
    $rdirs = array();
    $listing = array(); // master list of current dirs and files

    require_once('id3.class.php');

    // Language selection
    if (isset($CONFIG["option_language"])) {
        include("lang/".$CONFIG["option_language"]);
    }   else include("lang/agatha-us-en.php");


/* Process Incoming GET/POST Requests *******************************************/


    if (!isset($_REQUEST["pdir"])) {      # If you are hitting the first page there is no parent dir
        $pdir = "/";                      # So set it to /
        $pdir_64 = $pdir;

    } else if ($_REQUEST["pdir"] != "/") {
        $pdir_64 = $_REQUEST["pdir"];
        $pdir = stripslashes(base64_decode($_REQUEST["pdir"]));   # Convert from base64 if you do have one..
    }


    if (isset($_GET["cdir"])) {                  # If changing directories LEAVE AS GET!
        #if (is_numeric($_REQUEST["cdir"])) {
        #    read_dir($pdir);
        #    $pdir = $pdir.$dir_list[$_GET["cdir"]];
        #    if(DEBUG) error_log("Changing directorys $_GET[cdir]",0);
        #
        #    unset($file_list); unset($dir_list);
        #    $dir_list=array(); $file_list=array();
        #
        #    read_dir($pdir); # If there are no files and only 1 dir.. change into it without asking
        #
        #    if ((count($file_list) == 0) && (count($dir_list) == 1)) {
        #         $pdir = "$pdir$dir_list[0]";
        #    }
        #    unset($file_list); unset($dir_list);
        #    $dir_list=array(); $file_list=array();
        #} else {
            $cdir = base64_decode($_REQUEST["cdir"]);

            $mydir = new mydir(BASE_DIR.$pdir.$cdir);
            $listing = $mydir->getentries();

            if (($listing["files"]["count"] == 0) && ($listing["directories"]["count"] == 1)) {
                $pdir = $pdir.$cdir.$listing["directories"][0];
            } else $pdir = $pdir.$cdir;
            if (DEBUG) error_log("pdir :$pdir: cdir :$cdir:", 0);
        //}
    }



/*****************************************************************************
   If you want to embed agatha in another page you have to do two things.    *
   First.. on the first line of the page you need to put this in your file:  *
   <? include("agatha.php"); ?>                                              *
                                                                             *
   Second.. where you want agatha to appear in the page (remember the output *
   can be quite long) you simply add this:                                   *
   <? activate_agatha(); ?>                                                  *
                                                                             *
   Finally.. you need to set the config item for 'embeded' to equal 1        *
                                                                             *
   It's that simple...                                                       *
                                                                             *
   Klingon function calls do not have 'parameters' - they have 'arguments'   *
     - and they ALWAYS WIN THEM.                                             *
                                                                             *
******************************************************************************/

#if($CONFIG["embeded"] != 1) activate_agatha();


/**************************************************************************************/

#function activate_agatha() {
#global $CONFIG;

#convert_playlists();

if(isset($_GET["play"])) play_single();
elseif(isset($_REQUEST["playrecursive"])) playrecursive($pdir, $_REQUEST["playrecursive"]);
elseif(isset($_GET["stream"])) stream();
elseif(isset($_GET["pl"])) play_playlist();
elseif(isset($_POST["pladd"])) playlist_add();
elseif(isset($_REQUEST["pledit"])) playlist_edit();
elseif(isset($_REQUEST["selected"])) play_multiple($_REQUEST["selected"]);
else display_listing();

#}

/**************************************************************************************/



function playrecursive($pdir, $targetdir) {
    global $CONFIG, $rdirs, $listing;
    if (DEBUG) error_log("playrecursive= Enter Function", 0);

    $targetdir = base64_decode($targetdir);

    // Writes back to the global rdirs variable
    recurse(BASE_DIR . $pdir . $targetdir);
    play_multiple($rdirs);
}

// Pass in the starting directory.. will update the global array called rdirs
function recurse($file_dir) {
        global $rdirs;

        if ($handle = @opendir($file_dir))
        {
                $i=0;
                while (false !== ($file = @readdir($handle)))
                {
                        if ($file != "." && $file != "..")
                        {
                                $list[$i] = $file;
                                $i++;
                        }
                }
                $dir_length = count($list);
                #echo "<ul>";
                for($i=0;$i<$dir_length;$i++)
                {
                        if(strrpos($list[$i], ".") == FALSE)
                        {
                                #echo "<li>".$list[$i]."/</li>\n";
                                recurse($file_dir."/".$list[$i]);
                        }
                        else
                        {
                                #echo "<li><a href=\"".$file_dir."/".$list[$i]."\">".$list[$i]."</a></li>\n";
                                $tmp = str_replace('//', '/', $file_dir."/".$list[$i]);
                                $tmp2 = base64_encode(str_replace(BASE_DIR, '', $tmp));
                                array_push($rdirs, $tmp2);

                        }
                }
                #echo "</ul>";
                closedir($handle);
        }

}


/*
function convert_playlists() {
  global $CONFIG, $LANG, $uniqueid, $DEBUG;

  $c=0;
  $d = opendir(PLAYLIST_DIR);
    while($raw_list[$c]=readdir($d)) {
      if($raw_list[$c] == "." || $raw_list[$c]==".." || $raw_list[$c]=="index.php") continue;
      if($_SERVER["REMOTE_ADDR"] == substr($raw_list[$c], 0, -2)) {
        copy(PLAYLIST_DIR.$raw_list[$c], PLAYLIST_DIR.$raw_list[$c].".bak");
        $pl_number = substr($raw_list[$c], -1);
        if(DEBUG) error_log("convert_playlists= uniqueid :$uniqueid:",0);
        rename(PLAYLIST_DIR.$raw_list[$c], PLAYLIST_DIR."$uniqueid.$pl_number");
        chmod(PLAYLIST_DIR."$uniqueid.$pl_number", 0600);
        chmod(PLAYLIST_DIR.$raw_list[$c].".bak", 0600);
      }
    }
}
*/

/**************************************************************************************/


function draw_dirnav_bar() {
   global $BASE_URL, $pdir_64, $pdir, $LANG, $CONFIG;

   $path = split("/", $pdir);
   echo "<DIV id=layer_dirnav class=dirnav><A href=\"$BASE_URL\">(".$LANG["top"].")</A> / ";
   if (count($path) > 2) {
       $parent = null;
       $max = count($path)-1;

       for ($i=1; $i < count($path)-1; $i++) {

            #if ($i < $max) $parent .= $path[$i-1]."/";
            #$parent_enc = base64_encode($parent);
            #$dir_enc = base64_encode($path[$i]);
            #echo "<A href=\"$BASE_URL?cdir=$dir_enc&pdir=$parent_enc\">$path[$i]</a> / ";
            echo "$path[$i] / ";
       }
   }

   echo "<br><br><A href=\"$BASE_URL?mycookie=".$_COOKIE["uniqueid"]."\"><font size=-2>".$LANG["copy_my_cookie"]."</font></A> <img title=\"".$LANG["help_cookie"]."\" align=middle border=0 src=images/agatha_help_ico.png><br>\n";
   echo "</DIV>\n";
}


/**************************************************************************************/


function display_listing() {

  global $CONFIG, $BASE_URL, $LANG, $pdir, $file_list, $dir_list, $playlist_list;
  $pdir_64 = base64_encode($pdir);
  read_dir($pdir);
  read_playlists(PLAYLIST_DIR, $playlist_list);
  $sharedplaylist_list = read_shared_playlists();

  $mydir = new mydir(BASE_DIR . $pdir . $cdir);
  $listing = $mydir->getentries();


  echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
  echo "<html>\n";
  echo "<head>\n";
  echo "<meta http-equiv=\"Content-Type\" content=\"text/html;\" charset=\"utf-8\">";
  echo "<SCRIPT Language=\"Javascript\">\n";
  echo "  function SelectMost() {\n";
  echo "    for(var i=0; i < document.forms.songs.elements.length; i++) {\n";
  echo "      if(document.forms.songs.elements[i].type == \"checkbox\"  && document.forms.songs.elements[i].name != document.forms.songs.pladd.name) {\n";
  echo "        document.forms.songs.elements[i].checked = true;\n";
  echo "      }\n    }\n  }\n</SCRIPT>\n";
  echo "<title>$pdir - Agatha</title>\n";
  echo "<link href=\"agatha.css\" rel=\"stylesheet\" type=\"text/css\" >\n";
  echo "</head><body>\n\n";


  draw_dirnav_bar();

  echo "<form name=\"songs\" id=\"songs\" method=post action=\"$BASE_URL\">\n";

  if ($listing["directories"]["count"] > 0) {

      echo "<DIV class=\"category\"><A href=\"javascript:history.go(-1)\"><-".$LANG["back"].") </A> ".$LANG["subdirectories"] .":</DIV>\n";
      echo "<DIV class=\"directory\">\n";
      for ($d=0; $d < $listing["directories"]["count"]; $d++) {
           $cdir_64 = base64_encode($listing["directories"][$d]);

           $dir_name_display = ereg_replace("_", " ", substr($listing["directories"][$d],0,-1));
           $change_dir_link = "<A href=\"$BASE_URL?cdir=$cdir_64&#38;pdir=$pdir_64\">$dir_name_display</A>";
           $recursive_dir_play_link = "<A href=\"$BASE_URL?playrecursive=$cdir_64&#38;pdir=$pdir_64\">\t<img border=\"0\" title=\"$LANG[play_recursive]\" src=\"images/dir.gif\"></A>";
           if ($CONFIG["option_local_play"]) $recursive_local_play_link = "<a target=\"localplay\" href=\"mp3daemon.php?pdir=$pdir_64&#38;action=playrecursive&#38;playaction=$cdir_64\"> <img title=\"". $LANG["play_on_server"] ."\" align=\"bottom\" border=\"0\" title=\"". $LANG["local"] ."\" height=\"13\" src=\"images/agatha-localplay.png\"></a>&nbsp;";
               else $recursive_local_play_link = null;

           echo "$recursive_dir_play_link $recursive_local_play_link $change_dir_link<br>\n";
      }
      echo "</DIV>\n\n";
  }

  if (count($sharedplaylist_list) > 0) {

      for ($r=0; $r < sizeof($sharedplaylist_list); $r++) {
           $listid = split("\.", $sharedplaylist_list[$r]);  // No need to list your own if they are shared
           if ($listid[0] == $_COOKIE["uniqueid"]) {
               continue;
           } else $print_header = true;
      }

      if ($print_header == true) {
          echo "<DIV class=category><A href=\"javascript:history.go(-1)\"><-".$LANG["back"].") </A> Shared ". $LANG["playlists"] .":</DIV>\n";
          echo "<DIV class=\"playlist\">\n";
      }

      for ($r=0; $r < sizeof($sharedplaylist_list); $r++) {
           $listid = split("\.", $sharedplaylist_list[$r]);  // No need to list your own if they are shared
           if ($listid[0] == $_COOKIE["uniqueid"]) continue;

           $fp = fopen(SHAREDPLAYLIST_DIR.$sharedplaylist_list[$r], "r");
           $list_name = fgets($fp, 1024);
           fclose ($fp);
           $playlist_entry = substr($sharedplaylist_list[$r], -1);
           $playlist64 = base64_encode($sharedplaylist_list[$r]);


           $play_shared_playlist_link = "<A href=\"$BASE_URL?pl=$r&#38;shared=1\">$list_name</A>";
           $play_shared_playlist_shuffle = "<A href=\"$BASE_URL?pl=$r&#38;shuffle=1&#38;shared=1\"><img title=\"". $LANG["shuffle_play"] ."\" border=0 src=\"images/burst.gif\"></A>";
           if ($CONFIG["option_local_play"]) $play_local_link = "<A href=\"mp3daemon.php?action=playlist&playaction=$playlist64\" target=\"localplay\"> <img title=\"". $LANG["play_on_server"] ."\" align=\"bottom\" border=\"0\" title=\"". $LANG["local"] ."\" height=\"13\" src=\"images/agatha-localplay.png\"></a>&nbsp;";
               else $play_local_link = null;

           echo "\t$play_shared_playlist_shuffle $play_local_link $play_shared_playlist_link<br>\n";
      }
      echo "</DIV>\n\n";

  }

  if (count($playlist_list) > 0) {

      echo "<DIV class=category><A href=\"javascript:history.go(-1)\"><-".$LANG["back"].") </A> ". $LANG["playlists"] .":</DIV>\n";
      echo "<DIV class=\"playlist\">\n";
      for ($r=0; $r < sizeof($playlist_list); $r++) {
           $fp = fopen(PLAYLIST_DIR."$playlist_list[$r]", "r");
           $list_name=trim(fgets($fp, 1024));
           fclose($fp);

           $playlist64 = base64_encode($playlist_list[$r]);
           $playlist_entry = substr($playlist_list[$r], -1);

          if (file_exists(SHAREDPLAYLIST_DIR.$playlist_list[$r])) {
              $play_playlist_link    = "<A href=\"$BASE_URL?pl=$r\">[$list_name]</A>";
          } else $play_playlist_link = "<A href=\"$BASE_URL?pl=$r\">$list_name</A>";

          $play_playlist_shuffle = "<A href=\"$BASE_URL?pl=$r&#38;shuffle=1\"><img title=\"". $LANG["shuffle_play"] ."\" border=0 src=\"images/burst.gif\"></A>";
          $edit_playlist_link    = "<A href=$BASE_URL?pledit=$playlist_entry><img title=\"". $LANG["edit_playlist"] ."\" border=0 src=images/image.gif></A>";
          if ($CONFIG["option_local_play"]) $play_local_link = "<A href=\"mp3daemon.php?action=playlist&playaction=$playlist64\" target=\"localplay\"> <img title=\"". $LANG["play_on_server"] ."\" align=\"bottom\" border=\"0\" title=\"". $LANG["local"] ."\" height=\"13\" src=\"images/agatha-localplay.png\"></a>&nbsp;";
              else $play_local_link = null;

          echo "\t$edit_playlist_link $play_playlist_shuffle $play_local_link $play_playlist_link<br>\n";

      }
      echo "</DIV>\n\n";
  }

   if ($listing["files"]["count"] > 0) {

      echo "<DIV class=category><A href=\"javascript:history.go(-1)\"><-".$LANG["back"].") </A>".$LANG["songs"] .":</DIV>\n";
      echo "<input type=hidden name=pdir value=\"$pdir_64\"></input>\n";
      echo "<input type=hidden name=cdir value=\"".$_REQUEST["cdir"]."\"></input>\n";
      echo "<DIV class=song>\n";
      for ($f=0; $f < $listing["files"]["count"]; $f++) {

           $file_name_dec = $listing["files"][$f];
           $file_name_enc = base64_encode($listing["files"][$f]);
           $len = strlen($file_name_dec) -4;

           if ($CONFIG["id3"] > 0) {
               $id3 = new id3(BASE_DIR."$pdir$file_name_dec");
               if ($CONFIG["id3"] == 1) { // Only Track Time
                   $file_name_display = strtolower($id3->length)." &nbsp; ".ereg_replace("^[0-9]*-", "", ereg_replace("_", " ", substr($file_name_dec,0,$len)));
               } else if ($CONFIG["id3"] == 2) {
                   $file_name_display = strtolower($id3->length." &nbsp; ".$id3->artists." ".$id3->name);
                   if ($id3->name == "") $file_name_display=$id3->length." &nbsp; ".ereg_replace("^[0-9]*-", "", ereg_replace("_", " ", substr($file_name_dec,0,$len)));
               }
           } else {
              $file_name_display=ereg_replace("^[0-9]*-", "", ereg_replace("_", " ", substr($file_name_dec,0,$len)));
           }

           $direct_64 = base64_encode("$pdir$file_name_dec");

           $play_single_checkbox    = "<input type=checkbox name=\"selected[]\" value=\"$file_name_enc\">";
           $play_single_link        = "<A href=\"$BASE_URL?play=$file_name_enc&#38;pdir=$pdir_64\"> $file_name_display</A></input>";
           $play_single_local_link  = "<A target=\"localplay\" href=\"mp3daemon.php?action=play&#38;playaction=$direct_64\"> <img title=\"". $LANG["play_on_server"] ."\" align=\"bottom\" border=\"0\" title=\"". $LANG["local"] ."\" height=\"13\" src=\"images/agatha-localplay.png\"></>";

           $play_single_stream_link = "<A href=\"".$CONFIG["shoutcast_base_url"]."content$pdir$file_shoutcast\"> [S] </A></input>";

           echo "\t$play_single_checkbox ";

           if ($CONFIG["option_local_play"] == 1) {
               echo " $play_single_local_link &nbsp";
           }
           echo "$play_single_link<br>\n";
      }
      echo "</DIV>\n\n";
  }

  if (count($file_list) == 0 && count($playlist_list) == 0 && count($dir_list) == 0) echo "<font size=+1 color=ff0000)<b><i>Empty</b></i></font><br>\n";

      if (DISPLAY_LOGO) echo " <div id=layer_logo class=logo><img src=images/agatha-logo-sm.png></div>\n";
      if ($CONFIG["option_local_play"]) $localoption =  "\t<option value=local>$LANG[local]</option>\n";
echo "
      <DIV class=category><A href=\"javascript:history.go(-1)\"><-$LANG[back]) </A> <img title=\"$LANG[help_songs]\" align=middle border=0 src=images/agatha_help_ico.png></DIV>\n
      <DIV class=control><input type=checkbox name=shuffle> $LANG[shuffle]<br>\n
      <br><input type=checkbox name=pladd> $LANG[add_to_playlist]\n
      <select name=plfile>\n\t<option value=new>$LANG[new]</option>\n $localoption
     ";

for ($r=0; $r<sizeof($playlist_list); $r++) {
     $fp=fopen(PLAYLIST_DIR."$playlist_list[$r]", "r");
     $list_name=fgets($fp, 1024);
     fclose($fp);
     echo "\t<option value=$playlist_list[$r]>$list_name</option>\n";
}

echo "
      </select><br>\n
      <br><input type=button value=\"$LANG[select_most]\" onclick=\"SelectMost();\">  <input type=reset><br>\n
      <br><input type=submit value=\"$LANG[submit_list]\"></DIV>\n
      </form>\n
      </html>\n
     ";

}


/**************************************************************************************/


function playlist_edit() {
global $CONFIG, $LANG, $BASE_URL, $DEBUG;

                 if(DEBUG) error_log("playlist_edit= Enter Function",0);
  $song_list=array();
  $new_song_list=array();
  $del = $_POST["del"];
  $listname = $_POST["listname"];

  if(isset($_GET["remove"])) {
            if(DEBUG) error_log("playlist_edit= Enter REMOVE",0);

      $filetoremove = SHAREDPLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_GET["pledit"];
      if(file_exists($filetoremove)) {
         $err = unlink($filetoremove);
      }
      $filetoremove = PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_GET["pledit"];
      if(file_exists($filetoremove)) {
         $err = unlink($filetoremove);
      }

      #error($LANG["error_file_delete"]);
      #exit;
      echo "<html>";
      echo "<link href=\"agatha.css\" rel=\"stylesheet\" type=\"text/css\" >\n";
      echo "<body>";
      if(isset($_POST["cdir"])) echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2;URL=$BASE_URL?cdir=".$_POST["cdir"]."&pdir=".$_POST["pdir"]."\">";
        else echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2;URL=$BASE_URL\">";
      echo "<link href=\"agatha.css\" rel=\"stylesheet\" type=\"text/css\" >\n";
      echo "$LANG[playlist_deleted] <br><br>\n";
      echo "</body></html>";
      exit;

  }

  if(isset($_GET["shuffle_playlist"]) && $_GET["shuffle_playlist"] == 1) {

    if(file_exists(PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_GET["pledit"])) {
      $fp=fopen(PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_GET["pledit"], "r");   # Write the list back to disk
    } else error($LANG["error_open_playlist"]." ".PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_GET["pledit"].".");

    $song_list = array();

    while(!feof ($fp)) {
      array_push($song_list, fgets($fp, 4096));
    }
    fclose($fp);

    $playlist_name = array_shift($song_list);
    my_shuffle($song_list);
    array_unshift($song_list, $playlist_name);

    $fp=fopen(PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_GET["pledit"], "w");   # Write the list back to disk
    for($i=0; $i<sizeof($song_list); $i++) {
      fputs($fp, "$song_list[$i]");
    }
    fclose($fp);
    $song_list = array();

  }




  /*  Make sure the list name is not unrealisticly long */
  // Debugging? Klingons do not debug. Our software does not coddle the weak.
  if(isset($_POST["listname"])) {
     if(strlen($_POST["listname"]) > 60) $_POST["listname"] = substr($_POST["listname"], 0, 60);
  }

  if(isset($_POST["listname"]) || isset($_POST["del"])) {
            if(DEBUG) error_log("playlist_edit= Enter MODIFY", 0);

    if(isset($_POST["shareplaylist"])) {
       if(!($_POST["shareplaylist"] == "no" || $_POST["shareplaylist"] == "yes")) {
          error("Wrong Parameter Supplied ".$_POST["shareplaylist"]);
       }

       if($_POST["shareplaylist"] == "yes") {

          if(!file_exists(PLAYLIST_DIR."sharedplaylists/".$_COOKIE["uniqueid"].".".$_REQUEST["pledit"])) {
             symlink(PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_REQUEST["pledit"],
                     PLAYLIST_DIR."sharedplaylists/".$_COOKIE["uniqueid"].".".$_REQUEST["pledit"]);
          }
       } else if($_POST["shareplaylist"] == "no") {
          if(file_exists(PLAYLIST_DIR."sharedplaylists/".$_COOKIE["uniqueid"].".".$_REQUEST["pledit"])) {
             unlink(PLAYLIST_DIR."sharedplaylists/".$_COOKIE["uniqueid"].".".$_REQUEST["pledit"]);
          }
       }
    }


    if(file_exists(PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_POST["pledit"])) {
    $fp=fopen(PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_POST["pledit"], "r");
    } else error($LANG["error_open_playlist"]." ".PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_POST["pledit"].".");

    while(!feof ($fp)) {
      array_push($song_list, fgets($fp, 4096));
    }
    fclose($fp);

    $song_list[0] = $_POST["listname"]."\n";               # Set the list name
      if(isset($_POST["del"])) {                           # If selected to delete..
                       if(DEBUG) error_log("playlist_edit= Enter DELETE SONG",0);
        for($i=0; $i<sizeof($_POST["del"]); $i++) {          # remove from array
          for($p=0; $p<sizeof($song_list); $p++) {           #
                       if(DEBUG) error_log("del :$del[$i]: song :$p:",0);
            if($del[$i] == $p) {
               $i++;
               continue;
            }
            else array_push($new_song_list, $song_list[$p]);
          }                                                  #
        }                                                    #
        $song_list=$new_song_list;                           # Swap in the adjusted array
      }                                                      #

    $fp=fopen(PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_POST["pledit"], "w");   # Write the list back to disk
    for($i=0; $i<sizeof($song_list); $i++) {
      fputs($fp, "$song_list[$i]");
    }

  header("Content-Type: text/html");
  echo "<META HTTP-EQUIV=Refresh Content=2;URL=$BASE_URL?pledit=".$_POST["pledit"].">";
  echo "<link href=\"agatha.css\" rel=\"stylesheet\" type=\"text/css\" >\n";
  echo $LANG["playlist_modified"];
  exit;
  }

  /* Prepare to Display the PLEdit Screen - Read in all Required Data *****************/

  $fp=fopen(PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_GET["pledit"], "r");     # Read the list into an array for display
  while(!feof ($fp)) {
    array_push($song_list, fgets($fp, 4096));
  }
  fclose($fp);

  if(file_exists(SHAREDPLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_REQUEST["pledit"])) {
     $shareplaylist = "yes";
  }

  echo "<html>\n";
  echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
  echo "<head><title>Agatha - Playlist Edit</title></head>\n";
  echo "<body>\n<link href=\"agatha.css\" rel=\"stylesheet\" type=\"text/css\" >\n";
  draw_dirnav_bar();
  echo "<form method=post action=\"$BASE_URL\">\n";
  for($p=0; $p<(sizeof($song_list) -1); $p++) {
    if($p == 0) {
     echo "<input type=hidden name=pledit value=".$_GET["pledit"]."></input>\n";
     echo "<input type=text name=listname value=\"$song_list[$p]\"> <img title=\"".$LANG["help_list_edit"]."\" src=images/agatha_help_ico.png align=middle border=0><br><br>\n";
     echo "<font size=-2><A href=\"$BASE_URL?pledit=".$_GET["pledit"]."&#38;remove=1\">(". $LANG["delete_list"].")</A></font><br>\n";
     echo "<br>\n";
     continue;
    }
    echo "<input type=checkbox name=del[] value=$p> $song_list[$p]</input><br>\n";
  }
  if($shareplaylist == "yes") {
    echo "<br>Share Playlist <input type=radio name=shareplaylist checked value=yes> Yes </input><input type=radio name=shareplaylist value=no> No</input><br></input>\n";
    } else {
    echo "<br>Share Playlist<input type=radio name=shareplaylist value=yes> Yes </input><input type=radio name=shareplaylist checked value=no> No</input><br></input>\n";
  }

  echo "<br><A href=\"$BASE_URL?pledit=".$_GET["pledit"]."&#38;shuffle_playlist=1\">(".$LANG["shuffle"] = "Shuffle".")</A></font><br>\n";

  echo "<br><input type=submit value=". $LANG["modify_list"] ."><br><br><A href=\"javascript:history.go(-1)\"><-".$LANG["back"].") </A><br>\n";
  echo "</form></body></html>";

}


/**************************************************************************************/


function choose_next_playlist() {
  global $CONFIG, $playlist_list, $LANG;
   for($i=0; $i<=10; $i++) {
     if($i < 10) {
       while(!file_exists(PLAYLIST_DIR.$_COOKIE["uniqueid"].".$i")) {
         return($i);
       }
     } else error($LANG["error_too_many_playlists"]);
   }
}


/**************************************************************************************/

function playlist_add() {
global $CONFIG, $playlist_list, $pdir, $BASE_URL;

    if(DEBUG) error_log("playlist_add= ".sizeof($_REQUEST["selected"]),0);

    $plfile = $_REQUEST["plfile"];
    $selected = $_POST["selected"];

    include_once "mp3daemon.class.php";

    if ($plfile == "local") {

        $mp3 = new mp3daemon(LOCAL_PLAY_SOCKET);

        foreach ($selected as $item) {
                 $args[] = BASE_DIR . $pdir . base64_decode($item);
        }

        if ($mp3->command("add", $args) === FALSE) die($mp3->error);
        $status = $mp3->status();
        if ($status["state"] == "stopped") $mp3->command("play");

        header("Content-Type: text/html");
        echo "<body onload=\"history.back(-1)\">";
        exit;

    } else if ($plfile == "new") {

        read_playlists(PLAYLIST_DIR, $playlist_list);
        if (sizeof($playlist_list) == 0) {
            $nextlist=0;
        } else {
            $nextlist = choose_next_playlist();
        }
        $fp = fopen(PLAYLIST_DIR.$_COOKIE["uniqueid"].".$nextlist", "a+");
        fputs($fp, "Playlist $nextlist\n");
        fclose($fp);
        $plfile = $_COOKIE["uniqueid"].".$nextlist";
    }

        $fp = fopen(PLAYLIST_DIR."$plfile", "a+");
        $mydir = new mydir(BASE_DIR . $pdir);
        $listing = $mydir->getentries();

        for ($i=0; $i < count($selected); $i++) {
             $file_name_dec = base64_decode($selected[$i]);
             fputs($fp, "$pdir$file_name_dec\n");
             if(DEBUG) error_log("pladd= pdir :$pdir: file_list_index:$file_name_dec:", 0);
        }
        fclose($fp);


  header("Content-Type: text/html");
  if(isset($_REQUEST["cdir"])) echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2;URL=$BASE_URL?pdir=".$_REQUEST["pdir"]."\">";
    #echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2;URL=$BASE_URL?cdir=".$_REQUEST["cdir"]."&pdir=".$_REQUEST["pdir"]."\">";
    #echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2;URL=$BASE_URL\">";


  echo "<link href=\"agatha.css\" rel=\"stylesheet\" type=\"text/css\" >\n";
  echo "Added to Playlist";
  exit;

}

/**************************************************************************************/

function play_multiple($selected) {
    global $BASE_URL, $pdir_64, $DEBUG;

    if(DEBUG) error_log("play_multiple= Selection Size :".sizeof($selected).":", 0);

    if($_POST["shuffle"]) my_shuffle($selected);                         # and you want them shuffled..

    header("Content-Type: audio/mpegurl");
    header("Content-Disposition: filename=\"selected.m3u\"");


    if (is_numeric($selected[0])) {

        #for ($i=0; $i<count($selected); $i++) {
        #     if(DEBUG) error_log("play_multiple= echo $BASE_URL?stream=$selected[$i]&pdir=$pdir_64");
        #     echo "$BASE_URL?stream=$selected[$i]&pdir=$pdir_64\r\n";
        #}

    } else {
        for ($i=0; $i<count($selected); $i++) {
             if(DEBUG) error_log("play_multiple= echo $BASE_URL?stream=$selected[$i]&pdir=$pdir_64");
             echo "$BASE_URL?direct=1&stream=$selected[$i]&pdir=$pdir_64\r\n";
        }


    }
}


/**************************************************************************************/


function play_playlist() {
    global $CONFIG, $pdir, $BASE_URL;
    if(DEBUG) error_log("play_playliste= Enter Function", 0);

    $pl = $_REQUEST["pl"];

    if ($_REQUEST["shared"] == 1) {
        $sharedplaylist_list = read_shared_playlists();
        if(DEBUG) error_log("list :".$sharedplaylist_list[$pl].":",0);
        $playlist_to_open = PLAYLIST_DIR.$sharedplaylist_list[$pl];
    } else {
        $playlist_to_open = PLAYLIST_DIR.$_COOKIE["uniqueid"].".".$_GET["pl"];
    }
    if(DEBUG) error_log("play_playliste= playlist_to_open :$playlist_to_open:",0);
    if (!file_exists($playlist_to_open)) {
        error($LANG["error_open_playlist"]);
    }

    header("Content-Type: audio/x-mpegurl");
    header("Content-Disposition: filename=\"playlist.m3u\"");
    $songlist = array();
    $fp = fopen($playlist_to_open, "r");

    while (!feof ($fp)) array_push($songlist, fgets($fp, 4096));

    fclose($fp);
    array_shift($songlist);

    $nextsong = array();
    for ($i=0; $i<(sizeof($songlist)); $i++) $nextsong[$i]=$i;

    if ($_GET["shuffle"] == 1) my_shuffle($nextsong);

    if ($_REQUEST["shared"] == 1) {
        for ($s=0; $s<(sizeof($songlist) - 1); $s++) {
             echo "$BASE_URL?stream=".$_GET["pl"]."&nextsong=$nextsong[$s]&shared=1\r\n";
        }
    } else {
        for ($s=0; $s<(sizeof($songlist) - 1); $s++) {
             echo "$BASE_URL?stream=".$_GET["pl"]."&nextsong=$nextsong[$s]&uniqueid=".$_COOKIE["uniqueid"]."\r\n";
        }
    }
}

/**************************************************************************************/

function stream() {
    global $CONFIG, $MIME_TYPES, $pdir, $LANG;

    if ($_REQUEST["shared"] == 1) {
        $sharedplaylist_list = read_shared_playlists();
        $stream = $_GET["stream"];
        $playlisttoopen = SHAREDPLAYLIST_DIR.$sharedplaylist_list[$stream];
    } else {
        $playlisttoopen = PLAYLIST_DIR .$_REQUEST["uniqueid"] . "." . $_REQUEST["stream"];
    }

    if (isset($_REQUEST["nextsong"])) {  // This is from an ondisk playlist
        if(DEBUG) error_log("stream= Enter NextSong", 0);
        if(DEBUG) error_log("stream= Playlist to open :$playlisttoopen:", 0);

        $songlist = array();

        if (!file_exists($playlisttoopen)) {
            error($LANG["error_open_playlist"]);
        }

        $fp = fopen($playlisttoopen, "r");  # Open the matching playlist for this requestors IP
        while (!feof ($fp)) array_push($songlist, fgets($fp, 4096));    # Read in the list of songs from the playlist
        array_shift($songlist);

        $extension = trim(substr(strrchr($songlist[$_REQUEST["nextsong"]], "."),1));

        $file_to_open = BASE_DIR . trim($songlist[$_REQUEST["nextsong"]]);

        $file_size = filesize("$file_to_open");
        if(DEBUG) error_log("stream= File to stream :$file_to_open: Size :$file_size:", 0);
        $fp = fopen($file_to_open, "r");

    } elseif ($_REQUEST["direct"] == 1) {

        if (strstr(base64_decode($_REQUEST["stream"]), $pdir)) {
        
            $file_to_open = BASE_DIR . base64_decode($_REQUEST["stream"]);
        } else {
            $file_to_open = BASE_DIR . $pdir . base64_decode($_REQUEST["stream"]);
        }
        error_log("FILE:  $file_to_open",0);
        if(DEBUG) error_log("stream= Direct File to Open :$file_to_open:", 0);

    } else {

        $file_name_dec = base64_decode($_REQUEST["stream"]);
        $file_to_open = BASE_DIR . "$pdir$file_name_dec";
    }

    $display_name = substr($file_to_open, strrpos($file_to_open, '/')+1);
    $extension = trim(substr(strrchr($file_to_open, "."),1));

    if(DEBUG) error_log("stream= Extension NO list :$extension:", 0);
    if(DEBUG) error_log("stream= Header :$MIME_TYPES[$extension]:", 0);


    $file_size = filesize($file_to_open);
    if(DEBUG) error_log("stream= File to stream: $file_to_open", 0);
    if (!file_exists($file_to_open)) {
        error($LANG["error_open_song"]);
    }

    $fp = fopen($file_to_open, "r");

    header("$MIME_TYPES[$extension]");
    header("Content-Disposition: filename=$display_name");
    header("Content-Length $file_size");

    if(DEBUG) error_log("stream= :Begin Streaming $display_name", 0);


    if ($CONFIG["use_alternate_streaming"]) {

        $id3 = new id3($file_to_open);
        streamfp($fp, $id3->bitrate);

    } else {

        fpassthru($fp);

    }

    fclose($fp);
    if(DEBUG) error_log("stream= :End Streaming $display_name", 0);
}

/**************************************************************************************/

class MeasureTime {
        var $start = 0;
        var $alarm = 0;

        function getmicrotime() {
            list($usec, $sec) = explode(" ",microtime());
            return ((float)$usec + (float)$sec);
        }

        function start() {
            $this->start = $this->getmicrotime();
            usleep(100);
        }

        function setalarm($alarm) {
            $this->alarm = $alarm;
        }

        function alarm() {
            if ( ($this->getmicrotime() - $this->start)   >= (float)$this->alarm) return true;
            return false;
        }
}

function streamfp($fp, $kbit, $prebuffer=true) {
    global $CONFIG;

    $bread = ($kbit * 1000) / 8;
    $readbuffer = ($bread / 100) * $CONFIG['buffer'];

    if (DEBUG) error_log("streamfp= kbit :$kbit: readbuffer :$readbuffer:", 0);

    $mt = new MeasureTime();
    $mt->setalarm($CONFIG["sleeptime"]);


    echo fread($fp, $CONFIG["prebuffer"]);
    flush();

    $mt->start();
    while (!feof($fp)) {
           echo fread($fp, $readbuffer);
           flush();
           while (!$mt->alarm()) usleep($CONFIG["precision"]);
           $mt->start();
    }
}


/**************************************************************************************/


function read_dir($dir_parent) {
  global $CONFIG, $DEBUG, $dir_list, $file_list, $pdir;

            if(DEBUG) error_log("read_dir= Enter Function", 0);
  $c=0;
  $d = opendir(BASE_DIR."$pdir");
    while($raw_list[$c]=readdir($d)) {
      if($raw_list[$c] == "." || $raw_list[$c]==".." || $raw_list[$c]=="sharedplaylists") continue;
      if(is_dir(BASE_DIR."$pdir$raw_list[$c]")) {
         array_push($dir_list, $raw_list[$c]."/");
        # error_log("Raw Dir ".$raw_list[$c], 0);
      }
        elseif (strtolower(substr($raw_list[$c], -4)) != ".mp3") continue;
        else array_push($file_list, $raw_list[$c]);
    }
  sort($dir_list);
  sort($file_list);

}

/**************************************************************************************/

class mydir {

   var $fp;
   var $dirs;
   var $files;
   var $error;


   function mydir($dir) {

      $this->dir = null;
      $this->files = null;
      $this->error = null;


      if (! file_exists($dir)) {
          $this->error = "Supplied Path Does Not Exist";
          return FALSE;
      } else if (! is_readable($dir)) {
          $this->error = "Supplied Path Is Not Readable";
          clearstatcache();
          return FALSE;
      } else if (! is_dir($dir)) {
          $this->error = "Supplied Path Is Not a Directory";
          clearstatcache();
          return FALSE;
      }
      if (! $this->fp = opendir($dir)) {
          $this->error = "There was a problem reading $dir.";
          return FALSE;
      }

      $this->dir = $dir."/";
      return TRUE;

   }

   function getentries() {
       $this->error = null;
       $file_list = array();
       $dir_list  = array();

       $c = 0;
       while ($raw[$c] = readdir($this->fp)) {
              if ($raw[$c] == "." || $raw[$c] == ".." || $raw[$c] == "sharedplaylists" ) continue;
              if (is_dir($this->dir.$raw[$c])) array_push($dir_list, $raw[$c]."/");
              elseif (stristr(substr($raw[$c], -4), ".mp3") === FALSE) continue;
              else array_push($file_list, $raw[$c]);
       }
       sort($dir_list);
       sort($file_list);

       $list["directories"]["count"] = count($dir_list);
       $list["files"]["count"] = count($file_list);

       for ($i=0; $i < count($dir_list); $i++) {
            $list["directories"][$i] = $dir_list[$i];
       }

       for($j=0; $j < count($file_list); $j++) {
            $list["files"][$j] = $file_list[$j];
       }

       return $list;

   }


} // Class


/**************************************************************************************/


function read_playlists($dir, &$playlist) {
  global $CONFIG;
  $c = 0;
  $d = opendir($dir);
    while($raw_list[$c]=readdir($d)) {
    if($raw_list[$c] == "." || $raw_list[$c]==".." || $raw_list[$c] == "sharedplaylists") continue;
    if($_COOKIE["uniqueid"] == substr($raw_list[$c], 0, -2)) {
      array_push($playlist, $raw_list[$c]); }
    }
  return($playlist);
}

/**************************************************************************************/

function read_shared_playlists() {
  $sharedplaylist_list = array();
  $c = 0;
  $d = opendir(SHAREDPLAYLIST_DIR);
    while($raw_list[$c] = readdir($d)) {
       if($raw_list[$c] == "." || $raw_list[$c]==".." || $raw_list[$c]=="index.php") continue;
       #error_log("shared list :$raw_list[$c]:", 0);
       array_push($sharedplaylist_list, $raw_list[$c]);
    }
  return($sharedplaylist_list);
}

/**************************************************************************************/

function my_shuffle(&$array) {
  global $DEBUG;
      if(DEBUG) error_log("my_shuffle= Enter Function", 0);
  srand ((double)microtime()*1000000);
  for ($j = count($array) - 1; $j > 0; $j--) {
    if (($i = rand(0,$j))<$j) {
      $swp=$array[$i]; $array[$i]=$array[$j]; $array[$j]=$swp;
    }
  }
}

/**************************************************************************************/

function play_single() {
    global $CONFIG, $file_list, $BASE_URL, $pdir_64, $pdir, $play;

    $mydir = new mydir(BASE_DIR . $pdir . $cdir);
    $listing = $mydir->getentries();
    $play = base64_decode($_REQUEST["play"]);

      $file_to_play = $play.".m3u";
      if (DEBUG) error_log("File to play:$file_to_play: pdir :$pdir: cdir :$cdir:", 0);

      header("Content-Type: audio/x-mpegurl");
      header("Content-Disposition: filename=\"$file_to_play\"");

      $id3 = new id3(BASE_DIR.$pdir.$play);
      echo "#EXTM3U\n";
      echo "#EXTINF:$id3->lengths, $id3->name\n";

      echo "$BASE_URL?stream=".$_REQUEST["play"]."&pdir=$pdir_64\n";

}




/**************************************************************************************/
// By filing this bug report you have challenged the honor of my family. Prepare to die!

#function play_local($file, $pdir) {
#    global $CONFIG, $cdir, $playlist_list;
#
#    include "mp3daemon.class.php";
#    $mp3 = new mp3daemon(LOCAL_PLAY_SOCKET);
#
#    $mp3->command("play", array(BASE_DIR.$pdir.$file));
#
#    header("Content-Type: text/html");
   # echo "<head>\n
   #       <script language=\"JavaScript\" type=\"text/javascript\">\n
   #          function reloadlocalplay(url) {\n
   #             targetFrame = top.frames.localplay;\n
   #             targetFrame.location.href = url;\n
   #          }\n
   #       </script>\n
   #      ";
#    echo "<body onload=\"history.back(-1)\">";
#
#}


/**************************************************************************************/

function pray ($data, $functions=0) {
    if($functions!=0) { $sf=1; } else { $sf=0 ;}    // This kluge seemed necessary on one server.
    if (isset ($data)) {
        if (is_array($data) || is_object($data)) {
            if (count ($data)) {
                echo "<OL>\n";
                while (list ($key,$value) = each ($data)) {
                       $type=gettype($value);
                       if ($type=="array" || $type == "object") {
                           printf ("<li>(%s) <b>%s</b>:\n",$type, $key);
                           pray ($value,$sf);
                       } elseif (eregi ("function", $type)) {
                           if ($sf) {
                               printf ("<li>(%s) <b>%s</b> </LI>\n",$type, $key, $value);
                               //   There doesn't seem to be anything traversable inside functions.
                           }
                       } else {
                           if (!$value) { $value="(none)"; }
                           printf ("<li>(%s) <b>%s</b> = %s</LI>\n",$type, $key, $value);
                       }
                }
                echo "</OL>end.\n";
            } else {
                echo "(empty)";
            }
        }
    }
}    // function


function error($text) {
  echo "<html>\n<head><title>Agatha - Error</title></head>\n";
  echo "<body>\n\n";
  echo "<link href=\"agatha.css\" rel=\"stylesheet\" type=\"text/css\" >\n";
  echo "<DIV class=error>Error: $text<br>\n";
  error_log("ERROR: $text", 0);
  echo "</body></html>";
  exit;
}


?>
