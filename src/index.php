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

    $email = trim($_POST['email']);
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);

    $queryRes = $db->genericSimpleSelect([ 'COUNT(*)' ], 'users', array( 'user_email' => $email));

    $numUser = (int) mysqli_fetch_array($queryRes)[0];

    if($numUser !== 1) {
        $db->genericSimpleInsert(array('user_name' => $authCode,
                                       'user_surname' => $numUser,
                                       'user_email' => $email), 'users');
    }

    $queryRes = $db->genericSimpleSelect([ 'user_id' ], 'users', array( 'user_email' => $email));

    $userId = mysqli_fetch_array($queryRes)[0];

    if($db->numberOfToken($userId, 'auth') >= 4) {
        header('Location: index.php?err=1');
        die();
    } else if($db->numberOfToken($userId, 'wifi') >= 4) {
        header('Location: index.php?err=2');
        die();
    }

    $db->genericSimpleInsert(array('auth_code_value' => $authCode,
                                   'fk_user_id' => $userId), 'auth_codes');

    $emailRes = sendMail($email, 
                         $name.' '.$surname,
                         'Avo Wi-Fi - Autenticazione',
                         'Gentile '.$name.' '.$surname.', il suo codice è: <br> <strong>'.$authCode.'</strong> <br> o <a href="'.baseUrl().'authCode.php?authCode='.$authCode.'">Clicca Qui</a>');

    if(!$emailRes) {
        header('Location: index.php?err=3');
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
