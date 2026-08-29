<?php
require_once "auth_check.php";
require_once __DIR__ . "/../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);

    $valid_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        $update_query = "UPDATE orders SET status = '$new_status' WHERE id = $order_id";
        mysqli_query($conn, $update_query);
        header("Location: orders.php?msg=updated");
        exit();
    }
}

$query = "SELECT orders.*, users.name AS user_name, users.email AS user_email 
          FROM orders 
          JOIN users ON orders.user_id = users.id 
          ORDER BY orders.created_at DESC";
$result = mysqli_query($conn, $query);

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
    <title>إدارة الطلبات — Nassee Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <!-- استدعاء ملف الـ CSS الخارجي -->
    <link rel="stylesheet" href="CSS/admin-style.css">
</head>
<body>

    <div class="admin-container">
        <header class="page-header">
            <h2 class="page-title">📦 إدارة <span>الطلبات</span></h2>
            <a href="index.php" class="btn-back">⬅️ لوحة التحكم الرئيسية</a>
        </header>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert-success">✅ تم تحديث حالة الطلب بنجاح!</div>
        <?php endif; ?>

        <div class="glass-panel">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>تاريخ الطلب</th>
                            <th>العنوان والهاتف</th>
                            <th>تحديث الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="order-id">#<?php echo $order['id']; ?></td>
                                <td class="customer-info">
                                    <div style="font-weight:700;"><?php echo htmlspecialchars($order['user_name']); ?></div>
                                    <div><?php echo htmlspecialchars($order['user_email']); ?></div>
                                </td>
                                <td class="order-total">$<?php echo number_format($order['total'], 2); ?></td>
                                <td><?php echo getStatusBadge($order['status']); ?></td>
                                <td class="order-date"><?php echo date('Y/m/d H:i', strtotime($order['created_at'])); ?></td>
                                <td class="order-address">
                                    <div><?php echo htmlspecialchars($order['shipping_address']); ?></div>
                                    <div style="color:var(--neon-cyan); font-weight:bold;"><?php echo htmlspecialchars($order['phone']); ?></div>
                                </td>
                                <td>
                                    <form action="orders.php" method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="status-select">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>قيد المعالجة</option>
                                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>تم الشحن</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>مكتمل</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>ملغى</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn-save" title="حفظ التحديث">💾</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-orders">
                    <h3>👽 لا توجد طلبات مسجلة في القاعدة حالياً.</h3>
                    <p>بانتظار أول عملية شراء!</p>
                </div>
            <?php endif; ?>
            <a href="../generate_invoice.php">الفاتورة</a>
        </div>

    </div>

</body>
</html>
