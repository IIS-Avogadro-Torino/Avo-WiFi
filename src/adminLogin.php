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

require_once 'load.php';
require_once LIB_PATH.'/library.php';

$errors = array( 1 => 'email o password sono sbagliati' );

if(isset($_POST['adminLoginSubmit'])) {
    $db = DB::instace();

    if($db->adminLogin($_POST['email'], $_POST['password'])) {
        $_SESSION['isLogged'] = true; 
        header('Location: csvUpload.php');
        die();
    } else {
        header('Location: adminLogin.php?err=1');
        die();
    }
}

printHead('Admin Login', 
          [ 'style.css' ],
          [ ],
          true);
?>

<div class="page-cont container container--gapM page-size">
    <h1 class="page-cont__title">Accedi area admin</h1>
    
    <form class="page-cont__form container container--gapXS" method="POST" action="adminLogin.php">
        <?php 
            inputText("email", "E-mail"); 
            inputText("password", "Password", "password"); 
        ?>
        <input class="button button--marginTop" type="submit" name="adminLoginSubmit" value="Login">
        <?php echo isset($_GET['err']) ? '<h5 class="err">' . $errors[$_GET['err']] . '</h5>' : '' ?>
    </form>
</div>

<?php printFooter(); ?>
