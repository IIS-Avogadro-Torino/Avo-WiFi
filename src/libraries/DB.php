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
 * Class to deal with the DataBase
 * 
 * @author Marco Schiavello
 */
class DB {
    /**
     * the static instance of the class in the singleton pattern
     */
    private static $instance;
    
    /**
     * the connetion with the database used in the whole class
     */
    private $conn;

    /**
     * construstor of the class, put private to follow the singleton pattern
     */
    private function  __construct() {
        $this->conn = mysqli_connect($GLOBALS["location"], $GLOBALS["username"], $GLOBALS["password"], $GLOBALS["database"]);
    }

    /**
     * static method to instantiate or return the static instance
     */
    public static function instace() {
        if(empty(self::$instance))
            self::$instance = new DB();

        return self::$instance;
    }

    /**
    *   Shortcut for query the DB
    *
    *   @param string $query the query that needs to be executed
    *   @return mysqli_result|bool the result of the query
    */
    function query($query) { return mysqli_query($this->conn,$query); }
    
    /**
    *   Shortcut for sanitize a string
    *
    *   @param string $str the string that needs to be cleared
    *   @return string - sanitized string
    */
    function clearStr($str) { return is_numeric($str) ? $str : mysqli_real_escape_string($this->conn,$str); }

    /**
     * Query-builder function that creates a simple select query based on the arguments gived
     * 
     * @param array $fields an array of string that rappresents the fields of a table and an array of array if you want to specify the alias
     * @param string|array $table the table from wich the data are selected, if array is given its will fetch from multiples tables
     * @param array $where an associative array with the key being the field to check and the value the value of the field
     * @param string $more a string to add more specification to the query
     * @param string $op operand used between the field of a table and the value to check
     * @param string $logicalOp logical operand to use between two statements
     * @return mysqli_result|bool the resoult of the query
     */
    function genericSimpleSelect($fields, $table, $where, $more = '', $op = '=', $logicalOp = 'AND') {
        $whereString = '';
        $fieldsString = array_reduce($fields,
                                     function ($carry, $field) {
                                        return $carry .= (is_array($field) ? $field[0] . ' AS ' . $field[1] : $field ) . ', ';
                                     } );

        foreach(array_keys($where) as $key) {
            $whereValue = $this->clearStr($where[$key]);
            $whereString .= ($key . " $op " . (is_numeric($whereValue) ? $whereValue : '\'' . $whereValue . '\'') . " $logicalOp "); 
        }

        if(is_array($table)) {
            $table = array_reduce($table,
                                  function ($carry, $single_table) {
                                        return $carry .= $single_table . ', ';
                                  } );

            $table = substr($table, 0, -2);
        }

        $fieldsString = substr($fieldsString, 0, -2);
        $whereString = substr($whereString, 0, -5);

        return $this->query("SELECT $fieldsString 
                             FROM $table
                             WHERE $whereString $more;");
    }

    /**
     * Query-builder function that creates a simple insert query based on the arguments gived
     * 
     * @param array $fields an array of string that rappresents the fields of a table and an array of array if you want to specify the aliases
     * @param string $table the table in wich the data are inserted
     * @param array $where an associative array with the key being the field to check and the value the value of the field
     * @param string $more a string to add more specification to the query
     * @return mysqli_result|bool the resoult of the query
     */
    function genericSimpleInsert($fields, $table, $more = '') {
        $fieldNames = '';
        $fieldValues = '';

        foreach(array_keys($fields) as $key) {
            $fieldNames .= ($key . ', ');
            $fieldValue = $this->clearStr($fields[$key]);
            $fieldValues .= ((is_numeric($fieldValue) ? $fieldValue : '\'' . $fieldValue . '\'') . ', ');
        }

        $fieldNames = substr($fieldNames, 0, -2);
        $fieldValues = substr($fieldValues, 0, -2);

        return $this->query("INSERT INTO $table($fieldNames)
                             VALUES ($fieldValues) $more;");
    }

    /**
     * Query-builder function that creates a simple delete query based on the arguments gived
     * 
     * @param array $fields an array of string that rappresents the fields of a table and an array of array if you want to specify the alias
     * @param string $table the table from wich the data are deleted
     * @param array $where an associative array with the key being the field to check and the value the value of the field
     * @param string $more a string to add more specification to the query
     * @param string $op operand used between the field of a table and the value to check
     * @param string $logicalOp logical operand to use between two statements
     * @return mysqli_result|bool the resoult of the query
     */
    function genericSimpleDelete($where, $table, $more = '', $op = '=', $logicalOp = 'AND') {
        $whereString = '';

        foreach(array_keys($where) as $key) {
            $whereValue = $this->clearStr($where[$key]);
            $whereString .= ($key . " $op " . (is_numeric($whereValue) ? $whereValue : '\'' . $whereValue . '\'') . " $logicalOp "); 
        }

        $whereString = substr($whereString, 0, -5);

        return $this->query("DELETE FROM $table
                             WHERE $whereString $more;");
    }

    /**
     * Deletes the exprired token from the DB
     * 
     * @param int $userId the ID of the user from wich the function will delete the expired tokens
     * @param string $type the type of the token to check for expiration, type aviable ```'auth'```, ```'wifi'```
     * @return mysqli_result|bool the resoult of the query 
     */
    private function deleteExpiredToken($userId, $type) {
        return $this->genericSimpleDelete(array('fk_user_id' => $userId), 
                                          $type === 'auth' ? 'auth_codes' : ( $type === 'wifi' ? 'tokens' : ''),
                                          "AND " . ($type === 'auth' ? 'token_expiring_date' : ($type === 'wifi' ? 'code_expiring_date' : '')) . " < NOW()");
    }
 
    /**
     * Retrives the user data with the wifi token value
     * 
     * @param int $userId the ID of the user from wich the function will retrive the data
     * @return array an associative array with all the user data
     */
    function getUserInfoById($userId) {
        $userQuery = $this->genericSimpleSelect([ [ 'user_name', 'name' ],
                                                  [ 'user_surname', 'surname' ],
                                                  [ 'user_email', 'email' ],
                                                  [ 'token_value', 'token' ] ], 
                                                [ 'users', 'tokens' ], 
                                                array( 'user_id' => $userId),
                                                'ORDER BY token_expiring_date DESC LIMIT 1');

        $user = mysqli_fetch_assoc($userQuery);

        return $user;
    }

    /**
     * Sees if an auth token is present in the DB
     * 
     * @param string $authCode the auth code to verify the wifi token request
     * @return array an associative array with the resoult of the lookup in the DB of the authcode and the user ID associated
     */
    function isValidAuthCode($authCode) {
        $queryRes = $this->genericSimpleSelect([ 'fk_user_id' ],
                                               'auth_codes',
                                                array( 'auth_code_value' => $authCode));
            
        $userId = mysqli_fetch_array($queryRes)[0];

        $this->deleteExpiredToken($userId, 'auth');

        $queryRes = $this->genericSimpleSelect([ 'COUNT(*)' ],
                                               'auth_codes',
                                                array( 'auth_code_value' => $authCode));
        
        $numAuthCode = mysqli_fetch_array($queryRes)[0];

        return array('valid' => ($numAuthCode == 1), 'userId' => $userId);
    }

    /**
     * Assign a ramdom token form the DB and bind it with a user 
     * 
     * @param int $userId the user ID that requests the token 
     * @return mysqli_result|bool the resoult of the query 
     */
    function assignToken($userId, $duration) {
        $duration = $this->clearStr($duration);
        $sanUserId = $this->clearStr($userId);

        return $this->query("UPDATE tokens 
                             SET fk_user_id = $sanUserId, token_expiring_date = (NOW() + INTERVAL token_duration DAY) 
                             WHERE fk_user_id IS NULL AND token_duration = ".$duration." LIMIT 1;");
    }

    /**
     * Retuns the number of token associated with a user, the type of token is specified in the arguments
     * 
     * @param int $userId the user ID that requests the token 
     * @param string $type indicates the tyoe of the token, type aviable ```'auth'```, ```'wifi'``` 
     * @return int numer of token found in the DB binded with the user
     */
    function numberOfToken($userId, $type) {
        $this->deleteExpiredToken($userId, $type);
        
        $queryRes = $this->genericSimpleSelect([ 'COUNT(*)' ],
                                               $type === 'auth' ? 'auth_codes' : ( $type === 'wifi' ? 'tokens' : ''),
                                               array('fk_user_id' => $userId));

        return mysqli_fetch_array($queryRes)[0];
    }

    function numberRemainingToken($duration) {
        $duration = $this->clearStr($duration);
        $queryRes = $this->genericSimpleSelect([ 'COUNT(*)' ],
                                               'tokens',
                                               [], 
                                               "fk_user_id IS NULL AND token_duration = ".$duration);

        return mysqli_fetch_array($queryRes)[0];
    }

    function adminLogin($email, $password) {
        $email = $this->clearStr($email);
        $password = $this->clearStr($password);

        $queryRes = $this->genericSimpleSelect(['user_pwd'], [ 'users' ], array('user_email' => $email, 'user_admin' => true), '', '=', 'AND');

        $queryRes = mysqli_fetch_array($queryRes)[0];

        $salt = explode('.', $queryRes)[1];
        $pwd = explode('.', $queryRes)[0];

        return $pwd === md5($password.$salt);
    }

    /*
    function addAdmin($nome, $cognome, $email, $password) {
        $salt = bin2hex(random_bytes(30));
        $password = md5($password.$salt).'.'.$salt;

        $this->genericSimpleInsert(array('user_name' => $nome,
                                         'user_surname' => $cognome,
                                         'user_email' => $email,
                                         'user_admin' => true,
                                         'user_pwd' => $password), 'users');
    }*/

    function tokenAlreadyExists($token) {
        $token = $this->clearStr($token);
        $queryRes = $this->genericSimpleSelect([ 'COUNT(*)' ],
                                               'tokens',
                                               array('token_value' => $token));

        return mysqli_fetch_array($queryRes)[0] === 1;
    }

    function addToken($value, $duration) {
        $value = $this->clearStr($value);
        $duration = $this->clearStr($duration);

        $this->genericSimpleInsert(array( 'token_value' => $value, 
                                          'token_duration' => $duration), 'tokens');
    }

    function clearUnusableTokens() {

        $durations = array_reduce(array_keys($GLOBALS['priviDoms']), function($carry, $ele) { 
                                                                        return $carry .= ($ele.', ');
                                                                     });
        $this->genericSimpleDelete( [],
                                   'tokens', 
                                   'fk_user_id IS NULL AND token_duration NOT IN ('.substr($durations, 0, strlen($durations) - 2).')');
    }
}