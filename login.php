<?php
session_start();
require_once "config/database.php";

$error = "";

if (isset($_POST['login'])) {
    // this is function to clean the text from any tags
    // like mysqli_prepar, mysqli_stmt_bind_parama

    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "يرجى كتابة البريد الإلكتروني وكلمة المرور!";
    } else {
        $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // التحقق من كلمة المرور المشفرة
            if (password_verify($password, $user['password'])) {
                // هنا تبدا الجلسةللمستخدم
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                if($user['role'] === 'Admin'){
                    header("Location: Admin/index.php");
                }else{
                     header("Location: index.php");
                     exit();
                }        
            } else {
                $error = "كلمة المرور غير صحيحة!";
            }
        } else {
            $error = "البريد الإلكتروني غير مسجل لدينا!";
        }
    }
}
include_once "./includes/header.php";
?>


    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>مرحباً بعودتك! 👋</h2>
                <p>سجل دخولك لمتابعة مشترياتك وإدارة حسابك</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" placeholder="example@domain.com" required>
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" name="login" class="btn-auth">تسجيل الدخول</button>
            </form>

            <div class="auth-footer">
                <p>ليس لديك حساب؟ <a href="register.php">أنشئ حساباً جديداً</a></p>
            </div>
        </div>
    </main>

<?php  
include_once "./includes/footer.php";
?>

