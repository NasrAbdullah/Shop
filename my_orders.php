<?php
session_start();
require_once "config/database.php";

// 1. حماية الصفحة: التأكد من أن الزائر مسجل دخوله كعميل
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// 2. جلب كافة الطلبات الخاصة بالعميل المسجل حالياً فقط
$query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// دالة مساعدة لتنسيق حالة الطلب بشارات ملونة
function getStatusBadge($status) {
    switch ($status) {
        case 'pending': return '<span class="status-badge pending">⏳ قيد الانتظار</span>';
        case 'processing': return '<span class="status-badge processing">🌀 قيد المعالجة</span>';
        case 'shipped': return '<span class="status-badge shipped">🚀 تم الشحن</span>';
        case 'completed': return '<span class="status-badge completed">🟢 مكتمل</span>';
        case 'cancelled': return '<span class="status-badge cancelled">🔴 ملغى</span>';
        default: return '<span class="status-badge">' . $status . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي — NasSee</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/my-orders-style.css">
</head>
<body>

    <div class="orders-container">
        
        <!-- الهيدر -->
        <header class="page-header">
            <h1 class="page-title">📦 قائمة <span>طلباتي</span></h1>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="order-id">#<?php echo $order['id']; ?></td>
                                <td class="order-date"><?php echo date('Y/m/d H:i', strtotime($order['created_at'])); ?></td>
                                <td style="font-size:0.9rem; color:var(--text-sub);"><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                                <td style="font-size:0.9rem;">الدفع عند الاستلام</td>
                                <td class="order-total">$<?php echo number_format($order['total'], 2); ?></td>
                                <td><?php echo getStatusBadge($order['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <!-- في حال لا توجد طلبات سابقاً -->
                <div class="no-orders">
                    <h2>🛒 لم تقم بأي عملية شراء حتى الآن!</h2>
                    <p style="margin-top:0.5rem;">استكشف منتجاتنا وابدأ أول طلب لك الآن.</p>
                    <a href="index.php" class="btn-shop-now">تصفح المنتجات 🚀</a>
                </div>
            <?php endif; ?>
            <!-- زر استخراج الفاتورة -->
            <a href="generate_invoice.php?id=<?php echo $order['id']; ?>" 
            target="_blank" 
            style="color: var(--neon-cyan); text-decoration: none; font-weight: bold;">
            📄 الفاتورة
            </a>
        </div>

    </div>

</body>
</html>
