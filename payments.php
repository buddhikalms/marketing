<?php
session_start();
include 'config/db.php';

// Fetch payments
$sql = "SELECT p.*, u.username FROM payments p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - MarketingSys</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden relative md:ml-64 transition-all duration-300">
            <header class="bg-white shadow-sm z-10 p-4 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()"
                        class="md:hidden text-gray-500 hover:text-indigo-600 focus:outline-none">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800">Payments</h2>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">Welcome, Admin</span>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Transaction History</h3>
                    <a href="add_payment.php"
                        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        Add Payment
                    </a>
                </div>

                <!-- Payments Table -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">User</th>
                                    <th class="px-6 py-4">Amount</th>
                                    <th class="px-6 py-4">Transaction ID</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 text-gray-600">#<?php echo $row['id']; ?></td>
                                            <td class="px-6 py-4 font-medium text-gray-900">
                                                <?php echo htmlspecialchars($row['username'] ?? 'Unknown'); ?></td>
                                            <td class="px-6 py-4 text-green-600 font-medium">Rs.
                                                <?php echo number_format($row['amount'], 2); ?></td>
                                            <td class="px-6 py-4 text-gray-500 text-sm">
                                                <?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                            <td class="px-6 py-4 text-gray-500 text-sm">
                                                <?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No payments found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
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