<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// // حماية الصفحة: التأكد من تسجيل الدخول وأن الحساب ملك أدمن
// if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: ../login.php");
//     exit();
// }

// معرفة اسم الصفحة الحالية لإعطاء خلفية مميزة للرابط النشط
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- هيدر لوحة الأدمن الموحد -->
<header class="admin-site-header">
    <div class="admin-header-container">
        
        <!-- شعار لوحة التحكم -->
        <a href="index.php" class="admin-logo">
            NASSEE <span>ADMIN ⚙️</span>
        </a>

        <!-- روابط القائمة الرئيسية للأدمن -->
        <nav class="admin-nav">
            <a href="index.php" class="admin-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">الرئيسية 📊</a>
            <a href="products.php" class="admin-link <?php echo ($current_page == 'products.php' || $current_page == 'add-product.php') ? 'active' : ''; ?>">إدارة المنتجات 📦</a>
            <a href="add-product.php" class="admin-link <?php echo ($current_page == 'add-product.php') ? 'active' : ''; ?>">إضافة منتج ➕</a>
            <a href="orders.php" class="admin-link <?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">إدارة الطلبات 🛒</a>
        </nav>

        <!-- أدوات التحكم والعودة للمتجر -->
        <div class="admin-actions">
            <a href="../index.php" class="btn-store-view" target="_blank">معاينة المتجر 🌐</a>
            <a href="../logout.php" class="btn-admin-logout">خروج 🚪</a>
        </div>

    </div>
</header>

<!-- التنسيقات البرمجية الخاصة بهيدر الأدمن -->
<style>
.admin-site-header {
    background: rgba(12, 12, 22, 0.95);
    backdrop-filter: blur(15px);
    border-bottom: 2px solid rgba(255, 0, 127, 0.3);
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 0.8rem 2rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7);
}

.admin-header-container {
    max-width: 1300px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-logo {
    font-size: 1.5rem;
    font-weight: 900;
    color: #fff;
    text-decoration: none;
    letter-spacing: 1px;
}

.admin-logo span {
    color: #ff007f;
    text-shadow: 0 0 10px rgba(255, 0, 127, 0.6);
}

.admin-nav {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.admin-link {
    color: #a0a0b5;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.admin-link:hover {
    color: #00f3ff;
    background: rgba(0, 243, 255, 0.08);
}

.admin-link.active {
    color: #00f3ff;
    background: rgba(0, 243, 255, 0.15);
    border: 1px solid rgba(0, 243, 255, 0.3);
    box-shadow: 0 0 12px rgba(0, 243, 255, 0.2);
}

.admin-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.btn-store-view {
    color: #fff;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 700;
    padding: 0.4rem 0.9rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-store-view:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #00f3ff;
}

.btn-admin-logout {
    color: #ff4757;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 700;
    padding: 0.4rem 0.9rem;
    border: 1px solid rgba(255, 71, 87, 0.3);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-admin-logout:hover {
    background: #ff4757;
    color: #fff;
    box-shadow: 0 0 10px rgba(255, 71, 87, 0.5);
}
</style>
