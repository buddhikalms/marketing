<?php
session_start();
require_once 'config/db.php';

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User Stats
$stmt = $conn->prepare("
    SELECT 
        u.username, u.full_name, u.referral_code, u.wallet_balance, u.points,
        (SELECT COUNT(*) FROM users WHERE parent_id = u.id) as total_referrals,
        (SELECT COALESCE(SUM(amount), 0) FROM sales WHERE user_id = u.id) as total_sales
    FROM users u 
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// 3. Fetch Recent Referrals
$stmt_ref = $conn->prepare("SELECT username, created_at FROM users WHERE parent_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt_ref->bind_param("i", $user_id);
$stmt_ref->execute();
$recent_referrals = $stmt_ref->get_result();
$stmt_ref->close();

$ref_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/register.php?ref=" . $user['referral_code'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MarketingSys</title>
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
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Dashboard</h1>
                        <p class="text-gray-500 mt-1">Welcome back, <?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>!</p>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Wallet -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                                    <i data-lucide="wallet" class="w-6 h-6"></i>
                                </div>
                                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Available</span>
                            </div>
                            <p class="text-sm text-gray-500 font-medium">Wallet Balance</p>
                            <h3 class="text-2xl font-bold text-gray-900">Rs. <?php echo number_format($user['wallet_balance'], 2); ?></h3>
                        </div>

                        <!-- Points -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-purple-100 text-purple-600 rounded-lg">
                                    <i data-lucide="star" class="w-6 h-6"></i>
                                </div>
                                <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">Rewards</span>
                            </div>
                            <p class="text-sm text-gray-500 font-medium">Total Points</p>
                            <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($user['points']); ?></h3>
                        </div>

                        <!-- Sales -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                                    <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                                </div>
                                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Personal</span>
                            </div>
                            <p class="text-sm text-gray-500 font-medium">My Sales Volume</p>
                            <h3 class="text-2xl font-bold text-gray-900">Rs. <?php echo number_format($user['total_sales'], 2); ?></h3>
                        </div>

                        <!-- Referrals -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-orange-100 text-orange-600 rounded-lg">
                                    <i data-lucide="users" class="w-6 h-6"></i>
                                </div>
                                <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded-full">Network</span>
                            </div>
                            <p class="text-sm text-gray-500 font-medium">Direct Referrals</p>
                            <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($user['total_referrals']); ?></h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Referral Link Widget -->
                        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Referral Link</h2>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="relative flex-1">
                                    <input type="text" id="refLink" value="<?php echo $ref_link; ?>" readonly
                                        class="w-full bg-gray-50 border border-gray-300 text-gray-600 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 pr-10">
                                    <i data-lucide="link" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                                </div>
                                <button onclick="copyLink()"
                                    class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2 font-medium shadow-lg shadow-indigo-500/30">
                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                    Copy
                                </button>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Referrals</h2>
                            <div class="space-y-4">
                                <?php if ($recent_referrals->num_rows > 0): ?>
                                    <?php while ($row = $recent_referrals->fetch_assoc()): ?>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                                <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">@<?php echo htmlspecialchars($row['username']); ?></p>
                                                <p class="text-xs text-gray-500">Joined <?php echo date('M d', strtotime($row['created_at'])); ?></p>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500">No recent referrals.</p>
                                <?php endif; ?>
                            </div>
                            <a href="refferels.php" class="block mt-4 text-sm text-indigo-600 hover:text-indigo-800 font-medium">View All Referrals &rarr;</a>
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

        function copyLink() {
            var copyText = document.getElementById("refLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value).then(function() {
                alert("Referral link copied to clipboard!");
            });
        }
    </script>
</body>

</html>