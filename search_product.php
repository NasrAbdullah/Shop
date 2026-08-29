<?php
session_start();
require_once "config/database.php";

// 1. استقبال مدخلات البحث وقسم الفئة من رابط الصفحة (GET)
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// 2. جلب جميع الفئات من جدول الفئات لعرضها داخل قائمة الاختيار (Select Menu)
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);

// 3. بناء استعلام SQL ديناميكي يربط بين جدول المنتجات وجدول الفئات عبر الـ Foreign Key
$query = "SELECT products.*, categories.name AS category_name 
          FROM products 
          LEFT JOIN categories ON products.category_id = categories.id 
          WHERE 1=1";

// إذا كتب العميل كلمة في شريط البحث
if (!empty($search)) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $query .= " AND (products.name LIKE '%$safe_search%' OR products.description LIKE '%$safe_search%')";
}

// إذا اختار العميل فئة محددة
if ($category_id > 0) {
    $query .= " AND products.category_id = $category_id";
}

$query .= " ORDER BY products.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تصفح المنتجات والبحث — Nassee</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
        
        <!-- نموذج البحث والفلترة حسب المفتاح الأجنبي -->
        <form action="products.php" method="GET" class="filter-form" style="display: flex; gap: 1rem; margin-bottom: 2.5rem; flex-wrap: wrap;">
            
            <!-- حقل النص للبحث -->
            <input type="text" 
                   name="search" 
                   placeholder="ابحث عن اسم المنتج أو مواصفاته..." 
                   value="<?php echo htmlspecialchars($search); ?>" 
                   style="flex: 2; min-width: 220px; padding: 0.8rem 1rem; border-radius: 12px; border: 1px solid rgba(0,243,255,0.2); background: rgba(15,15,25,0.8); color: #fff; outline: none;">

            <!-- قائمة الفئات المجلوبة ديناميكياً برقم الـ ID -->
            <select name="category_id" style="flex: 1; min-width: 160px; padding: 0.8rem 1rem; border-radius: 12px; border: 1px solid rgba(0,243,255,0.2); background: rgba(15,15,25,0.8); color: #fff; outline: none;">
                <option value="0">جميع الفئات</option>
                <?php if ($categories_result && mysqli_num_rows($categories_result) > 0): ?>
                    <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if ($category_id === (int)$cat['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <!-- زر التصفية -->
            <button type="submit" style="padding: 0.8rem 1.8rem; border-radius: 12px; border: none; background: linear-gradient(135deg, #00f3ff, #ff007f); color: #fff; font-weight: bold; cursor: pointer;">
                فلترة 🔍
            </button>

            <!-- زر إلغاء الفلترة -->
            <?php if (!empty($search) || $category_id > 0): ?>
                <a href="products.php" style="padding: 0.8rem 1rem; text-decoration: none; color: #a0a0b5; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; align-items: center;">
                    إعادة ضبط ✖
                </a>
            <?php endif; ?>
        </form>

        <!-- شبكة عرض المنتجات -->
        <div class="products-grid">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($result)): ?>
                    <!-- ======================================== -->
                     
                    <div class="product-card">
                        <img src="images/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        
                        <!-- إظهار اسم الفئة المجلوب عبر الـ JOIN -->
                        <span style="color: var(--neon-cyan, #00f3ff); font-size: 0.8rem; font-weight: 700;">
                            <?php echo htmlspecialchars($product['category_name'] ?? 'عام'); ?>
                        </span>
                        
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                        <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn-details">التفاصيل 🚀</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; width: 100%; grid-column: 1 / -1; padding: 4rem; color: #a0a0b5;">
                    <h2>🔍 لا توجد نتائج مطابقة لخيارات الفلترة!</h2>
                    <p style="margin-top: 0.5rem;">جرّب اختيار فئة أخرى أو البحث بكلمات مختلفة.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
