<?php
require_once __DIR__ . "/../config/database.php";
require_once "auth_check.php";

// 1. حذف منتج
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $img_res = mysqli_query($conn, "SELECT image FROM products WHERE id = $delete_id");
    if ($img_row = mysqli_fetch_assoc($img_res)) {
        if (!empty($img_row['image']) && file_exists("../images/" . $img_row['image'])) {
            unlink("../images/" . $img_row['image']);
        }
    }
    mysqli_query($conn, "DELETE FROM products WHERE id = $delete_id");
    header("Location: index.php");
    exit();
}

// 2. إحصائيات سريعة للوحة
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
$total_users    = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));
$total_val_res  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(price) as total FROM products"));
$total_value    = $total_val_res['total'] ?? 0;

// 3. جلب جميع المنتجات
$products_res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

//====================================================================================================
// 1. إجمالي المبيعات والأرباح
$total_sales_q = mysqli_query($conn, "SELECT SUM(total) AS total_revenue FROM orders WHERE status = 'completed'");
$total_revenue = mysqli_fetch_assoc($total_sales_q)['total_revenue'] ?? 0;

// 2. المبيعات المحتملة (الطلبات المعلقة)
$pending_sales_q = mysqli_query($conn, "SELECT SUM(total) AS pending_revenue FROM orders WHERE status = 'pending'");
$pending_revenue = mysqli_fetch_assoc($pending_sales_q)['pending_revenue'] ?? 0;

// 3. أعداد الطلبات حسب الحالات
$orders_count_q = mysqli_query($conn, "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
FROM orders");
$orders_stat = mysqli_fetch_assoc($orders_count_q);

// 4. إحصائيات المنتجات والمخزون
// $products_stat_q = mysqli_query($conn, "SELECT 
//     COUNT(*) as total_products,
//     SUM(CASE WHEN stock <= 5 AND stock > 0 THEN 1 ELSE 0 END) as low_stock,
//     SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock
// FROM products");
// $products_stat = mysqli_fetch_assoc($products_stat_q);

// 5. إحصائيات العملاء والمستخدمين
$users_stat_q = mysqli_query($conn, "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as total_customers,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins
FROM users");
$users_stat = mysqli_fetch_assoc($users_stat_q);

// 6. أكثر منتج مبيعاً
$top_product_q = mysqli_query($conn, "SELECT p.name, SUM(oi.quantity) as total_sold 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    GROUP BY oi.product_id 
    ORDER BY total_sold DESC LIMIT 1");
$top_product = mysqli_fetch_assoc($top_product_q);
//============================================================
// حق نازل
//============================================================
// php echo $products_stat['out_of_stock']; 
// php echo $products_stat['low_stock']; 
?>

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مركز القيادة — NasSee Admin Nexus</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/admin.css">
</head>
<body class="admin-body">

<?php 
include_once "header.php";
?>
    <!-- خلفية الحبيبات النيونية المتحركة -->
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>
    <div class="ambient-glow glow-3"></div>

    <div class="admin-layout">
        <!-- شريط تنقل جانبي سريالي -->
        <aside class="sidebar-glass">
            <div class="brand">
                <div class="brand-icon">⚡</div>
                <h2>Nas<span>See</span> <small>NEXUS</small></h2>
            </div>

            <nav class="sidebar-menu">
                <a href="index.php" class="menu-item active">
                    <span class="icon">📊</span>
                    <span>مركز القيادة</span>
                </a>
                <a href="add-product.php" class="menu-item">
                    <span class="icon">✨</span>
                    <span>إضافة منتج خارق</span>
                </a>
                <a href="../index.php" target="_blank" class="menu-item">
                    <span class="icon">🌐</span>
                    <span>معاينة المتجر ↗</span>
                </a>
                <a href="orders.php" class="menu-item">
                    <span class="icon">🌐</span>
                    <span>إدارة الطلبات ↗</span>
                </a>
                <a href="../logout.php" class="menu-item logout">
                    <span class="icon">🚪</span>
                    <span>إنهاء الجلسة</span>
                </a>
            </nav>

            <div class="admin-profile-badge">
                <div class="avatar">👑</div>
                <div class="info">
                    <h4><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'الأدمن'); ?></h4>
                    <p>صلاحيات مطلقة</p>
                </div>
            </div>
        </aside>

        <!-- المحتوى الرئيسي للوحة -->
        <main class="main-content">
            
            <header class="top-nexus">
                <div>
                    <h1 class="glitch-text">مركز التحكم والتحليلات 🚀</h1>
                    <p class="subtitle">نظام إشراف مستقبلي لإدارة منتجات وتدفقات Nassee</p>
                </div>
                <a href="add-product.php" class="btn-cyan-glow">
                    <span>➕ ابتكر منتجاً جديداً</span>
                </a>
            </header>

            <!-- مؤشرات الأداء الحية (KPI Cards) -->
            <section class="stats-grid">
                <div class="kpi-card tilt">
                    <div class="kpi-icon icon-purple">📦</div>
                    <div class="kpi-data">
                        <span class="kpi-title">إجمالي الكتالوج</span>
                        <h3 class="counter"><?php echo number_format($total_products); ?> <small>منتج</small></h3>
                    </div>
                </div>

                <div class="kpi-card tilt">
                    <div class="kpi-icon icon-pink">💎</div>
                    <div class="kpi-data">
                        <span class="kpi-title">القيمة المادية للمتجر</span>
                        <h3>$<?php echo number_format($total_value, 2); ?></h3>
                    </div>
                </div>

                <div class="kpi-card tilt">
                    <div class="kpi-icon icon-cyan">👥</div>
                    <div class="kpi-data">
                        <span class="kpi-title">الأعضاء المسجلين</span>
                        <h3><?php echo number_format($total_users); ?> <small>مستخدم</small></h3>
                    </div>
                </div>
            </section>
         
            <!-- جدول الكائنات المتقدم (المنتجات) -->
            <section class="table-nexus-container">
                <div class="nexus-header">
                    <h3>جدول المنتجات والتفاعل</h3>
                    <span class="live-status"><span class="pulse-dot"></span> مباشر</span>
                </div>

                <div class="table-wrapper">
                    <table class="nexus-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>التصنيف</th>
                                <th>السعر</th>
                                <th>الحالة</th>
                                <th>التحكم والمزامنة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($prod = mysqli_fetch_assoc($products_res)): ?>
                                <tr class="row-hover">
                                    <td>
                                        <div class="prod-cell">
                                            <div class="img-wrapper">
                                                <img src="../images/<?php echo htmlspecialchars($prod['image']); ?>" alt="صورة" onerror="this.src='https://via.placeholder.com/60'">
                                            </div>
                                            <div>
                                                <strong class="prod-name"><?php echo htmlspecialchars($prod['name']); ?></strong>
                                                <span class="prod-id">#ID-<?php echo $prod['id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-tag"><?php echo htmlspecialchars($prod['category'] ?? 'عام'); ?></span>
                                    </td>
                                    <td class="price-tag">$<?php echo number_format($prod['price'], 2); ?></td>
                                    <td>
                                        <span class="status-badge active">متوفر بالمخزن</span>
                                    </td>
                                    <td>
                                        <div class="actions-group">
                                            <a href="edit-product.php?id=<?php echo $prod['id']; ?>" class="action-btn edit" title="تعديل">
                                                ✏️ تعديل
                                            </a>
                                            <a href="index.php?delete=<?php echo $prod['id']; ?>" class="action-btn delete" onclick="return confirm('هل تريد إزالة هذا المنتج نهائياً من الوجود؟')" title="حذف">
                                                🗑️ إزالة
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section><br><br>
             
            <!-- ====================================================================================== -->
            <div class="analytics-grid">

            <!-- إجمالي الإيرادات المكتملة -->
            <div class="stat-card cyan">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>إجمالي الأرباح</h3>
                    <p class="stat-value">$<?php echo number_format($total_revenue, 2); ?></p>
                    <span class="stat-sub">الطلبات المكتملة</span>
                </div>
            </div>

            <!-- مبيعات قيد الانتظار -->
            <div class="stat-card yellow">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3>أرباح قيد المعالجة</h3>
                    <p class="stat-value">$<?php echo number_format($pending_revenue, 2); ?></p>
                    <span class="stat-sub"><?php echo $orders_stat['pending_orders']; ?> طلبات معلقة</span>
                </div>
            </div>

            <!-- إجمالي الطلبات والحالات -->
            <div class="stat-card purple">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3>إجمالي الطلبات</h3>
                    <p class="stat-value"><?php echo $orders_stat['total_orders']; ?></p>
                    <span class="stat-sub">مكتمل: <?php echo $orders_stat['completed_orders']; ?> | ملغى: <?php echo $orders_stat['cancelled_orders']; ?></span>
                </div>
            </div>

            <!-- إحصائيات العملاء -->
            <div class="stat-card green">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3>إجمالي العملاء</h3>
                    <p class="stat-value"><?php echo $users_stat['total_customers']; ?></p>
                    <span class="stat-sub">الأدمن: <?php echo $users_stat['total_admins']; ?></span>
                </div>
            </div>

            <!-- التنبيه بالمخزون -->
            <div class="stat-card magenta">
                <div class="stat-icon">⚠️</div>
                <div class="stat-info">
                    <h3>تنبيهات المخزون</h3>
                    <p class="stat-value">نفذت</p>
                    <span class="stat-sub">مخزون منخفض:  منتجات</span>
                </div>
            </div>

            <!-- المنتج الأكثر مبيعاً -->
            <div class="stat-card blue">
                <div class="stat-icon">🔥</div>
                <div class="stat-info">
                    <h3>الأكثر مبيعاً</h3>
                    <p class="stat-value-text"><?php echo htmlspecialchars($top_product['name'] ?? 'لا يوجد بيانات'); ?></p>
                    <span class="stat-sub">تم بيع: <?php echo $top_product['total_sold'] ?? 0; ?> قطعة</span>
                </div>
            </div>

</div>


        </main>
    </div>

    <!-- سكريبت التأثيرات التفاعلية وحركة الماوس -->
    <script>
        // تأثير الإمالة الثلاثي الأبعاد على بطاقات الإحصائيات (Tilt Effect)
        document.querySelectorAll('.tilt').forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = ((y - centerY) / centerY) * -12;
                const rotateY = ((x - centerX) / centerX) * 12;

                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';
            });
        });
    </script>
</body>
</html>
