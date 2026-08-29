<?php
// this is variable about is there any session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " — NasSee" : "NasSee"; ?></title>

    <!-- Google Fonts (Cairo) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- هيدر المتجر الرئيسي الموحد -->
<header class="main-site-header">
    <div class="header-container">
        
        
        <!-- روابط الملاحة -->
        <nav class="site-nav">
            <a href="index.php" class="nav-link">الرئيسية 🏠</a>
            <a href="products.php" class="nav-link">كل المنتجات 🛍️</a>
            
            <a href="cart.php" class="nav-link cart-link">
                السلة 🛒
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="my_orders.php" class="nav-link">طلباتي 📦</a>
                
                <!-- زر الأدمن يظهر فقط لمن يملك دور admin -->
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin/index.php" class="nav-link admin-btn">لوحة الأدمن ⚙️</a>
                <?php endif; ?>

                <a href="logout.php" class="nav-link logout-btn">خروج 🚪</a>
            <?php else: ?>
                <a href="login.php" class="nav-link login-btn">دخول 🔑</a>
            <?php endif; ?>
             <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-welcome">
                    👋 مرحباً، <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                </span>

            <?php endif; ?>
        </nav>

        <!-- اللوجو -->
        <a href="index.php" style="float: left;" class="site-logo">
            NASSEE <span>STORE</span>
        </a>
    </div>
    </header>

<!-- تنسيقات الهيدر الزجاجية -->
<style>
.main-site-header {
    background: rgba(10, 10, 20, 0.85);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 243, 255, 0.15);
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 0.8rem 1.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.site-logo {
    font-size: 1.6rem;
    font-weight: 900;
    color: #fff;
    text-decoration: none;
    letter-spacing: 1px;
}

.site-logo span {
    color: #00f3ff;
    text-shadow: 0 0 10px rgba(0, 243, 255, 0.5);
}

.site-nav {
    display: flex;
    gap: 1.2rem;
    align-items: center;
}

.nav-link {
    color: #a0a0b5;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    position: relative;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
}

.nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.05);
}

.cart-link {
    position: relative;
}

.cart-badge {
    position: absolute;
    top: -5px;
    left: -5px;
    background: #ff007f;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 50px;
    box-shadow: 0 0 10px rgba(255, 0, 127, 0.6);
}

.admin-btn {
    color: #00f3ff !important;
    border: 1px solid rgba(0, 243, 255, 0.3);
    background: rgba(0, 243, 255, 0.05);
}

.admin-btn:hover {
    background: #00f3ff !important;
    color: #000 !important;
    box-shadow: 0 0 15px rgba(0, 243, 255, 0.5);
}

.logout-btn { color: #ff4757 !important; }
.login-btn { 
    background: linear-gradient(135deg, #00f3ff, #ff007f); 
    color: #fff !important; 
    border-radius: 10px;
}
</style>

