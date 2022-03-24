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
 * 
 */
class DB {
    private static $instance;
    private $conn;

    private function  __construct() {
        $this->conn = mysqli_connect($GLOBALS["location"], $GLOBALS["username"], $GLOBALS["password"], $GLOBALS["database"]);
    }

    public static function instace() {
        if(empty(self::$instance))
            self::$instance = new DB();

        return self::$instance;
    }

    /**
    *   Shortcut for query the DB
    *   Params: 
    *       @return mysqli_result|bool - the result of the query
    */
    function query($query) { return mysqli_query($this->conn,$query); }
    
    /**
    *   Shortcut for sanitize a string
    *   Params: 
    *       @return string - sanitized string
    */
    function clearStr($str) { return mysqli_real_escape_string($this->conn,$str); }
}