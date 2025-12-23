<?php
session_start();
include 'config/db.php';

$message = '';
$error = '';

// Fetch users for dropdown
$users = $conn->query("SELECT id, username FROM users ORDER BY username");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment - MarketingSys</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <?php include 'sidebar.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm p-8">
                    <div class="flex items-center gap-3 mb-6 border-b pb-4">
                        <a href="payments.php" class="text-gray-500 hover:text-indigo-600"><i data-lucide="arrow-left"
                                class="w-6 h-6"></i></a>
                        <h2 class="text-2xl font-bold text-gray-800">Add New Payment</h2>
                    </div>

                    <?php if ($message): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <?php echo $message; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="preview_payment.php" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                            <select name="user_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                                <option value="">-- Select User --</option>
                                <?php while ($u = $users->fetch_assoc()): ?>
                                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?>
                                        (ID: <?php echo $u['id']; ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount (Rs.)</label>
                            <input type="number" name="amount" step="0.01" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                                placeholder="2000.00">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction ID
                                (Optional)</label>
                            <input type="text" name="transaction_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                                placeholder="TXN12345678">
                        </div>

                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition-all shadow-lg shadow-indigo-500/30">Process
                            Payment & Preview</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>