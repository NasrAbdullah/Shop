<?php
require_once "config/database.php";

$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);


// هنا هو الذي يعمل وسم البداية 
// وكل شي خاص بال header
include_once "./includes/header.php";
// include_once "header.php";
?>

    <section class="hero">
        <div class="hero-content">
            <span class="hero-tag">✨ الجيل الجديد للتسوق الإلكتروني</span>
            <h1>اكتشف منتجات استثنائية فريدة</h1>
            <p>استمتع بتجربة تسوق فريدة مع عروض حصرية، جودة عالية، وتوصيل سريع للغاية.</p>
            <div class="hero-btns">
                <a href="products.php" class="btn btn-primary">تسوق الآن</a>
                <a href="#featured" class="btn btn-secondary">استكشف المزيد</a>
            </div>
        </div>
    </section>

    <section class="products" id="featured">
        <div class="section-title">
            <h2>المنتجات المميزة</h2>
            <p>تم اختيارها بعناية خاصة لترتقي بذوقك</p>
        </div>

        <div class="product-container">

             <!-- array to fetch convert data to assoccietive array -->
            <?php while ($product = mysqli_fetch_assoc($result)): ?>
                <div class="product-card">
                    <div class="card-image">
                        <!-- الصور من داخل المجلد -->
                        <img 
                            src="images/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                        >
                        <!-- <img src="./images/1785920092974.jpg" alt=""> -->
                        <span class="badge-featured">مميز</span>
                    </div>

                    <div class="card-body">
                        <div class="rating">★★★★★</div>
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        
                        <div class="card-footer"> 
                            <span class="price">$<?php echo htmlspecialchars($product['price']); ?></span>

                        </div>
                        <form action="cart.php" method="POST" style="margin-top: 1rem;">
                            <!-- نوع الإجراء -->
                            <input type="hidden" name="action" value="add">
                            
                            <!-- رقم المنتج -->
                            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                            
                            <!-- الكمية الافتراضية عند الضغط من الكرت هي 1 -->
                            <input type="hidden" name="quantity" value="1">

                            <!-- زر الإضافة -->
                            <button type="submit" name="add_to_cart" class="btn-cart">
                                أضف للسلة 🛍️
                            </button>
                        </form>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

<?php  
include_once "./includes/footer.php";
?>
