<?php
session_start();
require_once "config/database.php";

// 1. حماية الصفحة
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$error_msg = "";

// 2. جلب سلة المستخدم
$cart_query = mysqli_query($conn, "SELECT id FROM cart WHERE user_id = '$user_id'");
$cart_data = mysqli_fetch_assoc($cart_query);
$cart_id = $cart_data['id'] ?? null;

// 3. جلب منتجات السلة وحساب الإجمالي
$cart_items = [];
$subtotal = 0;

if ($cart_id) {
    $items_query = mysqli_query($conn, "SELECT ci.quantity, p.id AS product_id, p.name, p.price, p.image 
                                        FROM cart_items ci 
                                        JOIN products p ON ci.product_id = p.id 
                                        WHERE ci.cart_id = '$cart_id'");
    while ($row = mysqli_fetch_assoc($items_query)) {
        $row['item_total'] = $row['price'] * $row['quantity'];
        $subtotal += $row['item_total'];
        $cart_items[] = $row;
    }
}

// التوجيه إذا كانت السلة فارغة
if (empty($cart_items) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit();
}

$shipping = ($subtotal > 0) ? 15.00 : 0.00;
$grand_total = $subtotal + $shipping;

// 4. معالجة حفظ الطلب في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $payment_method   = trim($_POST['payment_method'] ?? 'cash_on_delivery');

    if (empty($shipping_address)) {
        $error_msg = "يرجى إضافة عنوان التوصيل بالتفصيل لتتمكن من إكمال الطلب.";
    } elseif (empty($cart_items)) {
        $error_msg = "سلتك فارغة حالياً!";
    } else {
        $shipping_address_clean = mysqli_real_escape_string($conn, $shipping_address);
        $payment_method_clean   = mysqli_real_escape_string($conn, $payment_method);

        // إدراج الطلب الرئيسي
        $order_sql = "INSERT INTO orders (user_id, total, shipping_address, payment_method, status, created_at) 
                      VALUES ('$user_id', '$grand_total', '$shipping_address_clean', '$payment_method_clean', 'pending', NOW())";
        
        if (mysqli_query($conn, $order_sql)) {
            $order_id = mysqli_insert_id($conn);

            // إدراج عناصر الطلب
            foreach ($cart_items as $item) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                $price = $item['price'];
                @mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$order_id', '$pid', '$qty', '$price')");
            }

            // تفريغ السلة بعد نجاح الطلب
            mysqli_query($conn, "DELETE FROM cart_items WHERE cart_id = '$cart_id'");

            // التحويل التلقائي لصفحة طلباتي
            header("Location: my_orders.php");
            exit();
        } else {
            $error_msg = "حدث خطأ في النظام أثناء معالجة طلبك: " . mysqli_error($conn);
        }
    }
}

include_once "./includes/header.php";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تأكيد وإتمام الطلب</title>
    <style>
        :root {
            --bg-dark: #050811;
            --neon-cyan: #38bdf8;
            --neon-purple: #c084fc;
            --neon-pink: #f43f5e;
            --neon-green: #4ade80;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
        }

        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(56, 189, 248, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 85% 80%, rgba(192, 132, 252, 0.08) 0%, transparent 40%);
            color: var(--text-main);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 30px 15px;
        }

        .checkout-wrapper {
            max-width: 1100px;
            margin: 0 auto;
        }

        .page-title {
            text-align: center;
            font-size: 2.2rem;
            font-weight: 900;
            margin-bottom: 30px;
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(56, 189, 248, 0.2);
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 25px;
        }

        @media (max-width: 900px) {
            .checkout-grid { grid-template-columns: 1fr; }
        }

        /* الكروت الزجاجية الخيالية */
        .cyber-card {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5), 
                        inset 0 0 2px rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .cyber-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, var(--neon-cyan), var(--neon-purple));
        }

        .card-header-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--neon-cyan);
            margin-top: 0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* تنسيق النموذج والمدخلات */
        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.95rem;
            color: var(--text-sub);
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(56, 189, 248, 0.2);
            border-radius: 12px;
            color: #fff;
            font-size: 0.95rem;
            box-sizing: border-box;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--neon-cyan);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
            background: rgba(0, 0, 0, 0.6);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* طرق الدفع المصممة حديثاً */
        .payment-option {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 12px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-option:hover {
            background: rgba(56, 189, 248, 0.08);
            border-color: var(--neon-cyan);
        }

        /* قائمة ملخص الطلب */
        .summary-items {
            max-height: 280px;
            overflow-y: auto;
            padding-left: 5px;
            margin-bottom: 20px;
        }

        .item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .item-info img {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .item-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin: 0;
        }

        .item-qty {
            font-size: 0.8rem;
            color: var(--text-sub);
        }

        .item-price {
            font-weight: 700;
            color: var(--neon-cyan);
        }

        /* صفوف المبالغ */
        .calc-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            color: var(--text-sub);
            font-size: 0.95rem;
        }

        .calc-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            margin: 15px 0;
        }

        .total-row {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
        }

        .total-price {
            color: var(--neon-green);
            text-shadow: 0 0 10px rgba(74, 222, 128, 0.4);
        }

        /* زر تأكيد الطلب النيون */
        .btn-confirm-order {
            width: 100%;
            padding: 16px;
            margin-top: 15px;
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-purple));
            border: none;
            border-radius: 12px;
            color: #000;
            font-size: 1.15rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.3);
        }

        .btn-confirm-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(192, 132, 252, 0.6);
        }

        .alert-danger {
            background: rgba(244, 63, 94, 0.15);
            border: 1px solid var(--neon-pink);
            color: var(--neon-pink);
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="checkout-wrapper">
        <h1 class="page-title">⚡ تأكيد وإتمام الطلب</h1>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-danger">⚠️ <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="checkout.php" method="POST" class="checkout-grid">
            
            <!-- القسم الأيمن: البيانات والشحن -->
            <div class="cyber-card">
                <h3 class="card-header-title">📍 عنوان التوصيل والدفع</h3>
                
                <div class="form-group">
                    <label for="shipping_address">العنوان التفصيلي للشحن:</label>
                    <textarea name="shipping_address" id="shipping_address" class="form-control" placeholder="أدخل (المدينة، الحي، الشارع، أو أي علامة مميزة)..." required></textarea>
                </div>

                <div class="form-group">
                    <label>طريقة الدفع المتاحة:</label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cash_on_delivery" checked style="accent-color: var(--neon-cyan);">
                        <span>💵 الدفع نقداً عند الاستلام (COD)</span>
                    </label>
                </div>
            </div>

            <!-- القسم الأيسر: ملخص المنتجات والتكلفة -->
            <div class="cyber-card">
                <h3 class="card-header-title">🛒 ملخص المنتجات (<?php echo count($cart_items); ?>)</h3>
                
                <!-- قائمة العناصر -->
                <div class="summary-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="item-row">
                            <div class="item-info">
                                <img src="images/<?php echo htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" alt="">
                                <div>
                                    <p class="item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                                    <span class="item-qty">الكمية: <?php echo $item['quantity']; ?></span>
                                </div>
                            </div>
                            <div class="item-price">$<?php echo number_format($item['item_total'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- حساب الإجمالي -->
                <div class="calc-row">
                    <span>المجموع الفرعي:</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="calc-row">
                    <span>رسوم الشحن:</span>
                    <span>$<?php echo number_format($shipping, 2); ?></span>
                </div>

                <div class="calc-divider"></div>

                <div class="calc-row total-row">
                    <span>المبلغ الكلي:</span>
                    <span class="total-price">$<?php echo number_format($grand_total, 2); ?></span>
                </div>

                <button type="submit" name="place_order" class="btn-confirm-order">
                    🚀 تأكيد الطلب الآن
                </button>
            </div>

        </form>
    </div>

</body>
</html>
