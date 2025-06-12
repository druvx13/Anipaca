<?php 
session_start();
session_destroy(); // Destroy the session first

if(isset($_COOKIE['userID'])){ 
    // No need to use $user_id from cookie if we are just unsetting it
    setcookie('userID', '', time() - 3600, '/'); // Unset the cookie
};
header('location:../../home');
exit(); // Good practice to add exit after redirect
?>