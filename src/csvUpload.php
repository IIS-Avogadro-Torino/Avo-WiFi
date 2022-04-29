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

if(!isset($_SESSION['isLogged']) || !$_SESSION['isLogged']) {
    header('Location: index.php');
    die();
}

require_once 'load.php';
require_once LIB_PATH.'/library.php';

$errors = array( 0 => 'errore nella lettura del file',
                 2 => 'il tipo del file non è csv',
                 3 => 'il file è vuoto',
                 4 => 'token non valido in linea '.$_GET['linea'],
                 5 => 'durata del token non valida o non ricade in nessun dominio privilegiato settato nel file load.php e non è guale a 1 in linea '.$_GET['linea'],
                 8 => 'intestazione non valida in linea 0, deve aevere almeno un campo token e durata');

if(isset($_POST['csvUploadSubmit'])) {
    $csvFields = checkCSV($_FILES['tokenCSV']['tmp_name'], $_FILES['tokenCSV']['name']);

    if(is_array($csvFields)) {
        uploadTokensByCsv($_FILES['tokenCSV']['tmp_name'], $csvFields);
    } else {
        $err = explode(',', $csvFields);
        header('Location: csvUpload.php?err='.$err[0].(isset($err[1]) ? '&linea='.$err[1] : ''));
        die();
    }
}

printHead('Carica con CSV', 
          [ 'style.css' ],
          [ 'uploadButton.js' ],
          true);
?>

<div class="page-cont container container--gapM page-size">
    <h1 class="page-cont__title">Carica con CSV</h1>
    
    <form class="page-cont__form container container--gapXS" method="POST" action="csvUpload.php" enctype="multipart/form-data">
        <p>Caricare un CSV con una colonna chiamata "codice" con il codice e un altra chimata "durata" con la durata del codice (ogni altra colonna sara ignorata)</p>
        <?php 
            inputFile("tokenCSV", "carica CSV", ".csv"); 
        ?>
        <input class="button button--marginTop" type="submit" name="csvUploadSubmit" value="Carica Token">
        <?php echo isset($_GET['err']) ? '<h5 class="err">' . $errors[$_GET['err']] . '</h5>' : '' ?>
    </form>
</div>

<?php printFooter(); ?>
