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
function sendMail($email, $fullName, $body)
{
    require_once LIB_PATH.'/PHPmailer/src/Exception.php';
    require_once LIB_PATH.'/PHPmailer/src/PHPMailer.php';
    require_once LIB_PATH.'/PHPmailer/src/SMTP.php';

    $mail = new PHPMailer;
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host =  $GLOBALS['SMTPHost'];                  // Specify main and backup SMTP servers 
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $GLOBALS['SMTPUsername'];           // SMTP username
    $mail->Password = $GLOBALS['SMTPPassword'];           // SMTP password
    $mail->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to 465 (SSL) o 587 (TLS)
    
    $mail->setFrom('some@email.com','Avo Wi-Fi');
    $mail->addAddress($email, $fullName);                     // Add a recipient
    
    $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
    //$mail->addAttachment($file,$name);            // Optional name
    $mail->isHTML(true);                                  // Set email format to HTML
    
    $mail->Subject = 'Avo Wi-Fi - token autenticazione';
    $mail->Body    = $body;
    
    if(!$mail->send()) 
        return false;

    return true;
}
