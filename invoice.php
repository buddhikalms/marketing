<?php
session_start();
include 'config/db.php';

if (!isset($_GET['id'])) {
    die("Invalid Invoice ID");
}

$payment_id = intval($_GET['id']);

// Fetch Payment & User Details
$sql = "SELECT p.*, u.username, u.email, u.city, u.contact_number 
        FROM payments p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invoice not found.");
}

$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $data['id']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .shadow-lg {
                box-shadow: none;
            }
        }
    </style>
</head>

<body class="bg-gray-100 py-10">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg">
        <!-- Header -->
        <div class="flex justify-between items-start border-b pb-8 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-indigo-900">INVOICE</h1>
                <p class="text-gray-500 mt-1">#INV-<?php echo str_pad($data['id'], 6, '0', STR_PAD_LEFT); ?></p>
            </div>
            <div class="text-right">
                <h2 class="font-bold text-xl text-gray-800">MarketingSys Inc.</h2>
                <p class="text-gray-500 text-sm">123 Business Road<br>Colombo, Sri Lanka</p>
            </div>
        </div>

        <!-- Bill To -->
        <div class="mb-8">
            <h3 class="text-gray-600 uppercase text-xs font-bold tracking-wider mb-2">Bill To:</h3>
            <div class="text-gray-800 font-medium text-lg"><?php echo htmlspecialchars($data['username']); ?></div>
            <div class="text-gray-500"><?php echo htmlspecialchars($data['email']); ?></div>
            <div class="text-gray-500"><?php echo htmlspecialchars($data['city'] ?? ''); ?></div>
            <div class="text-gray-500"><?php echo htmlspecialchars($data['contact_number'] ?? ''); ?></div>
        </div>

        <!-- Details -->
        <table class="w-full mb-8">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase">
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b">
                    <td class="px-4 py-4">Payment Transaction (ID:
                        <?php echo htmlspecialchars($data['transaction_id'] ?? 'N/A'); ?>)</td>
                    <td class="px-4 py-4 text-right font-medium">Rs. <?php echo number_format($data['amount'], 2); ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Total -->
        <div class="flex justify-end mb-8">
            <div class="text-right">
                <div class="text-gray-600 text-sm">Total Amount</div>
                <div class="text-3xl font-bold text-indigo-600">Rs. <?php echo number_format($data['amount'], 2); ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-center gap-4 no-print mt-12">
            <button onclick="window.print()"
                class="flex items-center gap-2 bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-900"><i
                    data-lucide="printer" class="w-4 h-4"></i> Print Invoice</button>
            <a href="payments.php"
                class="flex items-center gap-2 border border-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-50"><i
                    data-lucide="arrow-left" class="w-4 h-4"></i> Back to Payments</a>
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>