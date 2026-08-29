<?php
session_start();
require_once "config/database.php";

// 1. التحقق من تسجيل الدخول
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// 2. جلب بيانات السلة من جدول cart_items
$cart_query = "SELECT cart_items.*, products.name, products.price 
                FROM cart 
                JOIN cart_items ON cart.id = cart_items.cart_id 
                JOIN products ON cart_items.product_id = products.id 
                WHERE cart.user_id = '$user_id'";
$cart_result = mysqli_query($conn, $cart_query);

if (!$cart_result || mysqli_num_rows($cart_result) === 0) {
    header("Location: cart.php");
    exit();
}

// حساب المجموع وتجميع عناصر الطلب
$cart_items = [];
$grand_total = 0;
while ($item = mysqli_fetch_assoc($cart_result)) {
    $cart_items[] = $item;
    $grand_total += ($item['price'] * $item['quantity']);
}

// 3. معالجة إرسال طلب الشراء
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $shipping_address = mysqli_real_escape_string($conn, trim($_POST['shipping_address']));

    if (empty($phone) || empty($shipping_address)) {
        $error = "يرجى تعبئة كافة البيانات المطلوبة!";
    } else {
        // إنشاء الطلب في جدول orders
        $order_query = "INSERT INTO orders (user_id, total, status, shipping_address, phone, payment_method) 
                        VALUES ('$user_id', '$grand_total', 'pending', '$shipping_address', '$phone', 'cash_on_delivery')";
        
        if (mysqli_query($conn, $order_query)) {
            $order_id = mysqli_insert_id($conn);

            // إدخال تفاصيل المنتجات في order_items
            foreach ($cart_items as $item) {
                $p_id = $item['product_id'];
                $qty  = $item['quantity'];
                $price = $item['price'];

                $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                               VALUES ('$order_id', '$p_id', '$qty', '$price')";
                mysqli_query($conn, $item_query);
            }

            // تفريغ سلة التسوق للمستخدم من قاعدة البيانات
            $get_cart = mysqli_query($conn, "SELECT id FROM cart WHERE user_id = '$user_id'");
            if ($cart_data = mysqli_fetch_assoc($get_cart)) {
                $c_id = $cart_data['id'];
                mysqli_query($conn, "DELETE FROM cart_items WHERE cart_id = '$c_id'");
            }

            // التوجيه لصفحة نجاح الطلب
            header("Location: order_success.php?id=" . $order_id);
            exit();
        } else {
            $error = "حدث خطأ أثناء تنفيذ الطلب، يرجى المحاولة لاحقاً.";
        }
    }
}

// جلب رقم الهاتف الافتراضي للمستخدم إن وجد
$user_res = mysqli_query($conn, "SELECT phone FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_res);
$default_phone = $user_data['phone'] ?? '';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إتمام الشراء — Nassee</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/checkout-style.css">
</head>
<body>

    <div class="checkout-container">
        <h1 class="page-title">💳 إتمام <span>عملية الشراء</span></h1>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="checkout.php" method="POST" class="checkout-grid">
            
            <!-- نموذج البيانات -->
            <div class="glass-panel">
                <h3 class="section-title">📍 تفاصيل الشحن والتوصيل</h3>

                <div class="form-group">
                    <label>رقم الهاتف للتواصل</label>
                    <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($default_phone); ?>" required placeholder="مثال: 777111111">
                </div>

                <div class="form-group">
                    <label>عنوان التوصيل بالتفصيل</label>
                    <textarea name="shipping_address" class="form-input" rows="4" required placeholder="المدينة، الشارع، تفاصيل العنوان..."></textarea>
                </div>

                <div class="form-group">
                    <label>طريقة الدفع</label>
                    <input type="text" class="form-input" value="الدفع عند الاستلام (Cash on Delivery)" readonly style="opacity: 0.7;">
                </div>
            </div>

            <!-- ملخص السلة -->
            <div class="glass-panel">
                <h3 class="section-title">🛒 ملخص الطلب</h3>

                <?php foreach ($cart_items as $item): ?>
                    <div class="order-summary-item">
                        <div>
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                            <div style="color:var(--text-sub); font-size:0.85rem;">الكمية: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="total-row">
                    <span>الإجمالي الكلي:</span>
                    <span class="total-price">$<?php echo number_format($grand_total, 2); ?></span>
                </div>

                <button type="submit" name="place_order" class="btn-submit">🚀 تأكيد الطلب الآن</button>
            </div>

        </form>
    </div>

</body>
</html>
