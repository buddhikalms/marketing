<?php
session_start();
require_once 'config/db.php';

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$msg_type = ""; // success or error

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nic_number = trim($_POST['nic_number']);
    $gender = $_POST['gender'];
    $district = trim($_POST['district']);
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);

    if (!empty($user_id)) {
        $stmt = $conn->prepare("UPDATE users SET nic_number = ?, gender = ?, district = ?, city = ?, address = ?, contact_number = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $nic_number, $gender, $district, $city, $address, $contact_number, $user_id);

        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            $msg_type = "success";
        } else {
            $message = "Error updating profile. Please try again.";
            $msg_type = "error";
        }
        $stmt->close();
    }
}

// 3. Fetch Current User Data
$stmt = $conn->prepare("SELECT username, email, full_name, nic_number, gender, district, city, address, contact_number FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fallback if user not found (shouldn't happen if logged in)
if (!$user) {
    session_destroy();
    header("Location: auth.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - MarketingSys</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-50 font-sans text-gray-800">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Component -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden relative md:ml-64 transition-all duration-300">

            <!-- Header Component -->
            <?php include 'header.php'; ?>

            <!-- Scrollable Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-8">
                <div class="max-w-4xl mx-auto">
                    <!-- Page Header -->
                    <div class="mb-8">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Profile Settings</h1>
                        <p class="text-gray-500 mt-1">Manage your personal information and account details.</p>
                    </div>

                    <!-- Alert Message -->
                    <?php if ($message): ?>
                        <div
                            class="mb-6 p-4 rounded-lg flex items-center gap-3 <?php echo $msg_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                            <i data-lucide="<?php echo $msg_type === 'success' ? 'check-circle' : 'alert-circle'; ?>"
                                class="w-5 h-5"></i>
                            <span><?php echo htmlspecialchars($message); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 md:p-8">
                            <form method="POST" action="">
                                <!-- Read Only Section -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Username</label>
                                        <div
                                            class="flex items-center px-4 py-3 bg-gray-100 rounded-lg border border-gray-200 text-gray-600 cursor-not-allowed">
                                            <i data-lucide="user" class="w-4 h-4 mr-3 text-gray-400"></i>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Email
                                            Address</label>
                                        <div
                                            class="flex items-center px-4 py-3 bg-gray-100 rounded-lg border border-gray-200 text-gray-600 cursor-not-allowed">
                                            <i data-lucide="mail" class="w-4 h-4 mr-3 text-gray-400"></i>
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </div>
                                    </div>
                                </div>

                                <hr class="border-gray-100 mb-8">

                                <!-- Editable Fields -->
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <!-- NIC -->
                                    <div>
                                        <label for="nic_number" class="block text-sm font-medium text-gray-700 mb-1">NIC
                                            Number</label>
                                        <input type="text" name="nic_number" id="nic_number"
                                            value="<?php echo htmlspecialchars($user['nic_number'] ?? ''); ?>"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                            placeholder="Enter NIC">
                                    </div>

                                    <!-- Contact -->
                                    <div>
                                        <label for="contact_number"
                                            class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                        <input type="text" name="contact_number" id="contact_number"
                                            value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                            placeholder="Enter phone number">
                                    </div>

                                    <!-- Gender -->
                                    <div>
                                        <label for="gender"
                                            class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                        <select name="gender" id="gender"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                            <option value="">Select Gender</option>
                                            <option value="Male"
                                                <?php echo ($user['gender'] === 'Male') ? 'selected' : ''; ?>>Male
                                            </option>
                                            <option value="Female"
                                                <?php echo ($user['gender'] === 'Female') ? 'selected' : ''; ?>>Female
                                            </option>
                                            <option value="Other"
                                                <?php echo ($user['gender'] === 'Other') ? 'selected' : ''; ?>>Other
                                            </option>
                                        </select>
                                    </div>

                                    <!-- City -->
                                    <div>
                                        <label for="city"
                                            class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                        <input type="text" name="city" id="city"
                                            value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    </div>

                                    <!-- District -->
                                    <div>
                                        <label for="district"
                                            class="block text-sm font-medium text-gray-700 mb-1">District</label>
                                        <input type="text" name="district" id="district"
                                            value="<?php echo htmlspecialchars($user['district'] ?? ''); ?>"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    </div>

                                    <!-- Address (Full Width) -->
                                    <div class="md:col-span-2">
                                        <label for="address"
                                            class="block text-sm font-medium text-gray-700 mb-1">Residential
                                            Address</label>
                                        <textarea name="address" id="address" rows="3"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-8 flex justify-end">
                                    <button type="submit"
                                        class="flex items-center gap-2 bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-500/30 transition-all shadow-lg shadow-indigo-500/20 font-medium">
                                        <i data-lucide="save" class="w-4 h-4"></i>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer Component -->
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }
    </script>
</body>

</html>