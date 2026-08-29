<?php
session_start();
require_once "config/database.php";

// 1. فحص تسجيل الدخول
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? 'customer';

if (!$user_id) {
    header("Location: login.php");
    exit();
}

// 2. استقبال رقم الطلب
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 3. جلب بيانات الطلب (الأدمن يستطيع عرض أي فاتورة، العميل يستطيع عرض فاتورته فقط)
if ($user_role === 'admin') {
    $query = "SELECT orders.*, users.name as customer_name, users.email FROM orders 
              JOIN users ON orders.user_id = users.id 
              WHERE orders.id = $order_id";
} else {
    $query = "SELECT orders.*, users.name as customer_name, users.email FROM orders 
              JOIN users ON orders.user_id = users.id 
              WHERE orders.id = $order_id AND orders.user_id = $user_id";
}

$order_res = mysqli_query($conn, $query);

if (!$order_res || mysqli_num_rows($order_res) === 0) {
    die("الطلب غير موجود أو لا تملك صلاحية الوصول إليه.");
}

$order = mysqli_fetch_assoc($order_res);

// 4. جلب عناصر الطلب
$items_query = "SELECT order_items.*, products.name as product_name 
                FROM order_items 
                JOIN products ON order_items.product_id = products.id 
                WHERE order_id = $order_id";
$items_res = mysqli_query($conn, $items_query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة رقم #<?php echo $order['id']; ?> — Nassee Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Cairo', sans-serif; }
        body { background: #f4f6f9; color: #333; padding: 20px; }
        
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
        }

        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #00f3ff; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: 900; color: #000; }
        .logo span { color: #00f3ff; }
        
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .details-box h4 { font-size: 14px; color: #888; margin-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th, table td { padding: 12px; text-align: right; border-bottom: 1px solid #ddd; }
        table th { background: #f8f9fa; color: #555; font-weight: 700; }
        
        .total-box { text-align: left; font-size: 18px; font-weight: 800; }
        .total-box span { color: #00b8c4; font-size: 22px; }

        .btn-print {
            display: inline-block;
            background: #050509;
            color: #fff;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
            border: none;
        }

        /* أخفِ أزرار التحكم أثناء حفظ الملف كـ PDF أو طباعته */
        @media print {
            .btn-print { display: none; }
            body { background: #fff; padding: 0; }
            .invoice-box { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

    <div style="text-align: center;">
        <button onclick="window.print()" class="btn-print">🖨️ طباعة / حفظ كـ PDF</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="logo">NASSEE <span>STORE</span></div>
            <div>
                <h2>فاتورة مبيعات</h2>
                <small>تاريخ: <?php echo date('Y-m-d', strtotime($order['created_at'])); ?></small>
            </div>
        </div>

        <div class="details-grid">
            <div class="details-box">
                <h4>معلومات العميل</h4>
                <p><strong>الاسم:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>البريد:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            </div>
            <div class="details-box">
                <h4>تفاصيل الشحن والطلب</h4>
                <p><strong>رقم الفاتورة:</strong> #<?php echo $order['id']; ?></p>
                <p><strong>العنوان:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                <p><strong>حالة الطلب:</strong> <?php echo strtoupper($order['status']); ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>السعر الفردي</th>
                    <th>الكمية</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($items_res)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="total-box">
            المبلغ الإجمالي الكلي: <span>$<?php echo number_format($order['total'], 2); ?></span>
        </div>
    </div>

</body>
</html>
