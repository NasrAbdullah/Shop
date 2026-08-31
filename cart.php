<?php
session_start();
require_once "config/database.php";

// 1. حماية الصفحة: التأكد من أن الزائر مسجل دخوله
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// 2. الحصول على cart_id الخاص بالمستخدم أو إنشائه إن لم يكن موجوداً
$cart_check = mysqli_query($conn, "SELECT id FROM cart WHERE user_id = '$user_id'");
if (mysqli_num_rows($cart_check) > 0) {
    $cart_row = mysqli_fetch_assoc($cart_check);
    $cart_id  = $cart_row['id'];
} else {
    mysqli_query($conn, "INSERT INTO cart (user_id, created_at) VALUES ('$user_id', NOW())");
    $cart_id = mysqli_insert_id($conn);
}

// 3. استقبال طلب "أضف للسلة" المنبعث من الكروت أو تفاصيل المنتج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_to_cart']) || (isset($_POST['action']) && $_POST['action'] === 'add'))) {
    $product_id = (int)$_POST['product_id'];
    $quantity   = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    $item_query = mysqli_query($conn, "SELECT id, quantity FROM cart_items WHERE cart_id = '$cart_id' AND product_id = '$product_id'");

    if (mysqli_num_rows($item_query) > 0) {
        $item_data = mysqli_fetch_assoc($item_query);
        $new_qty   = $item_data['quantity'] + $quantity;
        mysqli_query($conn, "UPDATE cart_items SET quantity = '$new_qty' WHERE id = '{$item_data['id']}'");
    } else {
        mysqli_query($conn, "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES ('$cart_id', '$product_id', '$quantity')");
    }

    header("Location: cart.php");
    exit();
}

// 4. تحديث الكميات من السلة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $item_id => $qty) {
            $item_id = (int)$item_id;
            $qty     = (int)$qty;
            if ($qty <= 0) {
                mysqli_query($conn, "DELETE FROM cart_items WHERE id = '$item_id' AND cart_id = '$cart_id'");
            } else {
                mysqli_query($conn, "UPDATE cart_items SET quantity = '$qty' WHERE id = '$item_id' AND cart_id = '$cart_id'");
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// 5. حذف عنصر فردي من السلة
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    mysqli_query($conn, "DELETE FROM cart_items WHERE id = '$remove_id' AND cart_id = '$cart_id'");
    header("Location: cart.php");
    exit();
}

// 6. جلب عناصر السلة مع تفاصيل المنتجات من قاعدة البيانات
$query = "SELECT 
            cart_items.id AS cart_item_id, 
            cart_items.quantity AS qty, 
            products.id AS product_id, 
            products.name, 
            products.price, 
            products.image, 
            products.quantity AS stock_qty 
          FROM cart_items 
          JOIN products ON cart_items.product_id = products.id 
          WHERE cart_items.cart_id = '$cart_id'";

$result = mysqli_query($conn, $query);

$cart_products = [];
$subtotal = 0;

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['item_total'] = $row['price'] * $row['qty'];
        $subtotal += $row['item_total'];
        $cart_products[] = $row;
    }
}

$shipping = ($subtotal > 0) ? 15.00 : 0.00; // تكلفة الشحن
$grand_total = $subtotal + $shipping;

include_once "./includes/header.php";
?>

    <!-- محتوى السلة -->
    <main class="cart-container">
        
        <div class="cart-header">
            <h1>سلة التسوق الذكية</h1>
            <p>مراجعة المنتجات المحددة وتأكيد الطلب بنقرة واحدة</p>
        </div>

        <?php if (!empty($cart_products)): ?>
            <form action="cart.php" method="POST" class="cart-layout">
                
                <!-- قائمة المنتجات -->
                <div class="cart-items-box">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>السعر</th>
                                <th>الكمية</th>
                                <th>الإجمالي</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_products as $item): ?>
                                <tr>
                                    <td class="product-col">
                                        <img src="images/<?php echo htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <div>
                                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <span class="stock-badge">متوفر في المخزن</span>
                                        </div>
                                    </td>
                                    <td class="price-col">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="qty-col">
                                        <input type="number" 
                                               name="quantities[<?php echo $item['cart_item_id']; ?>]" 
                                               value="<?php echo $item['qty']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['stock_qty']; ?>">
                                    </td>
                                    <td class="total-col">$<?php echo number_format($item['item_total'], 2); ?></td>
                                    <td class="action-col">
                                        <a href="cart.php?remove=<?php echo $item['cart_item_id']; ?>" class="btn-remove" title="حذف">
                                            ✕
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="cart-actions">
                        <a href="products.php" class="btn-continue">← مواصلة التسوق</a>
                        <button type="submit" name="update_cart" class="btn-update">تحديث السلة</button>
                    </div>
                </div>

                <!-- كرت الفاتورة والدفع -->
                <div class="cart-summary-box">
                    <h3>ملخص الطلب</h3>
                    
                    <div class="summary-row">
                        <span>المجموع الفرعي</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>

                    <div class="summary-row">
                        <span>رسوم الشحن</span>
                        <span>$<?php echo number_format($shipping, 2); ?></span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row total-row">
                        <span>الإجمالي الكلي</span>
                        <span class="grand-price">$<?php echo number_format($grand_total, 2); ?></span>
                    </div>

                    <!-- زر إتمام الشراء المصحح ليذهب لصفحة checkout.php مباشرة -->
                    <a href="checkout.php" class="btn-checkout cyber-btn-checkout" style="text-decoration: none; display: block; text-align: center;">
                        إتمام الشراء الآن 🔥
                    </a>

                    <div class="secure-checkout-notice">
                        🔒 دفع آمن ومشفر 100%
                    </div>
                </div>
            </form>
        <?php else: ?>
            <!-- واجهة السلة الفارغة -->
            <div class="empty-cart-box">
                <div class="empty-icon">🛒</div>
                <h2>سلتك فارغة حالياً!</h2>
                <p>يبدو أنك لم تضف أي منتجات إلى سلتك بعد.</p>
                <a href="products.php" class="btn btn-primary">استكشف المنتجات الآن</a>
            </div>
        <?php endif; ?>

    </main>

<?php  
include_once "./includes/footer.php";
?>
