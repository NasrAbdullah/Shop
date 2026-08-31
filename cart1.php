<?php
require_once "config/database.php";
include_once "./includes/header.php";
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

$user_id = (int)$_SESSION['user_id'];

// 1. الحصول على cart_id الخاص بالمستخدم أو إنشائه إن لم يكن موجوداً
$cart_check = mysqli_query($conn, "SELECT id FROM cart WHERE user_id = '$user_id'");
if (mysqli_num_rows($cart_check) > 0) {
    $cart_row = mysqli_fetch_assoc($cart_check);
    $cart_id  = $cart_row['id'];
} else {
    mysqli_query($conn, "INSERT INTO cart (user_id, created_at) VALUES ('$user_id', NOW())");
    $cart_id = mysqli_insert_id($conn);
}

// 2. تحديث الكميات عند الضغط على "تحديث السلة"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $item_id => $qty) {
            $item_id = (int)$item_id;
            $qty     = (int)$qty;
            if ($qty > 0) {
                mysqli_query($conn, "UPDATE cart_items SET quantity = '$qty' WHERE id = '$item_id' AND cart_id = '$cart_id'");
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// 3. حذف عنصر من السلة عبر cart_items.id
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    mysqli_query($conn, "DELETE FROM cart_items WHERE id = '$remove_id' AND cart_id = '$cart_id'");
    header("Location: cart.php");
    exit();
}

// 4. استعلام الاستجابة المطابق لهيكلة الجداول لديك (cart + cart_items + products)
$query = "SELECT 
            cart_items.id AS cart_item_id, 
            cart_items.quantity AS item_qty, 
            products.id AS product_id, 
            products.name, 
            products.price, 
            products.image, 
            products.quantity AS stock_qty 
          FROM cart_items 
          JOIN products ON cart_items.product_id = products.id 
          WHERE cart_items.cart_id = '$cart_id'";

$result = mysqli_query($conn, $query);

$subtotal = 0;
$shipping = 15.00;
?>

<div class="cart-cyber-container">
    <div class="cart-header-title">
        <h1>سلة التسوق الذكية</h1>
        <p>مراجعة المنتجات المحددة وتأكيد الطلب بنقرة واحدة</p>
    </div>

    <form method="POST" action="cart.php">
        <div class="cart-grid-layout">
            
            <!-- جدول المنتجات -->
            <div class="glass-panel cart-table-panel">
                <table class="cyber-table">
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
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($item = mysqli_fetch_assoc($result)): 
                                $item_total = $item['price'] * $item['item_qty'];
                                $subtotal += $item_total;
                            ?>
                                <tr>
                                    <td class="product-cell">
                                        <div class="product-thumb">
                                            <img src="uploads/<?php echo htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" alt="">
                                        </div>
                                        <div class="product-meta">
                                            <span class="p-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                            <span class="p-stock">متوفر في المخزن</span>
                                        </div>
                                    </td>

                                    <td class="price-cell">$<?php echo number_format($item['price'], 2); ?></td>
                                    
                                    <td class="qty-cell">
                                        <input type="number" 
                                               name="quantities[<?php echo $item['cart_item_id']; ?>]" 
                                               value="<?php echo max(1, $item['item_qty']); ?>" 
                                               min="1" 
                                               max="<?php echo $item['stock_qty']; ?>"
                                               class="cyber-qty-input">
                                    </td>

                                    <td class="total-cell">$<?php echo number_format($item_total, 2); ?></td>
                                    
                                    <td class="action-cell">
                                        <a href="cart.php?remove=<?php echo $item['cart_item_id']; ?>" class="cyber-btn-delete" title="حذف">✕</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-cart-msg">🛒 سلة التسوق فارغة حالياً!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="cart-table-actions">
                    <button type="submit" name="update_cart" class="cyber-btn-update">تحديث السلة 🔄</button>
                </div>
            </div>

            <!-- ملخص الطلب -->
            <div class="glass-panel summary-panel">
                <h3 class="summary-title">ملخص الطلب</h3>
                
                <div class="summary-row">
                    <span>المجموع الفرعي</span>
                    <span class="val">$<?php echo number_format($subtotal, 2); ?></span>
                </div>

                <div class="summary-row">
                    <span>رسوم الشحن</span>
                    <span class="val">$<?php echo number_format($subtotal > 0 ? $shipping : 0, 2); ?></span>
                </div>

                <div class="summary-divider"></div>

                <div class="summary-row total-row">
                    <span>الإجمالي الكلي</span>
                    <span class="total-val">$<?php echo number_format($subtotal > 0 ? ($subtotal + $shipping) : 0, 2); ?></span>
                </div>

                <?php if ($subtotal > 0): ?>
                    <a href="checkout.php" class="cyber-btn-checkout">إتمام الشراء الآن 🔥</a>
                <?php else: ?>
                    <button disabled class="cyber-btn-checkout disabled">السلة فارغة</button>
                <?php endif; ?>

                <div class="secure-badge">🔒 دفع آمن ومشفر 100%</div>
            </div>

        </div>
    </form>
</div>
