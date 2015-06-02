<?

if(file_exists("config.php")) include("config.php");
  else {
     error($LANG["error_no_config"]);
     exit;
}

/* Check Input For Bad Stuff ******************************************/

#require_once("paranoia.inc.php");
#if(isset($_REQUEST)&&!empty($_REQUEST)){
#  $par=new paranoia($_REQUEST);
#  if(!empty($par->wrongParams)) {
#      error("There Were Invalid Characters In Your Request");
#  }
#}
/* End Check Input For Bad Stuff **************************************/

   // Language selection
    $LANG = array();
    if (isset($CONFIG["option_language"])) {
        require_once("lang/".$CONFIG["option_language"]);
    }   else require_once("lang/agatha-us-en.php");



$BASE_URL = "http://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"];

require_once("mp3daemon.class.php");

$mp3 = new mp3daemon(LOCAL_PLAY_SOCKET);

if(! $status = $mp3->status()) die($mp3->error);
if(! $pl = $mp3->GetPlaylist()) die ($mp3->error);


if (!isset($_REQUEST["pdir"])) {      # If you are hitting the first page there is no parent dir
        $pdir = "/";                      # So set it to /
        $pdir_64 = $pdir;

} else if ($_REQUEST["pdir"] != "/") {
        $pdir_64 = $_REQUEST["pdir"];
        $pdir = stripslashes(base64_decode($_REQUEST["pdir"]));   # Convert from base64 if you do have one..
}


if (isset($_REQUEST["action"])) {


    if (strpos($_REQUEST["action"], "vol") !== false) {
        $aumix = new aumix();
        $aumix->setvolume($_REQUEST["action"]);

    } else {

        switch ($_REQUEST["action"]) {

           case "ff":

                $mp3->command("ff", array($CONFIG["jump_time"]));

           break;

           case "rw":

                $mp3->command("rw", array($CONFIG["jump_time"]));

           break;


           case "saveplaylist":

                $pl = $mp3->getplaylist();

                $nextlist = choose_next_playlist();
                $fp = fopen(PLAYLIST_DIR.$_COOKIE["uniqueid"].".$nextlist", "a+");
                fputs($fp, "Playlist $nextlist\n");

                for ($i=0; $i < count($pl); $i++) {
                   $file = str_replace(BASE_DIR, "", $pl[$i]["filename"]);
                   if ($file == "") continue;
                   fputs($fp, $file."\n");
                }

                fclose($fp);



           break;

           case "playlist":
                $songlist = array();

                $playlist_file = base64_decode($_REQUEST["playaction"]);

                $fp = fopen(PLAYLIST_DIR . $playlist_file, "r");
                while (!feof ($fp)) array_push($songlist, trim(fgets($fp, 1024)));
                fclose($fp);
                array_shift($songlist);
                for ($i=0; $i < count($songlist)-1; $i++) {
                     if(DEBUG) error_log("songlist :" . $songlist[$i] . ":", 0);
                     $out[] .= BASE_DIR . $songlist[$i];
                }

                $mp3->command("add", $out);
                $status = $mp3->status();
                if ($status["state"] == "stopped") $mp3->command("play");

           break;

           case "playrecursive":
                 $rdirs = array();
                 $playaction = base64_decode($_REQUEST["playaction"]);
                 recurse(BASE_DIR . $pdir . $playaction);


                 for ($i=0; $i < count($rdirs); $i++) {
                     $out[] .= BASE_DIR . $rdirs[$i];
                 }

                 $mp3->command("add", $out);
                 $status = $mp3->status();
                 if ($status["state"] == "stopped") $mp3->command("play");


           break;

           case "play":
                if (! isset($_REQUEST["playaction"])) {

                      if ($mp3->command("play") === FALSE) die ($mp3->error);

                } elseif (is_numeric($_REQUEST["playaction"])) {
                    
                    if ($mp3->command("play", array($_REQUEST["playaction"])) === FALSE) die ($mp3->error);

                } else {

                    $playaction = base64_decode($_REQUEST["playaction"]);
                    $status = $mp3->status();

                    if ($status["state"] == "playing") {

                        $mp3->command("add", array(BASE_DIR . $playaction));

                    } else {

                        if ($mp3->command("play", array(BASE_DIR.$playaction)) === FALSE) die ($mp3->error);

                    }


                }

           break;

           case "repeat":

                if ($status["loop"] == "all") {

                    if ($mp3->command("loop", array("single"))  === FALSE) die ($mp3->error);

                } else {

                if ($mp3->command("loop", array("all")) === FALSE) die ($mp3->error);

                }

           break;

           case "shuffle":

                if ($mp3->command("rand") === FALSE) die ($mp3->error);

           break;

           case "clearplaylist":

               if ($mp3->clearplaylist() === FALSE) die ($mp3->error);

           break;

           default:

               if( $mp3->command($_REQUEST["action"]) === FALSE) die ($mp3->error);

        } // Switch
    } // Else

    if(! $status = $mp3->status()) die($mp3->error);
    if(! $pl = $mp3->GetPlaylist()) die ($mp3->error);

}

if ($status["remaining"] == 0 || $status["state"] == "stopped") $remaining = 10;
   else if ($status["remaining"] > 30) $remaining = 30;
   else $remaining = $status["remaining"] +2;

?>


<head>
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<meta http-equiv="expires" content="0">
<META HTTP-EQUIV="Refresh" CONTENT=<?echo $remaining ?>;URL="<?echo $BASE_URL ?>">
<link href="agatha.css" rel="stylesheet" type="text/css">
</head>


<?

print_local_ctl();




function print_playlist() {
    global $mp3, $LANG, $BASE_URL, $pl;

    echo "<form name=\"frmplselect\" action=\"$BASE_URL\" method=\"post\">\n";
    echo "<a href=\"$BASE_URL?action=clearplaylist\">$LANG[clear]</a>\n";
    echo "<input type=\"hidden\" name=\"action\" value=\"play\">\n";
    echo "<select style=\"width: 260px\" onchange=\"frmplselect.submit()\" name=\"playaction\">\n";

    for ($i=0; $i < $pl["trackcount"]; $i++) {
         $iplus = $i+1;
         $ienc = base64_encode($i);

         if ($i < 9) $extra = "&nbsp;&nbsp;";
         else $extra = null;

         if ($i == $pl["current"]) {

             echo "<option SELECTED value=\"$i\">".$pl[$i]["title"]."</option>\n";

         } else {

             echo "<option value=\"$i\">".$pl[$i]["title"]."</option>\n";

         }
    }

    echo "</select> <a href=\"$BASE_URL?action=saveplaylist\">$LANG[save]</a></form>\n";

}




function print_volume_ctl() {
    global $BASE_URL;

    echo "<a alt=\"Volume (-)\"  href=\"$BASE_URL?action=voldown\"><<</a>";
    echo "<a alt=\"Volume 0%\" href=\"$BASE_URL?action=vol0\">=</a>";
    echo "<a alt=\"Volume 10%\" href=\"$BASE_URL?action=vol10\">=</a>";
    echo "<a alt=\"Volume 20%\" href=\"$BASE_URL?action=vol20\">=</a>";
    echo "<a alt=\"Volume 30%\" href=\"$BASE_URL?action=vol30\">=</a>";
    echo "<a alt=\"Volume 40%\" href=\"$BASE_URL?action=vol40\">=</a>";
    echo "<a alt=\"Volume 50%\" href=\"$BASE_URL?action=vol50\">||</a>";
    echo "<a alt=\"Volume 60%\" href=\"$BASE_URL?action=vol60\">=</a>";
    echo "<a alt=\"Volume 70%\" href=\"$BASE_URL?action=vol70\">=</a>";
    echo "<a alt=\"Volume 80%\" href=\"$BASE_URL?action=vol80\">=</a>";
    echo "<a alt=\"Volume 90%\" href=\"$BASE_URL?action=vol90\">=</a>";
    echo "<a alt=\"Volume 100%\" href=\"$BASE_URL?action=vol100\">=</a>";
    echo "<a alt=\"Volume (+)\" href=\"$BASE_URL?action=volup\">>></a>";
}


function print_local_ctl() {
    global $mp3, $LANG, $BASE_URL, $status, $pl;

    echo "<table border=0 cellspacing=0 cellpadding=0 width=100%>\n";
    echo "<tr valign=\"top\">\n";
    echo "<td nowrap width=25% align=\"left\"><font size=2>\n";
    echo "<a alt=\"Shuffle\" href=\"$BASE_URL?action=shuffle\">$LANG[shuffle]</a> ($status[random]) | ";
    echo "<a alt=\"Repeat\" href=\"$BASE_URL?action=repeat\">$LANG[repeat]</a> ($status[loop])</font>";
    echo "</td><td width=50% nowrap align=\"center\"><font size=2>\n";

    print_playlist();

    echo "</td><td width=25% nowrap align=right><font size=2>\n";

    print_volume_ctl();

    echo "</td></tr><tr>";
    echo "<td nowrap><font size=2>$status[title] -(". format_minutes($status["remaining"]).")</td>
          <td valign=top align=center><font size=2>
              <a href=\"$BASE_URL?action=rw\"> <<</a> |\n
              <a href=\"$BASE_URL?action=prev\"> $LANG[previous]</a> |\n
              <a href=\"$BASE_URL?action=play\"> $LANG[play]</a> |\n
              <a href=\"$BASE_URL?action=pause\"> $LANG[pause]</a> |\n
              <a href=\"$BASE_URL?action=stop\"> $LANG[stop]</a> |\n
              <a href=\"$BASE_URL?action=next\"> $LANG[next]</a> |\n
              <a href=\"$BASE_URL?action=ff\"> >></a>\n
    ";

    if ($status["state"] == "playing") {
        $current = $pl["current"]+1; // Accounts for the 0 Starting number for display
    } else $current = 0;        

    echo  "</td><font size=2>
          <td nowrap align=right><font size=2>$LANG[track] $current of $pl[trackcount] : $status[state]</td>
          </tr>
          ";

    echo "</table>\n";
}


function format_minutes($time) {
    $minutes = round($time / 60, 0);
    $seconds = $time % 60;

    return "$minutes:$seconds";
}


class aumix {

    function aumix() {


    }

    function setvolume($arg) {

        $vol = substr($arg, 3);

        if (is_numeric($vol)) $cmd = $vol;
        else if ($vol == "up") $cmd = "+5";
        else if ($vol == "down") $cmd = "-5";

        if(DEBUG) error_log (AUMIX_BIN." -w$cmd -v85", 0);
        system (AUMIX_BIN." -w$cmd -v85", $return);

    }



} //class

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
                                $tmp2 = str_replace(BASE_DIR, '', $tmp);
                                array_push($rdirs, $tmp2);

                        }
                }
                #echo "</ul>";
                closedir($handle);
        }

}

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


