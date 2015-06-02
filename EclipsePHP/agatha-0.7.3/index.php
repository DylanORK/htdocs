<?

$LANG["error_no_config"] = "No Config File Found<br><br>
                            Copy config.php.dist to config.php and edit the path info... Also.. be sure to read the README file";


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


$BASE_URL = $_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"];
$URL = $_SERVER["SERVER_NAME"] . substr($_SERVER["SCRIPT_NAME"], 0, strrpos($_SERVER["SCRIPT_NAME"], '/'))."/";

if($CONFIG["option_local_play"] == TRUE) {
    include "mp3daemon.class.php";
    if ($mp3 = new mp3daemon(LOCAL_PLAY_SOCKET)) {
        if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE")) $height = 60;
            else $height = 55;
            #else $height = 42;

        echo "<head>\n
              <title>Agatha - Be Heard!</title>\n
              <LINK rel=\"SHORTCUT ICON\" href=\"http://$URL/favicon.ico\">
              <frameset rows=$height,* frameborder=0 border=0>\n
              <frame marginwidth=0 scrolling=no marginheight=0 name=localplay src=mp3daemon.php>\n
              <frame marginwidth=0 marginheight=0 name=main src=agatha.php>\n
              </frameset\n\n
              </head>\n\n";
    } else {

        include("agatha.php");
    }

} else include("agatha.php");



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

