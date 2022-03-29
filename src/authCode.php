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

if(isset($_POST['authCodeSubmit']) || isset($_GET['authCode'])) {
    $db = DB::instace();
    $authCode = $db->clearStr(!isset($_GET['authCode']) ? $_POST['authCode'] : $_GET['authCode']);

    $queryRes = $db->isValidAuthCode($authCode);

    if($queryRes['valid']) {
        $db->genericSimpleDelete(array('fk_user_id' => $queryRes['userId']), 'auth_codes');

        $db->assignRandomToken($queryRes['userId']);

        $user = $db->getUserInfoById($queryRes['userId']);
        
        $emailRes = sendMail($user['email'], 
                             $user['name'].' '.$user['surname'],
                             'Avo Wi-Fi - Token',
                             'Gentile '.$user['name'].' '.$user['surname'].', il suo token per l\'accesso alla rete Wi-Fi dell\'Avogadro è: <br> <strong>'.$user['token'].'</strong>');
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
        if((isset($_POST['authCodeSubmit']) || isset($_GET['authCode'])) && (int) $queryRes['valid'] === 1) {
    ?>

    <h1 class="page-cont__title">Token inviato con successo</h1>

    <p>
        Ciao <?php echo $user['name'].' '.$user['surname'] ?>, il tuo token per accedere al Wi-fi 
        è stato mandato alla seguente E-Mail ( controllare anche lo spam ):
    </p>
    <p><?php echo $user['email'];?></p>
        

    <?php
        } else {
    ?>
    
    <h1 class="page-cont__title">Immetti codice conferma</h1>
        
    <p>
        Ti è stata recapitata una email con un codice di autenticazione, immettilo qui sotto ( controllare anche lo spam )
    </p>

    <form class="page-cont__form container container--gapS" method="POST" action="authCode.php">
        <?php 
            inputText("authCode", "Codice autenticazione"); 
        ?>
        <input class="button" type="submit" name="authCodeSubmit" value="Verifica">
    </form>

    <?php } ?>
</div>

<?php printFooter(); ?>
