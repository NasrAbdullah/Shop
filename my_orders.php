<?php
session_start();
require_once "config/database.php";

// 1. حماية الصفحة
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM orders WHERE id = '$del_id'");
    header("Location: my_orders.php");
    exit();
}


// 2. جلب كافة الطلبات الخاصة بالعميل
$query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// دالة تنسيق شارات الحالة
function getStatusBadge($status) {
    switch ($status) {
        case 'pending': 
            return '<span class="status-badge pending">⏳ قيد الانتظار</span>';
        case 'processing': 
            return '<span class="status-badge processing">🌀 قيد المعالجة</span>';
        case 'shipped': 
            return '<span class="status-badge shipped">🚀 تم الشحن</span>';
        case 'completed': 
            return '<span class="status-badge completed">🟢 مكتمل</span>';
        case 'cancelled': 
            return '<span class="status-badge cancelled">🔴 ملغى</span>';
        default: 
            return '<span class="status-badge">' . htmlspecialchars($status) . '</span>';
    }
}

include_once "./includes/header.php";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>قائمة طلباتي</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

    <div class="orders-container">
        
        <!-- الهيدر -->
        <header class="page-header">
            <h1 class="page-title">📦 قائمة طلباتي</h1>
            <a href="index.php" class="btn-home">🏠 الصفحة الرئيسية</a>
        </header>

        <!-- الجدول الزجاجي للطلبات -->
        <div class="glass-panel">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>تاريخ الطلب</th>
                            <th>عنوان التوصيل</th>
                            <th>طريقة الدفع</th>
                            <th>المبلغ الإجمالي</th>
                            <th>حالة الطلب</th>
                            <th>الإجراءات</th>
                            <th>إجراء</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <!-- رقم الطلب -->
                                <td class="order-id">#<?php echo $order['id']; ?></td>

                                <!-- تاريخ الطلب -->
                                <td class="order-date">
                                    <?php echo isset($order['created_at']) ? date('Y/m/d H:i', strtotime($order['created_at'])) : '---'; ?>
                                </td>

                                <!-- عنوان الشحن -->
                                <td style="font-size:0.9rem; color:var(--text-muted);">
                                    <?php echo htmlspecialchars($order['shipping_address'] ?? 'غير محدد'); ?>
                                </td>

                                <!-- طريقة الدفع -->
                                <td style="font-size:0.9rem;">
                                    <?php 
                                        $pm = $order['payment_method'] ?? 'cash_on_delivery';
                                        echo ($pm === 'cash_on_delivery') ? '💳 الدفع عند الاستلام' : htmlspecialchars($pm); 
                                    ?>
                                </td>

                                <!-- إجمالي المبلغ -->
                                <td class="order-total">$<?php echo number_format($order['total'] ?? 0, 2); ?></td>

                                <!-- حالة الطلب -->
                                <td>
                                    <?php 
                                        $status = $order['status'] ?? 'pending';
                                        echo getStatusBadge($status); 
                                    ?>
                                </td>

                                <!-- زر استخراج الفاتورة -->
                                <td>
                                    <a href="generate_invoice.php?id=<?php echo $order['id']; ?>" target="_blank" class="btn-invoice">
                                       📄 الفاتورة
                                    </a>
                                </td>
                                <td>
                                <a href="my_orders.php?delete_id=<?php echo $order['id']; ?>" 
                                onclick="return confirm('هل أنت تأكد من حذف هذا الطلب؟');" 
                                style="color: #f87171; text-decoration: none; font-weight: bold;">
                                🗑️ حذف
                                </a>
</td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <!-- في حال لا توجد طلبات سابقاً -->
                <div class="no-orders">
                    <h2>🛒 لم تقم بأي عملية شراء حتى الآن!</h2>
                    <p style="color: var(--text-muted);">استكشف منتجاتنا وابدأ أول طلب لك الآن.</p>
                    <a href="index.php" class="btn-shop-now">تصفح المنتجات 🚀</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
