<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// حساب عدد العناصر في السلة لإظهاره كشارّة (Badge) فوق أيقونة السلة
$cart_count = 0;
if (isset($_SESSION['user_id']) && isset($conn)) {
    $u_id = $_SESSION['user_id'];
    $c_res = mysqli_query($conn, "SELECT SUM(quantity) as total_qty FROM cart_items WHERE user_id = '$u_id'");
    if ($c_res) {
        $c_row = mysqli_fetch_assoc($c_res);
        $cart_count = $c_row['total_qty'] ?? 0;
    }
}
?>

<!-- هيدر المتجر الرئيسي الموحد -->
<header class="main-site-header">
    <div class="header-container">
        
        <!-- اللوجو -->
        <a href="index.php" class="site-logo">
            NASSEE <span>STORE</span>
        </a>

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
        </nav>

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
