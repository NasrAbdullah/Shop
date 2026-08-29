<?php
session_start();
require_once "config/database.php";

// 1. التأكد من تسجيل الدخول
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// 2. استقبال رقم الطلب من الرابط والتحقق منه
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 3. جلب بيانات الطلب بشرط أن يكون خاصاً بنفس المستخدم المسجل (للأمان)
$query = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    // إذا لم يتم العثور على الطلب يتم التوجيه للرئيسية
    header("Location: index.php");
    exit();
}

$order = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم تأكيد الطلب بنجاح — Nassee</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/success-style.css">
</head>
<body>

    <div class="success-card">
        <div class="icon-box">🎉</div>
        
        <h1 class="success-title">شكراً لك! تم إتمام طلبك بنجاح</h1>
        <p class="success-subtitle">تم استلام طلبك وجاري تجهيزه للتوصيل في أقرب وقت.</p>

        <!-- تفاصيل الطلب -->
        <div class="order-details-box">
            <div class="detail-row">
                <span class="detail-label">رقم الطلب:</span>
                <span class="detail-value highlight">#<?php echo $order['id']; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">المبلغ الإجمالي:</span>
                <span class="detail-value green">$<?php echo number_format($order['total'], 2); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">طريقة الدفع:</span>
                <span class="detail-value">الدفع عند الاستلام</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">عنوان التوصيل:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['shipping_address']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">رقم التواصل:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['phone']); ?></span>
            </div>
        </div>

        <!-- أزرار الإجراءات -->
        <div class="action-btns">
            <a href="index.php" class="btn-primary">متابعة التسوق 🛍️</a>
            <a href="my_orders.php" class="btn-secondary">استعراض طلباتي 📦</a>
        </div>
    </div>

</body>
</html>
