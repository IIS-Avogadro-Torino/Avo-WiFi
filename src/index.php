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

include_once 'load.php';
include_once LIB_PATH.'/library.php';

if(isset($_POST['indexSubmit'])) {
    $db = DB::instace();
    $authCode = strtoupper(bin2hex(random_bytes(30)));
    $email = $db->clearStr(trim($_POST['email']));
    $name = $db->clearStr(trim($_POST['name']));
    $surname = $db->clearStr(trim($_POST['surname']));

    $queryRes = $db->query("SELECT COUNT(*), user_id
                            FROM users
                            WHERE user_email = '$email';");
    $queryRes = mysqli_fetch_array($queryRes);

    if((int)$queryRes[0] !== 1) {
        $db->query("INSERT INTO users(user_name, user_surname, user_email) 
                    VALUES('$name', '$surname', '$email');");
    }

    $queryRes = $db->query("SELECT user_id
                            FROM users
                            WHERE user_email = '$email';");
    $queryRes = mysqli_fetch_array($queryRes)[0];

    $db->query("INSERT INTO auth_codes(auth_code_value, fk_user_id) 
                VALUES('$authCode', $queryRes);");

    $emailRes = sendMail($email, 
                         $name.' '.$surname,
                         'Avo Wi-Fi - Autenticazione',
                         'Gentile '.$name.' '.$surname.', il suo codice è: <br> <strong>'.$authCode.'</strong> <br> o <a href="'.baseUrl().'authCode.php?authCode='.$authCode.'">Clicca Qui</a>');

    if(!$emailRes) {
        header('Location: index.php');
        die();
    }

    header('Location: authCode.php');
    die();
}

printHead('Richiesta Token', 
          [ 'style.css' ],
          [ ],
          false);
?>

<?php include_once COMP_PATH.'/logoBox.php';?>

<div class="page-cont container container--gapM page-size">
    <h1 class="page-cont__title">Richiedi il tuo token</h1>
    
    <form class="page-cont__form container container--gapS" method="POST" action="index.php">
        <?php 
            inputText("name", "Nome"); 
            inputText("surname", "Cognome"); 
            inputText("email", "E-Mail"); 
        ?>
        <input class="button" type="submit" name="indexSubmit" value="Richiedi">
    </form>
</div>

<?php printFooter(); ?>
