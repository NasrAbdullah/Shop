<?php
session_start();

// 1. مسح جميع متغيرات الجلسة
$_SESSION = array();

// 2. تدمير الجلسة بالكامل من السيرفر
session_destroy();

// 3. إعادة التوجيه للصفحة الرئيسية
header("Location: index.php");
exit();
?>
