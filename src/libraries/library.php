<?php
######################################################################
# Wi-Fi-voucher-o-matic-2.0
# Copyright (C) 2022 Marco Schiavello, Ivan Bertotto, ITIS Avogadro
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.If not, see <http://www.gnu.org/licenses/>.
######################################################################

session_start();

use PHPMailer\PHPMailer\PHPMailer;// namespaces for PHPmailer
use PHPMailer\PHPMailer\Exception;

require_once LIB_PATH.'/UILibrary.php';
require_once LIB_PATH."/DB.php";

/**
 * Function to send E-Mail
 * 
 * @param string $email the destination email
 * @param string $fullname fullname of the receiver  
 * @param string $title the title of the E-Mail
 * @param string $body the body of the E-Mail, it could contains HTML
 */
function sendMail($email, $fullName, $title, $body)
{
    require_once LIB_PATH.'/PHPmailer/src/Exception.php';
    require_once LIB_PATH.'/PHPmailer/src/PHPMailer.php';
    require_once LIB_PATH.'/PHPmailer/src/SMTP.php';
    require_once ABS_PATH.'/emails.php';

    $mail = new PHPMailer;
    //$mail->SMTPDebug = 3;
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host =  $GLOBALS['SMTPHost'];                  // Specify main and backup SMTP servers 
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $GLOBALS['SMTPUsername'];           // SMTP username
    $mail->Password = $GLOBALS['SMTPPassword'];           // SMTP password
    $mail->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to 465 (SSL) o 587 (TLS)
    
    $mail->setFrom('amministrazione_futurelabs@itisavogadro.it', 'Avo Wi-Fi');
    $mail->addAddress($email, $fullName);                     // Add a recipient
    
    $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
    //$mail->addAttachment($file,$name);            // Optional name
    $mail->isHTML(true);                                  // Set email format to HTML
    
    $mail->Subject = $title;
    $mail->Body    = str_replace('[[Msg]]', $body, str_replace('[[Title]]', $title, $mainBodyEmail));
    
    if(!$mail->send()) 
        return false;

    return true;
}

/**
*   Builds the base URL of the site and return it
*   
*   @return string base URL of the site
*/
function baseUrl() {
    $url = null;

    // as default it's just the current URL
    if( !$url ) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        $url = $protocol . $_SERVER['HTTP_HOST'] . innerJoinURL($_SERVER['REQUEST_URI'],str_replace('\\', '/', dirname(__DIR__)));
    }

    return trim($url);
}

/**
*   Given two URLs returns a URL with the only parts in common between the two URLs
*   
*   @return string the URL with only the parts in common
*/
function innerJoinURL($str1,$str2) {
    $str1Splitted = explode("/", $str1);
    $str2Splitted = explode("/", $str2);

    return "/".implode("/",array_intersect($str1Splitted, $str2Splitted))."/";
}

function privDomainDuration($email) {
    foreach($GLOBALS['priviDoms'] as $duration => $privDomains) {
        foreach($privDomains as $privDomain)
            if(strpos(explode('@', $email)[1], $privDomain) !== false)
                return $duration;
    }

    return 1;
}

/**
*   given a CSV file check if it is valid and return an array of fields
*   Params: 
*       @param string $file path of the file
*       @param string $name the name of the file
*       @param string $type the type of the file 
*       @return string|array string if there are errors else return an array of field 
*/
function checkCSV($file, $name = "", $type = "text/csv") {
    $name = $name === "" ? $name : $file;

    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
    
    $ret = true;
    $checkedField = 0;
    $fieldsName = [];
    $linea = 1; // it contain the line where the program is

    if(!is_readable($file))
        $ret = "0";
    else if(!in_array($type,$csvMimes))
        $ret = "2";
    else if(empty($file))
        $ret = "3";
    else {
        $separator = findSeparator($file);

        if(($f = fopen($file,"r")) !== null) {

            $data = fgetcsv($f,10000,$separator);
            //checks if there at least the mandatory fields
            foreach($data as $field) {
                $field = trim($field);
                $field = strtoupper($field);

                if($field == "TOKEN" || $field == "DURATA")
                    $checkedField++; 

                array_push($fieldsName,$field);
            }
            if($checkedField != 2)
                    $ret = "8,"."0";
            
            while(($data = fgetcsv($f,10000,$separator)) && $ret === true) {

                if(strlen($data[array_search('TOKEN', $fieldsName)]) === 0)
                    $ret = "4,".$linea + 1;

                $durata = (int) $data[array_search('DURATA', $fieldsName)];
                if($durata < 1 || ($durata !== 1 && !in_array($durata, array_keys($GLOBALS['priviDoms']))))
                    $ret = "5,".$linea + 1;

                $linea++;
            }

            fclose($f);
        }
    }


    return $ret === true ? $fieldsName : $ret;
}

function uploadTokensByCsv($file, $csvFileds) {
    $db = DB::instace();
    $f = fopen($file,"r");
    $separator = findSeparator($file);
    $data = fgetcsv($f,10000,$separator); // skip heading
    
    $tokenValue = '';
    $tokenDuration = 0;

    while(($data = fgetcsv($f,10000,$separator))) {
        $tokenValue = $data[array_search('TOKEN', $csvFileds)];
        if($db->tokenAlreadyExists($tokenValue))
            continue;

        $tokenDuration = $data[array_search('DURATA', $csvFileds)];
        $db->addToken($tokenValue, $tokenDuration);
    }

    fclose($f);

    $db->clearUnusableTokens();
}

/**
*   given a file path it serch for the separator, it do so by dividing with evry possible 
*   separator, the separator that can divide the line in most part will be the separator of the CSV
*   Params: 
*       @param string $file - path of the file
*       @return string - the separator
*/
function findSeparator($file)
{
    $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

    $handle = fopen($file, "r");
    $firstLine = fgets($handle);
    fclose($handle); 
    foreach ($delimiters as $delimiter => &$count) {
        $count = count(str_getcsv($firstLine, $delimiter));
    }

    return array_search(max($delimiters), $delimiters);
}