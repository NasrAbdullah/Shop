<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role   = isset($_SESSION['user_role']) ? strtolower(trim($_SESSION['user_role'])) : '';
$user_id = $_SESSION['user_id'] ?? null;

// if($role !== 'Admin')
//     header("Location: index.php");
// else{
//     header("Location: ../index.php");
// }
   
// if($role === 'Admin'){
//                     header("Location: Admin/index.php");
//                 }else{
//                      header("Location: index.php");
//                      exit();
//                 }    