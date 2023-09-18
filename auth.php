<?php
/**
 * get responce to ajax-query for user authorization events
 */
namespace testAuthForm;

session_start();

/**
* include autoloader
*/
require_once 'Autoloader.php';

/**
* get data from $_POST
*/
$email  = filter_input(INPUT_POST, 'email');
$pass   = filter_input(INPUT_POST, 'pass');
$code   = filter_input(INPUT_POST, 'code');
$logout = filter_input(INPUT_POST, 'logout');
$info   = filter_input(INPUT_POST, 'info');

/**
 * create new Authorization object
 */
$user = new Authorization();


/**
 * run requested method
 */
if ($email && $pass){
    $user->checkUser($email, $pass, $code);
} elseif ($logout) {
    $user->logout();
} elseif ($info) {
    $user->getAuthInfo();
} else {
    echo "Empty email or password";
}
