<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch Root User Info
$user_query = $conn->prepare("SELECT username, full_name, referral_code FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$root_user = $user_query->get_result()->fetch_assoc();
$user_query->close();

// Recursive function to fetch downline
function getDownline($conn, $parent_id, $level = 1, $max_depth = 5)
{
    if ($level > $max_depth) return [];

    $children = [];
    $stmt = $conn->prepare("SELECT id, username, full_name, created_at, (SELECT COUNT(*) FROM users WHERE parent_id = u.id) as direct_referrals FROM users u WHERE parent_id = ?");
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $temp_rows = [];
    while ($row = $result->fetch_assoc()) {
        $temp_rows[] = $row;
    }
    $stmt->close();

    foreach ($temp_rows as $row) {
        $row['children'] = getDownline($conn, $row['id'], $level + 1, $max_depth);
        $children[] = $row;
    }
    return $children;
}

$tree = getDownline($conn, $user_id);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Tree - Marketing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Tree CSS */
        .tree ul {
            padding-top: 20px;
            position: relative;
            display: flex;
            justify-content: center;
        }

        .tree li {
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 20px 5px 0 5px;
        }

        /* Connectors */
        .tree li::before,
        .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 1px solid #ccc;
            width: 50%;
            height: 20px;
        }

        .tree li::after {
            right: auto;
            left: 50%;
            border-left: 1px solid #ccc;
        }

        /* Remove connectors from root */
        .tree li:only-child::after,
        .tree li:only-child::before {
            display: none;
        }

        .tree li:only-child {
            padding-top: 0;
        }

        /* Remove left connector from first child and right from last child */
        .tree li:first-child::before,
        .tree li:last-child::after {
            border: 0 none;
        }

        /* Adding back the vertical connector to the last nodes */
        .tree li:last-child::before {
            border-right: 1px solid #ccc;
            border-radius: 0 5px 0 0;
        }

        .tree li:first-child::after {
            border-radius: 5px 0 0 0;
        }

        /* Downward connectors from parents */
        .tree ul ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 1px solid #ccc;
            width: 0;
            height: 20px;
        }

        .tree-node {
            border: 1px solid #e5e7eb;
            padding: 10px;
            display: inline-block;
            border-radius: 8px;
            background: white;
            min-width: 140px;
            transition: all 0.3s;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .tree-node:hover {
            background: #eff6ff;
            border-color: #93c5fd;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-800">
    <div class="flex h-screen overflow-hidden">
        <?php include 'sidebar.php'; ?>
        <div class="flex-1 flex flex-col overflow-hidden relative md:ml-64 transition-all duration-300">
            <?php include 'header.php'; ?>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-8">
                <div class="max-w-full mx-auto">
                    <div class="mb-8">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Network Tree</h1>
                        <p class="text-gray-500 mt-1">Visual representation of your referral network.</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 overflow-auto">
                        <div class="tree min-w-max">
                            <ul>
                                <li>
                                    <div class="tree-node relative group ring-2 ring-indigo-100 cursor-pointer"
                                        onclick="openUserModal(<?php echo $user_id; ?>)">
                                        <div class="flex flex-col items-center gap-2">
                                            <div
                                                class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold shadow-md">
                                                <?php echo strtoupper(substr($root_user['username'], 0, 1)); ?>
                                            </div>
                                            <div class="font-bold text-gray-900">
                                                <?php echo htmlspecialchars($root_user['username']); ?></div>
                                            <div
                                                class="text-xs text-indigo-600 font-medium bg-indigo-50 px-2 py-0.5 rounded-full">
                                                You</div>
                                        </div>
                                    </div>
                                    <?php if (!empty($tree)): ?>
                                        <?php renderTree($tree); ?>
                                    <?php endif; ?>
                                </li>
                            </ul>
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
                                <p class="text-xs text-gray-500 uppercase font-semibold">Points Balance</p>
                                <p class="text-indigo-600 font-bold text-lg" id="modal_points">-</p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Total Sales</p>
                                <p class="text-green-600 font-bold text-lg" id="modal_sales">-</p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Wallet Balance</p>
                                <p class="text-gray-800 font-medium" id="modal_wallet">-</p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Joined Date</p>
                                <p class="text-gray-800 font-medium" id="modal_joined">-</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 space-y-2">
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                                <span id="modal_email">-</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="phone" class="w-4 h-4"></i>
                                <span id="modal_contact">-</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                                <span id="modal_address">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

        function openUserModal(userId) {
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
                    document.getElementById('modal_points').textContent = (data.points || 0) + ' Points';
                    document.getElementById('modal_sales').textContent = 'Rs. ' + parseFloat(data.total_sales)
                        .toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        });
                    document.getElementById('modal_wallet').textContent = 'Rs. ' + parseFloat(data.wallet_balance || 0)
                        .toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        });
                    document.getElementById('modal_joined').textContent = new Date(data.created_at)
                        .toLocaleDateString();
                    document.getElementById('modal_email').textContent = data.email;
                    document.getElementById('modal_contact').textContent = data.contact_number || 'No contact info';
                    document.getElementById('modal_address').textContent = (data.city || '') + (data.city && data
                        .district ? ', ' : '') + (data.district || '');
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

<?php
function renderTree($nodes)
{
    echo "<ul>";
    foreach ($nodes as $node) {
        echo "<li>";
        echo '<div class="tree-node cursor-pointer" onclick="openUserModal(' . $node['id'] . ')">';
        echo '<div class="flex flex-col items-center gap-1">';
        echo '<div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">' . strtoupper(substr($node['username'], 0, 1)) . '</div>';
        echo '<div class="font-medium text-gray-900 text-sm">' . htmlspecialchars($node['username']) . '</div>';
        echo '<div class="text-xs text-gray-500">Direct: ' . $node['direct_referrals'] . '</div>';
        echo '</div>';
        echo '</div>';
        if (!empty($node['children'])) {
            renderTree($node['children']);
        }
        echo "</li>";
    }
    echo "</ul>";
}
?>