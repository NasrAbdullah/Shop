<?php
require_once __DIR__ . "/../config/database.php";
require_once "auth_check.php";

$error = "";
$id = (int)($_GET['id'] ?? 0);

// جلب بيانات المنتج الحالي
$res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id LIMIT 1");
$product = mysqli_fetch_assoc($res);

if (!$product) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['update_product'])) {
    $name        = mysqli_real_escape_string($conn, trim($_POST['name']));
    $price       = (float)$_POST['price'];
    $category    = mysqli_real_escape_string($conn, trim($_POST['category']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    $image_update_sql = "";

    // معالجة تغيير الصورة (في حال اختيار صورة جديدة)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp  = $_FILES['image']['tmp_name'];
        $image_ext  = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($image_ext, $allowed_exts)) {
            $new_image_name = time() . '_' . rand(1000, 9999) . '.' . $image_ext;
            $target_folder  = "../images/" . $new_image_name;

            if (move_uploaded_file($image_tmp, $target_folder)) {
                // حذف الصورة القديمة إذا كانت موجودة
                if (!empty($product['image']) && file_exists("../images/" . $product['image'])) {
                    unlink("../images/" . $product['image']);
                }
                $image_update_sql = ", image = '$new_image_name'";
            } else {
                $error = "فشل في رفع الصورة الجديدة!";
            }
        } else {
            $error = "امتداد الصورة غير مدعوم!";
        }
    }

    if (empty($error)) {
        $sql = "UPDATE products SET 
                name = '$name', 
                price = '$price', 
                category_id = '$category', 
                description = '$description' 
                $image_update_sql 
                WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            header("Location: index.php");
            exit();
        } else {
            $error = "حدث خطأ أثناء تحديث البيانات في قاعدة البيانات!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المنتج — NasSee Nexus</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">

    <!-- الخلفيات المتوهجة الحية -->
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>
    <div class="ambient-glow glow-3"></div>

    <div class="admin-layout">
        <!-- الشريط الجانبي المستقبلي -->
        <aside class="sidebar-glass">
            <div class="brand">
                <div class="brand-icon">⚡</div>
                <h2>Nas<span>See</span> <small>NEXUS</small></h2>
            </div>

            <nav class="sidebar-menu">
                <a href="index.php" class="menu-item">
                    <span class="icon">📊</span>
                    <span>مركز القيادة</span>
                </a>
                <a href="add-product.php" class="menu-item">
                    <span class="icon">✨</span>
                    <span>إضافة منتج خارق</span>
                </a>
                <a href="../index.php" target="_blank" class="menu-item">
                    <span class="icon">🌐</span>
                    <span>معاينة المتجر ↗</span>
                </a>
                <a href="../logout.php" class="menu-item logout">
                    <span class="icon">🚪</span>
                    <span>إنهاء الجلسة</span>
                </a>
            </nav>

            <div class="admin-profile-badge">
                <div class="avatar">👑</div>
                <div class="info">
                    <h4><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'الأدمن'); ?></h4>
                    <p>صلاحيات مطلقة</p>
                </div>
            </div>
        </aside>

        <!-- منطقة العمل الرئيسية -->
        <main class="main-content">
            
            <header class="top-nexus">
                <div>
                    <h1 class="glitch-text">تحديث خصائص المنتج ✏️</h1>
                    <p class="subtitle">تعديل بيانات الكائن رقم <span style="color:var(--neon-cyan)">#ID-<?php echo $product['id']; ?></span> ومزامنتها لحظياً</p>
                </div>
                <a href="index.php" class="btn-cyan-glow" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-glow); box-shadow:none;">
                    <span>⬅ العودة للوحة</span>
                </a>
            </header>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error mb-2"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- بطاقة النموذج الزجاجية ثلاثية الأبعاد -->
            <div class="add-card-container tilt">
                <form action="edit-product.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="nexus-form">
                    
                    <div class="form-grid">
                        <!-- اسم المنتج -->
                        <div class="input-group">
                            <label for="name"><span class="input-icon">🏷️</span> اسم المنتج</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required autocomplete="off">
                        </div>

                        <!-- السعر -->
                        <div class="input-group">
                            <label for="price"><span class="input-icon">💎</span> السعر ($)</label>
                            <input type="number" step="0.01" id="price" name="price" value="<?php echo $product['price']; ?>" required>
                        </div>

                        <!-- التصنيف -->
                        <div class="input-group full-width">
                            <label for="category"><span class="input-icon">🌀</span> التصنيف الذكي</label>
                            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($product['category'] ?? ''); ?>" list="categories-list">
                            <datalist id="categories-list">
                                <option value="الكترونيات" name="1">
                                <option value="نظارات ثنائية">
                                <option value="1" >
                                <option value="سيارات خيالية" name="4">
                                <option value="ساعات ذكية" >
                            </datalist>
                        </div>

                        <!-- الوصف -->
                        <div class="input-group full-width">
                            <label for="description"><span class="input-icon">📝</span> وصف المنتج</label>
                            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <!-- تعديل/تغيير الصورة -->
                        <div class="input-group full-width">
                            <label><span class="input-icon">🖼️</span> صورة المنتج (اسحب صورة جديدة لاستبدال الحالية)</label>
                            
                            <div class="drop-zone" id="drop-zone">
                                <input type="file" name="image" id="product-image" accept="image/*" class="file-input">
                                
                                <div class="drop-zone-content hidden" id="drop-content">
                                    <div class="upload-icon">🚀</div>
                                    <h4>أسقط صورة جديدة هنا لاستبدال الصورة الحالية</h4>
                                    <p>يدعم صيغ JPG, PNG, WEBP, GIF</p>
                                </div>

                                <div class="image-preview-wrapper" id="preview-wrapper">
                                    <img id="image-preview" src="../images/<?php echo htmlspecialchars($product['image']); ?>" alt="الصورة الحالية" onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
                                    <button type="button" class="remove-img-btn" id="remove-img">🔄 تغيير الصورة</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- أزرار الإجراءات -->
                    <div class="form-actions">
                        <button type="submit" name="update_product" class="btn-cyan-glow btn-submit">
                            <span>💾 حفظ وتطبيق التحديثات</span>
                        </button>
                        <a href="index.php" class="btn-cancel">إلغاء التعديل</a>
                    </div>

                </form>
            </div>

        </main>
    </div>

    <!-- سكريبت التفاعلات والسحب والإسقاط وإمالة 3D -->
    <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('product-image');
        const dropContent = document.getElementById('drop-content');
        const previewWrapper = document.getElementById('preview-wrapper');
        const imagePreview = document.getElementById('image-preview');
        const removeBtn = document.getElementById('remove-img');

        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });

        ['dragleave', 'dragend'].forEach(type => {
            dropZone.addEventListener(type, () => {
                dropZone.classList.remove('drag-over');
            });
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFiles(e.dataTransfer.files);
            }
        });

        function handleFiles(files) {
            if (files && files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    dropContent.classList.add('hidden');
                    previewWrapper.classList.remove('hidden');
                }
                reader.readAsDataURL(files[0]);
            }
        }

        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.click();
        });

        // تأثير الإمالة الثلاثي الأبعاد (Tilt)
        const card = document.querySelector('.tilt');
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * -4;
            const rotateY = ((x - centerX) / centerX) * 4;

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';
        });
    </script>
</body>
</html>
