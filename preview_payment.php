<?php
session_start();
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['user_id'])) {
    header("Location: add_payment.php");
    exit;
}

$user_id = intval($_POST['user_id']);
$amount = floatval($_POST['amount']);
$transaction_id = $_POST['transaction_id'] ?? '';

// Fetch User Details for Preview
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Invoice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-100 py-10">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg relative overflow-hidden">
        <!-- Watermark -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-10">
            <span class="text-9xl font-bold text-gray-500 transform -rotate-45">PREVIEW</span>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-start border-b pb-8 mb-8 relative z-10">
            <div>
                <h1 class="text-3xl font-bold text-gray-400">INVOICE PREVIEW</h1>
                <p class="text-gray-500 mt-1">Date: <?php echo date('Y-m-d'); ?></p>
            </div>
            <div class="text-right">
                <h2 class="font-bold text-xl text-gray-800">MarketingSys Inc.</h2>
                <p class="text-gray-500 text-sm">123 Business Road<br>Colombo, Sri Lanka</p>
            </div>
        </div>

        <!-- Bill To -->
        <div class="mb-8 relative z-10">
            <h3 class="text-gray-600 uppercase text-xs font-bold tracking-wider mb-2">Bill To:</h3>
            <div class="text-gray-800 font-medium text-lg"><?php echo htmlspecialchars($user['username']); ?></div>
            <div class="text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
            <div class="text-gray-500"><?php echo htmlspecialchars($user['city'] ?? ''); ?></div>
            <div class="text-gray-500"><?php echo htmlspecialchars($user['contact_number'] ?? ''); ?></div>
        </div>

        <!-- Details -->
        <table class="w-full mb-8 relative z-10">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b">
                    <td class="px-4 py-4">Payment Transaction (ID: <?php echo htmlspecialchars($transaction_id ?: 'Pending'); ?>)</td>
                    <td class="px-4 py-4 text-right font-medium">Rs. <?php echo number_format($amount, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Total -->
        <div class="flex justify-end mb-8 relative z-10">
            <div class="text-right">
                <div class="text-gray-600 text-sm">Total Amount</div>
                <div class="text-3xl font-bold text-indigo-600">Rs. <?php echo number_format($amount, 2); ?></div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-center gap-4 mt-12 relative z-10">
            <button onclick="history.back()" class="flex items-center gap-2 border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 font-medium">Edit Details</button>

            <form action="process_payment.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                <input type="hidden" name="transaction_id" value="<?php echo htmlspecialchars($transaction_id); ?>">
                <button type="submit" class="flex items-center gap-2 bg-indigo-600 text-white px-8 py-3 rounded-lg hover:bg-indigo-700 font-bold shadow-lg shadow-indigo-500/30">Confirm & Save Invoice</button>
            </form>
        </div>
    </div>
</body>

</html>