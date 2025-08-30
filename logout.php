<?php
session_start();
include_once 'includes/functions.php'; // <--- TAMBAHKAN BARIS INI
session_unset();
session_destroy();
redirect('login.php');
?>