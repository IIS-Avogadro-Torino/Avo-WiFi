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

use PHPMailer\PHPMailer\PHPMailer;// namespaces for PHPmailer
use PHPMailer\PHPMailer\Exception;

include_once LIB_PATH.'/UILibrary.php';
require_once LIB_PATH."/DB.php";

/**
 * 
 */
function sendMail($email, $fullName, $title, $body)
{
    require_once LIB_PATH.'/PHPmailer/src/Exception.php';
    require_once LIB_PATH.'/PHPmailer/src/PHPMailer.php';
    require_once LIB_PATH.'/PHPmailer/src/SMTP.php';


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
    $mail->Body    = $body;
    
    if(!$mail->send()) 
        return false;

    return true;
}

/**
*   builds the base URL of the site and return it
*   Params: 
*       @return string base URL of the site
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
*   given two URLs returns a URL with the only parts in common between the two URLs
*   Params: 
*       @return string the URL with only the parts in common
*/
function innerJoinURL($str1,$str2) {
    $str1Splitted = explode("/", $str1);
    $str2Splitted = explode("/", $str2);

    return "/".implode("/",array_intersect($str1Splitted, $str2Splitted))."/";
}
