<?php
session_start();
require_once "config/database.php";

// تهيئة السلة إذا لم تكن موجودة في الجلسة (Session)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 1. إضافة منتج للسلة
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity   = (int)$_POST['quantity'];

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    header("Location: cart.php");
    // header("Location: index.php");
    exit();
}

// 2. تحديث الكميات
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $id => $qty) {
        $id  = (int)$id;
        $qty = (int)$qty;
        if ($qty <= 0)  {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
    }
    header("Location: cart.php");
    exit();
}

// 3. حذف عنصر فردي
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$remove_id]);
    header("Location: cart.php");
    exit();
}

// جلب تفاصيل المنتجات الموجودة في السلة من قاعدة البيانات
$cart_products = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $row['qty'] = $_SESSION['cart'][$row['id']];
        $row['item_total'] = $row['price'] * $row['qty'];
        $subtotal += $row['item_total'];
        $cart_products[] = $row;
    }
}

$shipping = ($subtotal > 0) ? 15.00 : 0.00; // تكلفة شحن ثابته
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
                                        <img src="images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <div>
                                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <span class="stock-badge">متوفر في المخزن</span>
                                        </div>
                                    </td>
                                    <td class="price-col">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="qty-col">
                                        <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['qty']; ?>" min="1" max="10">
                                    </td>
                                    <td class="total-col">$<?php echo number_format($item['item_total'], 2); ?></td>
                                    <td class="action-col">
                                        <!--  here we send the id of the item of the product -->
                                        <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn-remove" title="حذف">
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

                    <a href="checkout.php">
                        <button type="button" class="btn-checkout">
                            ✨ إتمام الشراء الآن
                        </button>
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
