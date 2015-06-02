<?php
/****************************************************************
*Author: Ovidiu EFTIMIE
*Copyright:Ovidiu EFTIMIE
*Last modified:Friday, November 03, 2000 10:43:17 AM
*Description : Checks an array to see if it contains invalid chars
*****************************************************************/
class paranoia{
var $wrongParams=false;
var $para=array("~","","!","@","#","\$","%","^","*","(",")","_","-","+","|","\\","{","}",";","\"","'",",","<",">","/");

#var $para=array("~","","!","@","#","\$","%","^","&","*","(",")","_","-","+","=","|","\\","{","}",":",";","\"","'",",","<",".",">","?","/");


/**************************************************
*Class constructor
*Params : $paramsArray - the array containing the data to be checked
***************************************************/
function paranoia($paramsArray){
reset($this->para);
$this->wrongParams=false;
while(list($k,$postvars)=each($paramsArray)){
        if(is_array($postvars)){
                while(list($r,$postvals)=each($postvars)){
                        while(list($r, $val)=each($this->para)){
                                if(($val == "" && $postvar == "")) continue;
                                $wrong=strchr($postvals,$val);
                                if(!empty($wrong)){
                                        $this->wrongParams=true;
                                }
                        }
                }
        }else{
                while(list($r, $val)=each($this->para)){
                        if(($val == "") && ($postvar == "")) continue;
                        $wrong=strchr($postvars,$val);
#                        error_log("Invalid Characters Posted :$postvars: Check :$val:", 0);
                                if(!empty($wrong)){
                                $this->wrongParams=true;
                                }
                }
                reset($this->para);
        }
}
return $this->wrongParams;
}
/*******************************************************
*Checks to see the invalid chars
********************************************************/
function checkParanoia(){
echo date("d M Y H:i:s",time())."<br>Checking paranoia chars...<br><br>";
        while(list($d,$r)=each($this->para)){
                echo $d.".&nbsp;&nbsp; <b>".$r."</b>  = <b>".ord($r)."</b><br>";
        }

}
/********************************************************
*Redirecting function
********************************************************/
function sendBack(){
echo '<html><head><title></title></head><body
onLoad="javascript:history.back(-1);"></body></html>';
}
}

?>
