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

if(isset($_POST['authCodeSubmit'])) {
    $db = DB::instace();
    $authCode =  $db->clearStr(!isset($_GET['authCode']) ? $_POST['authCode'] : $_GET['authCode']);

    $queryRes = $db->query("SELECT COUNT(*), fk_user_id
                            FROM auth_codes
                            WHERE auth_code_value = '$authCode';");
    $queryRes = mysqli_fetch_array($queryRes);

    if((int) $queryRes[0] === 1) {
        $db->query("DELETE FROM auth_codes
                    WHERE fk_user_id = $queryRes[1];");

        $db->query("UPDATE tokens 
                    SET fk_user_id = $queryRes[1], token_expiring_date = (NOW() + INTERVAL token_duration DAY) 
                    WHERE fk_user_id IS NULL LIMIT 1;");


        $queryRes2 = $db->query("SELECT user_name AS name, user_surname AS surname, user_email AS email                                
                                FROM users
                                WHERE user_id = $queryRes[1];");

        $user = mysqli_fetch_assoc($queryRes2);

        $queryRes2 = $db->query("SELECT token_value AS token                                
                                 FROM tokens
                                 WHERE fk_user_id = $queryRes[1]
                                 ORDER BY token_expiring_date DESC LIMIT 1;");

        $token = mysqli_fetch_array($queryRes2)[0];
        
        $emailRes = sendMail($user['email'], 
                             $user['name'].' '.$user['surname'],
                             "Gentile ".$user['name'].' '.$user['surname'].", il suo token per l'accesso alla rete Wi-Fi dell'Avogadro è: <br> <strong>$token</strong>");

    }
}

printHead('Autenticazione', 
          [ 'style.css' ],
          [ ],
          false);
?>

<?php include_once COMP_PATH.'/logoBox.php';?>

<div class="page-cont container container--gapM page-size">
    
    <?php
        if(isset($_POST['authCodeSubmit']) && (int) $queryRes[0] === 1) {
    ?>

        <h1 class="page-cont__title">Token inviato con successo</h1>
    
        <p>
            Ciao <?php echo $user['name'].' '.$user['surname'] ?>, il tuo token per accedere al Wi-fi 
            è stato mandato alla seguente E-Mail:
        </p>
        <p><?php echo $user['email']."  "."$token";?></p>
        

    <?php
        } else {
    ?>
    
    <h1 class="page-cont__title">Immetti codice conferma</h1>

    <form class="page-cont__form container container--gapS" method="POST" action="authCode.php">
        <?php 
            inputText("authCode", "Codice autenticazione"); 
        ?>
        <input class="button" type="submit" name="authCodeSubmit" value="Verifica">
    </form>

    <?php } ?>
</div>

<?php printFooter(); ?>