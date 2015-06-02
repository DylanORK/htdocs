<?
if(!defined('MP3DAEMON_CLASS')) { // is the class defined yet?
    define('MP3DAEMON_CLASS',true); // define the class, so we don't include it more than once and have problems
    define('MP3DAEMON_CLASS_VERSION',"1.0");

    class mp3daemon {

    var $fp;                 // the file pointer, or the connection to the server running on winamp/
    var $socket;             // Location of the on disk socket we are talking to
    var $valid_commands;
    var $error = null;

    function mp3daemon($socket) {
        $error = null;
        $this->socket = $socket;
        $this->valid_commands = array( "add", "del", "ff", "info", "jump", "loop", "ls",
                                       "next", "pause", "play", "prev", "quit", "rand",
                                       "rw", "stop", "time"
                                     );
        if (! $this->command("ls", array(" -l"))){
           if (strpos($this->error, "socket") != FALSE) {
               $out = trim(system("mp3 ls", $ret));
               if ($out != null) {
                  $this->error = "Unable to start mp3daemon: $out";
                  return FALSE;
               }
           }
        }
        return true;
    }


    function command($cmd, $args=null) {
        $error = null;

        if (! $this->_validate_command($cmd)) return FALSE;

        if (! $this->_connect()) return FALSE;

        if ($args != null && !is_array($args)) {
            $error = "Arguments to command method must be in array format array('arg1', 'arg2') etc";
            return FALSE;
        }

        switch ($cmd) {

            case "add":
                 $say = "add\n";
                 foreach($args as $arg) {
                         $say .= $arg."\n";
                 }
                 $say .= "\n";

                 return $this->_getdata($say);

            break;

            case "play":

                 return $this->_play($args);

            break;

            default:
                 if ($args == null) {
                     return $this->_getdata("$cmd\n\n");
                 } else {
                        $say = "$cmd\n";
                        for ($i=0; $i < count($args); $i++) {
                             #if($cmd == "del") $say .= $args[$i];
                             #else
                             $say .= $args[$i];
                        }
                        $say = trim($say);
                        $say .= "\n\n";
                        if(DEBUG) error_log("Default Command Handler :$say:", 0);
                        return $this->_getdata($say);
                 }

            break;


        } // Switch

        if (! $this->_disconnect()) return FALSE;
    }

    function _validate_command($cmd) {
        $this->error = null;

        if (in_array($cmd, $this->valid_commands)) {
            $this->error = "That command is not valid";
            return TRUE;
        }
        else return FALSE;

    }


    function clearplaylist() {
        $this->error = null;

        $pl = $this->getplaylist();

        if ($this->command("loop", array("off")) === FALSE) return FALSE;

        if ($this->command("stop") === FALSE) return FALSE;

        for ($i=0; $i < count($pl); $i++) {
             $out[] .= "$i\n";
        }

        if ($this->command("del", $out) === FALSE) return FALSE;

        if ($this->command("loop", array("all")) === FALSE) return FALSE;

        return TRUE;
    }



    function _play($args) {
        $this->error = null;

        if ($args == null) {
            $say = "play\n\n";
        } else {
                $say  = "play\n";
                $say .= $args[0]."\n\n";
        }
        return $this->_getdata($say);

    }

    function status() {
        $this->error = null;
        $info = array();
        $timevalues = array("elapsed", "remaining", "total");

        $raw = $this->command("info");

        if ($raw === FALSE) return FALSE;

        foreach ($raw as $line) {
            $tmp = explode("|", $line);
            $key = trim($tmp[0]);
            $value = trim($tmp[1]);

            if (in_array($key, $timevalues)) {    // Get rid of that string.. and we don't need 3 digit precision on times do we?
               $value = round(substr($value, 0, strpos($value, ' ')), 0);
            }

            if ($key == "random") {
               if ($value == 1) $value = "on";
               else if ($value == 0) $value = "off";
            }

            $info[$key] = $value;
        }

        return $info;
    }


    function GetPlaylist() {
        $this->error = null;

        $raw = $this->command("ls", array("-l"));

        $out["trackcount"] = count($raw);

        for ($i=0; $i < count($raw); $i++) {
            $tmp = explode(" ", $raw[$i]);


            if (trim($tmp[0]) == ">") {
                $out[$i]["track"] = trim($tmp[1]);
                $out[$i]["tracklength"] = trim($tmp[2]);

                $tmp2 = explode('"', strstr($raw[$i], '"'));
                $out[$i]["title"] = strtr($tmp2[1], "_", " ");
                $out[$i]["filename"] = $tmp2[3];

                $out[$i]["current"] = TRUE;
                $out["current"] = $i;

            } else {
                $out[$i]["track"] = trim($tmp[0]);
                $out[$i]["tracklength"] = trim($tmp[1]);

                $tmp2 = explode('"', strstr($raw[$i], '"'));
                $out[$i]["title"] = strtr($tmp2[1], "_", " ");
                $out[$i]["filename"] = $tmp2[3];

                $current = FALSE;

            }

        }

        if ($out) return $out;
        else return true;

    }


    function _GetData($command = null){
        $output = null;
        
        if (!$this->fp) {
            $this->error = "No Socket!";
            return false;
        }

        if ($command != null){
            if(DEBUG) error_log ("Command :$command:", 0);
            fputs($this->fp, $command);
        } else {
            $this->error = "No command given";
            return FALSE;
        }

        while (TRUE) {
            flush();

            $s = trim(fgets($this->fp ,2000));
            if ($s == null) {
                #$output = array();
                break;
            }

            $output[] = $s;
        }

        return $output;
    }


    function _connect() {
        $this->error = null;
                         # trim unix://
        if (!file_exists(substr($this->socket, 5))) {
            $this->error = "Given Socket :$this->socket: Does Not Exist: You need to start the mp3daemon";
            return FALSE;
        }

        if (!$this->fp = fsockopen("$this->socket", 0, $errno, $errstr)) {
            $this->error = "Unable to open socket $this->socket error :$errno:$errstr:";
            return FALSE;
        }

        return TRUE;
    }

    function _disconnect() {
         $this->error = null;
         if (!fclose($this->fp)) {
             $this->error = "Error Closing Socket :$this->socket:";
             return FALSE;
         }
         return TRUE;
    }




    } // Class
} //If

?>
