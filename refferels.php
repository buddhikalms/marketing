<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch User Info
$user_query = $conn->prepare("SELECT username, referral_code, email, wallet_balance, points FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();
$user_query->close();

// Generate Referral Link
$ref_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/register.php?ref=" . $user['referral_code'];

// Fetch Referral Stats
// 1. Total Referrals (Direct)
$stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE parent_id = ?");
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$total_referrals = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

// 2. Pending Referrals (No sales yet)
$stmt_pending = $conn->prepare("SELECT COUNT(*) as total FROM users u WHERE u.parent_id = ? AND NOT EXISTS (SELECT 1 FROM sales s WHERE s.user_id = u.id)");
$stmt_pending->bind_param("i", $user_id);
$stmt_pending->execute();
$pending_referrals = $stmt_pending->get_result()->fetch_assoc()['total'];
$stmt_pending->close();

// 3. Fetch Referral List with Sales Status
$stmt_list = $conn->prepare("SELECT u.id, u.username, u.email, u.created_at, u.full_name, (SELECT COUNT(*) FROM sales s WHERE s.user_id = u.id) as sale_count FROM users u WHERE u.parent_id = ? ORDER BY u.created_at DESC");
$stmt_list->bind_param("i", $user_id);
$stmt_list->execute();
$referrals = $stmt_list->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Referrals - Marketing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-50 font-sans text-gray-800">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden relative md:ml-64 transition-all duration-300">

            <!-- Header -->
            <?php include 'header.php'; ?>

            <!-- Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-8">
                <div class="max-w-6xl mx-auto">

                    <div class="mb-8">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Referral Program</h1>
                        <p class="text-gray-500 mt-1">Track your network growth and earnings.</p>
                    </div>

                    <!-- Referral Link Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Unique Referral Link</h2>
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="relative flex-1">
                                <input type="text" id="refLink" value="<?php echo $ref_link; ?>" readonly
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-600 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 pr-10">
                                <i data-lucide="link"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            </div>
                            <button onclick="copyLink()"
                                class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2 font-medium shadow-lg shadow-indigo-500/30">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                                Copy Link
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Total Referrals -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4">
                            <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                                <i data-lucide="users" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Total Referrals</p>
                                <h3 class="text-2xl font-bold text-gray-900"><?php echo $total_referrals; ?></h3>
                            </div>
                        </div>

                        <!-- Pending (Mock Data) -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4">
                            <div class="p-3 bg-yellow-100 text-yellow-600 rounded-lg">
                                <i data-lucide="clock" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Pending Status</p>
                                <h3 class="text-2xl font-bold text-gray-900"><?php echo $pending_referrals; ?></h3>
                            </div>
                        </div>

                        <!-- Earned Rewards (Mock Data) -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4">
                            <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                                <i data-lucide="dollar-sign" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Earned Rewards</p>
                                <h3 class="text-2xl font-bold text-gray-900">Rs.
                                    <?php echo number_format($user['wallet_balance'], 2); ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Referrals Table -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Referral History</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">User</th>
                                        <th scope="col" class="px-6 py-3">Joined Date</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($referrals->num_rows > 0): ?>
                                        <?php while ($row = $referrals->fetch_assoc()): ?>
                                            <?php $is_active = $row['sale_count'] > 0; ?>
                                            <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 font-medium text-gray-900">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                                            <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <div class="font-semibold">
                                                                <?php echo htmlspecialchars($row['full_name']); ?></div>
                                                            <div class="text-xs text-gray-400">
                                                                @<?php echo htmlspecialchars($row['username']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php if ($is_active): ?>
                                                        <span
                                                            class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full border border-green-200">Active</span>
                                                    <?php else: ?>
                                                        <span
                                                            class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full border border-yellow-200">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button onclick="openUserModal(<?php echo $row['id']; ?>)"
                                                        class="text-indigo-600 hover:text-indigo-900 font-medium">View</button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                                No referrals found. Share your link to get started!
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>

            <!-- User Details Modal -->
            <div id="userModal"
                class="fixed inset-0 bg-gray-900/50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
                <div
                    class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform transition-all scale-100 overflow-hidden">
                    <div class="bg-indigo-600 p-6 flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold text-white" id="modal_fullname">User Details</h3>
                            <p class="text-indigo-200 text-sm" id="modal_username">@username</p>
                        </div>
                        <button onclick="closeUserModal()" class="text-white/80 hover:text-white transition-colors">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Email</p>
                                <p class="text-gray-800 font-medium truncate" id="modal_email">-</p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Joined Date</p>
                                <p class="text-gray-800 font-medium" id="modal_joined">-</p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Total Sales</p>
                                <p class="text-green-600 font-bold" id="modal_sales">-</p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Location</p>
                                <p class="text-gray-800 font-medium" id="modal_location">-</p>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <div class="flex items-center gap-2 text-gray-600 mb-2">
                                <i data-lucide="phone" class="w-4 h-4"></i>
                                <span id="modal_contact">-</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                                <span id="modal_address">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end">
                        <button onclick="closeUserModal()"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">Close</button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
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

        function openUserModal(userId) {
            // Show loading state or clear previous data
            document.getElementById('modal_fullname').textContent = 'Loading...';
            document.getElementById('userModal').classList.remove('hidden');

            fetch(`get_user_details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        closeUserModal();
                        return;
                    }

                    document.getElementById('modal_fullname').textContent = data.full_name || 'N/A';
                    document.getElementById('modal_username').textContent = '@' + data.username;
                    document.getElementById('modal_email').textContent = data.email;
                    document.getElementById('modal_joined').textContent = new Date(data.created_at)
                        .toLocaleDateString();
                    document.getElementById('modal_sales').textContent = 'Rs. ' + parseFloat(data.total_sales)
                        .toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        });
                    document.getElementById('modal_location').textContent = (data.city || '-') + ', ' + (data
                        .district || '-');
                    document.getElementById('modal_contact').textContent = data.contact_number || 'No contact info';
                    document.getElementById('modal_address').textContent = data.address || 'No address info';
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to load user details');
                    closeUserModal();
                });
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
        }
    </script>
</body>

</html>