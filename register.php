<?php
session_start();
require_once "config/database.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $name     = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = "يرجى ملء جميع الحقول المطلوبة!";
        // هذه الدالة الخاصة بالتحقق  مثل نوع البيانات او هل هو ايميل او لا 
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "البريد الإلكتروني غير صحيح!";
    } elseif (strlen($password) < 6) {
        $error = "كلمة المرور يجب أن تكون 6 أحرف على الأقل!";
    } else {
        // التحقق مما إذا كان البريد مستخدماً من قبل
        $check_sql = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $check_res = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_res) > 0) {
            $error = "هذا البريد الإلكتروني مسجل بالفعل!";
        } else {
            // password_hash(Password, Password_default to encryption the password )
            // تشفير كلمة المرور بأمان بأحدث المعايير (BCRYPT)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (name, email, password, phonr) VALUES ('$name', '$email', '$hashed_password', '$phone')";
            if (mysqli_query($conn, $sql)) {
                $success = "تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.";
            } else {
                $error = "حدث خطأ أثناء إنشاء الحساب، يرجى المحاولة لاحقاً.";
            }
        }
    }
}

include_once "./includes/header.php";
?>

    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>إنشاء حساب جديد ✨</h2>
                <p>انضم إلى عائلة NasSee واستمتع بتجربة تسوق استثنائية</p>
            </div>

            <!--  هذه الذي تظهر رسالة الخطا خلال تسجيل الدخول -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name">الاسم الكامل</label>
                    <input type="text" id="name" name="name" placeholder="أدخل اسمك الكامل" required>
                </div>

                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="example@domain.com" required>
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="text" id="phone" name="phone" placeholder="777474" required>
                </div>

                <button type="submit" name="register" class="btn-auth">إنشاء الحساب</button>
            </form>

            <div class="auth-footer">
                <p>لديك حساب بالفعل؟ <a href="login.php">سجل الدخول هنا</a></p>
            </div>
        </div>
    </main>


<?php  
include_once "./includes/footer.php";
?>
