<?php
require_once "config/database.php";

// جلب التصنيف والبحث إذا تم تحديدهما
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$search   = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// بناء الاستعلام الديناميكي
$sql = "SELECT * FROM products WHERE 1=1";
if (!empty($category)) {
    $sql .= " AND category_id = '$category'";
}
if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}
$result = mysqli_query($conn, $sql);

include_once "./includes/header.php";
?>

    <!-- رأس قسم المنتجات -->
    <header class="products-header">
        <div class="header-content">
            <span class="hero-tag">✨ تشكيلة فريدة وحصرية</span>
            <h1>استكشف عالم من التميز</h1>
            <p>اعثر على كل ما تحتاجه بسهولة عبر البحث والتصفية المتقدمة</p>
        </div>
    </header>

    <!-- قسم الفلترة والبحث -->
    <section class="filter-section">
        <form action="products.php" method="GET" class="filter-container">
            
            <!-- صندوق البحث -->
            <div class="search-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="search" placeholder="ابحث عن منتج..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <!-- أزرار التصنيفات (Categories) -->
            <div class="categories-pills">
                <a href="products.php" class="pill <?php echo empty($category) ? 'active' : ''; ?>">الكل</a>
                <a href="products.php?category=electronics" class="pill <?php echo $category == 'electronics' ? 'active' : ''; ?>">إلكترونيات</a>
                <a href="products.php?category=fashion" class="pill <?php echo $category == 'fashion' ? 'active' : ''; ?>">أزياء</a>
                <a href="products.php?category=accessories" class="pill <?php echo $category == 'accessories' ? 'active' : ''; ?>">إكسسوارات</a>
            </div>

            <button type="submit" class="btn-filter">تطبيق</button>
        </form>
    </section>

    <!-- شبكة عرض المنتجات -->
    <section class="products">
        <div class="product-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <div class="card-image">
                            <img 
                                src="images/<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                            >
                            <img src="./images/1785920092985.png" alt="">
                            <span class="badge-featured">جديد</span>
                        </div>

                        <div class="card-body">
                            <div class="rating">★★★★★</div>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="card-footer">
                                <span class="price">$<?php echo htmlspecialchars($product['price']); ?></span>
                            <form action="cart.php" method="post" style="margin-top: 1rem;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn-cart" name="add_to_cart"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                أضف للسلة</button>
                             </form>
                               
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-products">
                    <p>🔍 لم نجد أي منتجات تطابق بحثك حالياً.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php  
include_once "./includes/footer.php";
?>
