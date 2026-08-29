<?php
session_start();
require_once "config/database.php";

// 1. استقبال معرّف المنتج والتحقق من صحته
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: index.php");
    exit();
}

// 2. الاستعلام عن بيانات المنتج من قاعدة البيانات
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

// إذا لم يتم العثور على المنتج
if (!$product) {
    header("Location: index.php");
    exit();
}

// 3. معالجة إضافة المنتج إلى السلة
$alert_msg = "";
if (isset($_POST['add_to_cart'])) {
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        header("Location: login.php");
        exit();
    }

    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $quantity = max(1, $quantity); // التأكد من أن الكمية 1 على الأقل

    // فحص ما إذا كان المنتج موجوداً مسبقاً في سلة العميل
    $check_cart = mysqli_query($conn, "SELECT id, quantity FROM cart_items WHERE user_id = '$user_id' AND product_id = '$product_id'");
    
    if (mysqli_num_rows($check_cart) > 0) {
        // تحديث الكمية إذا كان موجوداً
        $cart_item = mysqli_fetch_assoc($check_cart);
        $new_qty = $cart_item['quantity'] + $quantity;
        mysqli_query($conn, "UPDATE cart_items SET quantity = '$new_qty' WHERE id = '{$cart_item['id']}'");
    } else {
        // إضافة عنصر جديد للسلة
        $price = $product['price'];
        mysqli_query($conn, "INSERT INTO cart_items (user_id, product_id, quantity, price) VALUES ('$user_id', '$product_id', '$quantity', '$price')");
    }

    $alert_msg = "تمت إضافة المنتج إلى السلة بنجاح! 🛍️";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> — Nassee</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/product-details.css">
</head>
<body>

    <div class="details-wrapper">
        
        <!-- زر العودة للمتجر -->
        <a href="index.php" class="back-link">
            <span>→</span> العودة للمتجر الرئيسي
        </a>

        <?php if (!empty($alert_msg)): ?>
            <div style="background: rgba(0,255,128,0.15); border: 1px solid #00ff80; color: #00ff80; padding: 1rem; border-radius: 14px; margin-bottom: 1.5rem; text-align: center; font-weight: bold;">
                <?php echo $alert_msg; ?>
            </div>
        <?php endif; ?>

        <!-- البطاقة الزجاجية الكبرى -->
        <div class="product-card-glass">
            
            <!-- قسم معارض الصور والتفاعل البصري -->
            <div class="gallery-container">
                <?php if (!empty($product['stock']) && $product['stock'] > 0): ?>
                    <span class="stock-badge available">متوفر في المخزون (<?php echo $product['stock']; ?>)</span>
                <?php else: ?>
                    <span class="stock-badge empty">غير متوفر حالياً</span>
                <?php endif; ?>

                <img src="uploads/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="main-image">
            </div>

            <!-- قسم تفاصيل المنتج والتحكم -->
            <div class="info-container">
                <span class="product-category">اصدار حصري</span>
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                <div class="price-box">
                    <span class="price-amount">$<?php echo number_format($product['price'], 2); ?></span>
                </div>

                <p class="product-desc">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'لا يوجد وصف متاح لهذا المنتج حالياً.')); ?>
                </p>

                <!-- نموذج الإضافة للسلة -->
                <form action="" method="POST">
                    <div class="action-panel">
                        <div class="qty-picker">
                            <button type="button" class="qty-btn" onclick="updateQty(-1)">-</button>
                            <input type="number" name="quantity" id="qtyInput" value="1" min="1" readonly class="qty-input">
                            <button type="button" class="qty-btn" onclick="updateQty(1)">+</button>
                        </div>

                        <button type="submit" name="add_to_cart" class="btn-add-cart">
                            <span>إضافة إلى السلة</span>
                            <span style="font-size: 1.3rem;">🚀</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- البرمجيات التفاعلية في الواجهة -->
    <script>
        function updateQty(change) {
            const input = document.getElementById('qtyInput');
            let current = parseInt(input.value) || 1;
            current += change;
            if (current < 1) current = 1;
            input.value = current;
        }
    </script>
</body>
</html>
