<?php
require 'config/config.php';


if (isset($_SESSION['user_id'])) {

    
    if ($_SESSION['role'] === 'admin') {
        header("Location: pages/admin.php");
    } else {
        header("Location: pages/dashboard.php");
    }

} else {

    header("Location: auth/login.php");
}

exit();