<?php
session_start();
require_once 'config/db.php';

// PHPMailer Logic (Ensure PHPMailer is installed via Composer or included manually)
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;
// require 'vendor/autoload.php';

$error = "";
$success = "";
$action = $_POST['action'] ?? '';
$ref_code = isset($_GET['ref']) ? $_GET['ref'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($action === 'login') {
        // --- LOGIN LOGIC ---
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (!empty($email) && !empty($password)) {
            $stmt = $conn->prepare("SELECT id, username, password, role, full_name FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Set Session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Send Security Alert Email (Example Implementation)
                    /*
                    $mail = new PHPMailer(true);
                    try {
                        $mail->setFrom('no-reply@yourdomain.com', 'Security Team');
                        $mail->addAddress($email, $user['full_name']);
                        $mail->isHTML(true);
                        $mail->Subject = 'Security Alert: New Login Detected';
                        $mail->Body    = 'Hello ' . htmlspecialchars($user['full_name']) . ',<br>A new login was detected on your account.';
                        $mail->send();
                    } catch (Exception $e) {
                        // Handle error silently
                    }
                    */

                    // Redirect
                    if ($user['role'] == 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No account found with that email.";
            }
            $stmt->close();
        } else {
            $error = "Please fill in all fields.";
        }
    } elseif ($action === 'register') {
        // --- REGISTRATION LOGIC ---
        $username = trim($_POST['username']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $referral_code = trim($_POST['referral_code']);

        if (!empty($username) && !empty($full_name) && !empty($email) && !empty($password) && !empty($referral_code)) {

            // 1. Validate Referral Code
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt_check->bind_param("s", $referral_code);
            $stmt_check->execute();
            $res_check = $stmt_check->get_result();

            if ($res_check->num_rows > 0) {
                $parent = $res_check->fetch_assoc();
                $parent_id = $parent['id'];

                // 2. Check if Username/Email exists
                $stmt_exist = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
                $stmt_exist->bind_param("ss", $email, $username);
                $stmt_exist->execute();

                if ($stmt_exist->get_result()->num_rows == 0) {
                    // 3. Create User
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $my_referral_code = "REF" . strtoupper(substr(md5(uniqid()), 0, 6));

                    // Note: Assuming other fields (nic, address etc) allow NULL in DB or are handled later
                    $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password, full_name, referral_code, parent_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_insert->bind_param("sssssi", $username, $email, $hashed_password, $full_name, $my_referral_code, $parent_id);

                    if ($stmt_insert->execute()) {
                        $success = "Registration successful! Please sign in.";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                    $stmt_insert->close();
                } else {
                    $error = "Username or Email already taken.";
                }
                $stmt_exist->close();
            } else {
                $error = "Invalid Referral Code.";
            }
            $stmt_check->close();
        } else {
            $error = "All fields are required.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Marketing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .floating-input:placeholder-shown~label {
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            color: #6b7280;
        }

        .floating-input:focus~label,
        .floating-input:not(:placeholder-shown)~label {
            top: 0;
            transform: translateY(-50%);
            font-size: 0.875rem;
            background-color: white;
            padding: 0 0.25rem;
            color: #4f46e5;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans">

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Left Side -->
        <div
            class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 to-violet-700 relative overflow-hidden items-center justify-center p-12">
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-80 h-80 bg-purple-500 opacity-20 rounded-full blur-3xl"></div>
            </div>
            <div class="relative z-10 text-center text-white max-w-lg">
                <div class="mb-8 flex justify-center">
                    <div class="p-3 bg-white/10 rounded-xl backdrop-blur-sm"><i data-lucide="trending-up"
                            class="w-12 h-12 text-white"></i></div>
                </div>
                <h2 class="text-4xl font-bold mb-6 leading-tight">"Growth is never by mere chance; it is the result of
                    forces working together."</h2>
                <p class="text-indigo-200 text-lg font-medium">- James Cash Penney</p>
            </div>
        </div>

        <!-- Right Side -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-gray-100">

                <!-- Alerts -->
                <?php if ($error): ?>
                    <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-200" role="alert">
                        <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg border border-green-200"
                        role="alert"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <!-- Login Form -->
                <div id="login-container"
                    class="<?php echo (($action === 'register' && empty($success)) || ($action === '' && !empty($ref_code))) ? 'hidden' : 'block'; ?> transition-all duration-500">
                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-bold text-gray-900">Welcome Back</h1>
                        <p class="text-gray-500 mt-2">Enter your credentials to access your account.</p>
                    </div>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="login">
                        <div class="relative">
                            <input type="email" name="email" id="login-email"
                                class="floating-input peer w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-transparent"
                                placeholder="Email" required>
                            <label for="login-email"
                                class="absolute left-3 transition-all duration-200 pointer-events-none">Email
                                Address</label>
                            <i data-lucide="mail"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        </div>
                        <div class="relative">
                            <input type="password" name="password" id="login-pass"
                                class="floating-input peer w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-transparent"
                                placeholder="Password" required>
                            <label for="login-pass"
                                class="absolute left-3 transition-all duration-200 pointer-events-none">Password</label>
                            <button type="button" onclick="togglePassword('login-pass')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"><i
                                    data-lucide="eye" class="w-5 h-5"></i></button>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox"
                                    class="w-4 h-4 text-indigo-600 rounded"> <span
                                    class="text-sm text-gray-600">Remember me</span></label>
                            <a href="#" class="text-sm font-medium text-indigo-600 hover:underline">Forgot Password?</a>
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition-all shadow-lg">Sign
                            In</button>
                    </form>
                    <p class="mt-8 text-center text-gray-600">Don't have an account? <button onclick="toggleForms()"
                            class="text-indigo-600 font-bold hover:underline ml-1">Create Account</button></p>
                </div>

                <!-- Registration Form -->
                <div id="register-container"
                    class="<?php echo (($action === 'register' && empty($success)) || ($action === '' && !empty($ref_code))) ? 'block' : 'hidden'; ?> transition-all duration-500">
                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
                        <p class="text-gray-500 mt-2">Start your journey with us today.</p>
                    </div>

                    <form method="POST" class="space-y-5">
                        <input type="hidden" name="action" value="register">
                        <div class="relative">
                            <input type="text" name="full_name" id="reg-name"
                                class="floating-input peer w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-transparent"
                                placeholder="Full Name" required>
                            <label for="reg-name"
                                class="absolute left-3 transition-all duration-200 pointer-events-none">Full
                                Name</label>
                            <i data-lucide="user"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        </div>
                        <div class="relative">
                            <input type="text" name="username" id="reg-user"
                                class="floating-input peer w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-transparent"
                                placeholder="Username" required>
                            <label for="reg-user"
                                class="absolute left-3 transition-all duration-200 pointer-events-none">Username</label>
                            <i data-lucide="at-sign"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        </div>
                        <div class="relative">
                            <input type="email" name="email" id="reg-email"
                                class="floating-input peer w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-transparent"
                                placeholder="Email" required>
                            <label for="reg-email"
                                class="absolute left-3 transition-all duration-200 pointer-events-none">Email
                                Address</label>
                            <i data-lucide="mail"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        </div>
                        <div class="relative">
                            <input type="password" name="password" id="reg-pass"
                                class="floating-input peer w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-transparent"
                                placeholder="Password" required>
                            <label for="reg-pass"
                                class="absolute left-3 transition-all duration-200 pointer-events-none">Password</label>
                            <button type="button" onclick="togglePassword('reg-pass')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"><i
                                    data-lucide="eye" class="w-5 h-5"></i></button>
                        </div>
                        <div class="relative">
                            <input type="text" name="referral_code" id="reg-ref"
                                class="floating-input peer w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-transparent"
                                placeholder="Referral Code" value="<?php echo htmlspecialchars($ref_code); ?>" required>
                            <label for="reg-ref"
                                class="absolute left-3 transition-all duration-200 pointer-events-none">Referral
                                Code</label>
                            <i data-lucide="share-2"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition-all shadow-lg">Sign
                            Up</button>
                    </form>
                    <p class="mt-8 text-center text-gray-600">Already have an account? <button onclick="toggleForms()"
                            class="text-indigo-600 font-bold hover:underline ml-1">Sign In</button></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function toggleForms() {
            document.getElementById('login-container').classList.toggle('hidden');
            document.getElementById('register-container').classList.toggle('hidden');
        }

        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>

</html>