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

/**
 * Prints the head and the nav if specified
 * Param:
 *  @param string $pageName the name of page that will displays on the tab
 *  @param array $cssFiles CSS file to include 
 *  @param array $jsFiles JS file to include 
 *  @param bool $nav indicates the presence of the nav-bar
 *  @return void
 */
function printHead($pageName, $cssFiles = [], $jsFiles = [], $nav = true) {
    $cssIncludes = '';
    $jsIncludes = '';
    
    foreach( $cssFiles as $cssFile )
        $cssIncludes .= "<link rel=\"stylesheet\" href=\"assets/css/$cssFile\">\n";
    
    foreach( $jsFiles as $jsFile )
        $jsIncludes .= "<script src=\"assets/css/$jsFile\" defer></script>\n";

    print "
    <!DOCTYPE html>
    <html lang=\"it\">
    <head>
        <meta charset=\"UTF-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <link rel=\"icon\" href=\"assets/img/logo.png\">
        <!-- CSS -->
        $cssIncludes
        <!--------->
        <!-- JS -->
        $jsIncludes
        <!-------->
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>$pageName</title>
    </head>
    <body>";

    if($nav)
        include_once  COMP_PATH.'/nav.php';
    
    print '<main>';
}

/**
 * Prints the end of the page with the footer
 * Param:
 *  @return void
 */
function printFooter() {
    print '</main>';

    include_once  COMP_PATH.'/footer.php';

    print "</body>
           </html>";
}
