<?php
require_once __DIR__ . "/../config/database.php";
require_once "auth_check.php";

$error = "";
$success = "";

if (isset($_POST['add_product'])) {
    $name        = mysqli_real_escape_string($conn, trim($_POST['name']));
    $price       = (float)$_POST['price'];
    $category_id    = (int) $_POST['category_id'];
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    // معالجة رفع الصورة
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp  = $_FILES['image']['tmp_name'];
        $image_ext  = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($image_ext, $allowed_exts)) {
            // تسمية فريدة لمنع التكرار
            $new_image_name = time() . '_' . rand(1000, 9999) . '.' . $image_ext;
            $target_folder  = "../images/" . $new_image_name;

            // التأكد من وجود مجلد الصور
            if (!file_exists("../images/")) {
                mkdir("../images/", 0777, true);
            }

            if (move_uploaded_file($image_tmp, $target_folder)) {
                $sql = "INSERT INTO products (name,category_id, price, description, image) 
                        VALUES ('$name',1, '$price',  '$description', '$new_image_name')";
                // '$category_id',
                if (mysqli_query($conn, $sql)) {
                    $_SESSION['success_msg'] = "تم ابتكار المنتج ونشره بنجاح في كتالوج NasSee! ⚡";
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "حدث خطأ في قاعدة البيانات أثناء حفظ المنتج!";
                }
            } else {
                $error = "فشل نقل الصورة إلى المجلد المخصص!";
            }
        } else {
            $error = "امتداد الصورة غير مدعوم! استخدم: JPG, PNG, WEBP, GIF";
        }
    } else {
        $error = "يرجى اختيار صورة للمنتج!";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ابتكار منتج جديد — Nassee Nexus</title>
    
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
                <a href="add-product.php" class="menu-item active">
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
                    <h1 class="glitch-text">ابتكار كائن خارق جديد 🔮</h1>
                    <p class="subtitle">قم بإدخال بيانات المنتج وتوليده مباشرة داخل منظومة المتجر</p>
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
                <form action="add-product.php" method="POST" enctype="multipart/form-data" class="nexus-form">
                    
                    <div class="form-grid">
                        <!-- حقل اسم المنتج -->
                        <div class="input-group">
                            <label for="name"><span class="input-icon">🏷️</span> اسم المنتج الفريد</label>
                            <input type="text" id="name" name="name" placeholder="مثال: نضارة الأبعاد النيونية X1" required autocomplete="off">
                        </div>

                        <!-- حقل السعر -->
                        <div class="input-group">
                            <label for="price"><span class="input-icon">💎</span> السعر ($)</label>
                            <input type="number" step="0.01" id="price" name="price" placeholder="99.99" required>
                        </div>

                        <!-- حقل التصنيف -->
                        <div class="input-group full-width">
                            <label for="category"><span class="input-icon">🌀</span> التصنيف الذكي</label>
                            <input type="text" id="category" name="category_id" placeholder="مثال: إلكترونيات, إكسسوارات, نظارات" list="categories-list">
                            <datalist id="categories-list">
                                <option value="" name="1">إلكترونيات</option>
                                <option value="2">  نظارات ثنائية</option>
                                <option value="3"> ملابس مستقبلية </option>
                                <option value="4">ساعات ذكية</option>
                            </datalist>
                        </div>

                        <!-- حقل الوصف -->
                        <div class="input-group full-width">
                            <label for="description"><span class="input-icon">📝</span> وصف المنتج والشغف</label>
                            <textarea id="description" name="description" rows="4" placeholder="اكتب وصفاً جذاباً يعكس القوة والتصميم المستقبلي للمنتج..."></textarea>
                        </div>

                        <!-- منطقة رفع ومعاينة الصور التفاعلية -->
                        <div class="input-group full-width">
                            <label><span class="input-icon">🖼️</span> الصورة الرئيسية (سحب وإسقاط)</label>
                            
                            <div class="drop-zone" id="drop-zone">
                                <input type="file" name="image" id="product-image" accept="image/*" required class="file-input">
                                
                                <div class="drop-zone-content" id="drop-content">
                                    <div class="upload-icon">🚀</div>
                                    <h4>أسقط صورة المنتج هنا أو <span class="highlight">تصفح الجهاز</span></h4>
                                    <p>يدعم صيغ JPG, PNG, WEBP, GIF (الحجم الأقصى: 5MB)</p>
                                </div>

                                <div class="image-preview-wrapper hidden" id="preview-wrapper">
                                    <img id="image-preview" src="" alt="معاينة الصورة">
                                    <button type="button" class="remove-img-btn" id="remove-img">❌ إزالة الصورة</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- أزرار الإجراءات -->
                    <div class="form-actions">
                        <button type="submit" name="add_product" class="btn-cyan-glow btn-submit">
                            <span>🚀 حقن ونشر المنتج الآن</span>
                        </button>
                        <a href="index.php" class="btn-cancel">إلغاء الأمر</a>
                    </div>

                </form>
            </div>

        </main>
    </div>

    <!-- سكريبت التفاعلات الحية والسحب والإسقاط والمعاينة -->
    <script>
        // 1. معاينة وحذف الصور مع دعم السحب والإسقاط (Drag & Drop)
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
            fileInput.value = '';
            imagePreview.src = '';
            previewWrapper.classList.add('hidden');
            dropContent.classList.remove('hidden');
        });

        // 2. تأثير الإمالة الثلاثي الأبعاد (Tilt Effect)
        const card = document.querySelector('.tilt');
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * -4; // إمالة خفيفة ومريحة
            const rotateY = ((x - centerX) / centerX) * 4;

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';
        });
    </script>
</body>
</html>
