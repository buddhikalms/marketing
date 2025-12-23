<?php
session_start();
include 'config/db.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// --- AJAX Handler for User Details ---
if (isset($_GET['fetch_user']) && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $user_id = intval($_GET['id']);

    // Fetch specific user details
    $stmt = $conn->prepare("SELECT id, username, full_name, email, nic_number, gender, district, city, address, contact_number, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    exit;
}

// --- Fetch Data for Tree View ---
// Grouping by District -> City -> Users
$sql = "SELECT id, username, district, city FROM users ORDER BY district ASC, city ASC, username ASC";
$result = $conn->query($sql);

$tree_data = [];
while ($row = $result->fetch_assoc()) {
    $district = empty($row['district']) ? 'Unassigned District' : ucwords(strtolower($row['district']));
    $city = empty($row['city']) ? 'Unassigned City' : ucwords(strtolower($row['city']));

    $tree_data[$district][$city][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Marketing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .tree-content {
            transition: all 0.3s ease-in-out;
            overflow: hidden;
        }

        .tree-content.collapsed {
            max-height: 0;
            opacity: 0;
        }

        .tree-content.expanded {
            max-height: 2000px;
            opacity: 1;
        }

        .chevron {
            transition: transform 0.2s;
        }

        .expanded>button>.chevron {
            transform: rotate(90deg);
        }

        /* Custom Scrollbar for Tree */
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: #c7c7c7;
            border-radius: 3px;
        }

        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-gray-800">
    <div class="flex h-screen overflow-hidden">
        <?php include 'sidebar.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden relative md:ml-64 transition-all duration-300">
            <?php include 'header.php'; ?>

            <main class="flex-1 flex overflow-hidden bg-gray-100">
                <!-- Left Sidebar: Tree View -->
                <div class="w-80 bg-white border-r border-gray-200 flex flex-col shadow-sm z-10">
                    <div class="p-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="font-bold text-gray-700 flex items-center gap-2">
                            <i data-lucide="network" class="w-4 h-4 text-indigo-600"></i>
                            User Directory
                        </h2>
                    </div>
                    <div class="flex-1 overflow-y-auto custom-scroll p-2">
                        <div class="space-y-1">
                            <?php foreach ($tree_data as $district => $cities): ?>
                                <div class="tree-group">
                                    <button onclick="toggleTree(this)" class="w-full flex items-center gap-2 p-2 hover:bg-gray-50 rounded-md text-left text-sm font-medium text-gray-700 transition-colors focus:outline-none group">
                                        <i data-lucide="chevron-right" class="chevron w-4 h-4 text-gray-400 group-hover:text-indigo-500"></i>
                                        <i data-lucide="map" class="w-4 h-4 text-indigo-500"></i>
                                        <span><?php echo htmlspecialchars($district); ?></span>
                                        <span class="ml-auto text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-full"><?php echo count($cities); ?></span>
                                    </button>
                                    <div class="tree-content collapsed pl-4 border-l border-gray-100 ml-3 my-1">
                                        <?php foreach ($cities as $city => $users): ?>
                                            <div class="tree-group">
                                                <button onclick="toggleTree(this)" class="w-full flex items-center gap-2 p-2 hover:bg-gray-50 rounded-md text-left text-sm text-gray-600 transition-colors focus:outline-none group">
                                                    <i data-lucide="chevron-right" class="chevron w-3 h-3 text-gray-400 group-hover:text-indigo-500"></i>
                                                    <i data-lucide="map-pin" class="w-3 h-3 text-indigo-400"></i>
                                                    <span><?php echo htmlspecialchars($city); ?></span>
                                                    <span class="ml-auto text-xs text-gray-400"><?php echo count($users); ?></span>
                                                </button>
                                                <div class="tree-content collapsed pl-4 border-l border-gray-100 ml-2.5 my-1">
                                                    <?php foreach ($users as $user): ?>
                                                        <button onclick="loadUserDetails(<?php echo $user['id']; ?>)" class="w-full flex items-center gap-2 p-2 hover:bg-indigo-50 hover:text-indigo-700 rounded-md text-left text-sm text-gray-500 transition-colors focus:outline-none group user-item" data-id="<?php echo $user['id']; ?>">
                                                            <i data-lucide="user" class="w-3 h-3 text-gray-300 group-hover:text-indigo-400"></i>
                                                            <span class="truncate"><?php echo htmlspecialchars($user['username']); ?></span>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Content: Detail View -->
                <div class="flex-1 overflow-y-auto bg-gray-50 p-4 md:p-8" id="main-content">
                    <div id="empty-state" class="h-full flex flex-col items-center justify-center text-gray-400">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="user-search" class="w-8 h-8 text-gray-300"></i>
                        </div>
                        <p class="text-lg font-medium">Select a user to view details</p>
                    </div>

                    <div id="user-details" class="hidden max-w-3xl mx-auto">
                        <!-- Profile Header -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                            <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                            <div class="px-8 pb-8 relative">
                                <div class="absolute -top-12 left-8">
                                    <div class="w-24 h-24 bg-white rounded-full p-1 shadow-lg">
                                        <div id="detail-avatar" class="w-full h-full bg-indigo-100 rounded-full flex items-center justify-center text-2xl font-bold text-indigo-600">U</div>
                                    </div>
                                </div>
                                <div class="pt-14 flex justify-between items-start">
                                    <div>
                                        <h1 id="detail-fullname" class="text-2xl font-bold text-gray-900">User Name</h1>
                                        <p id="detail-username" class="text-gray-500 font-medium">@username</p>
                                    </div>
                                    <button onclick="copyDetails()" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                                        <i data-lucide="copy" class="w-4 h-4"></i>
                                        Copy Details
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i data-lucide="user-check" class="w-5 h-5 text-indigo-500"></i>
                                    Personal Information
                                </h3>
                                <div class="space-y-4">
                                    <div><label class="text-xs font-semibold text-gray-400 uppercase">NIC Number</label>
                                        <p id="detail-nic" class="text-gray-900 font-medium mt-0.5">-</p>
                                    </div>
                                    <div><label class="text-xs font-semibold text-gray-400 uppercase">Gender</label>
                                        <p id="detail-gender" class="text-gray-900 font-medium mt-0.5">-</p>
                                    </div>
                                    <div><label class="text-xs font-semibold text-gray-400 uppercase">Contact</label>
                                        <p id="detail-contact" class="text-gray-900 font-medium mt-0.5">-</p>
                                    </div>
                                    <div><label class="text-xs font-semibold text-gray-400 uppercase">Email</label>
                                        <p id="detail-email" class="text-gray-900 font-medium mt-0.5">-</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <i data-lucide="map" class="w-5 h-5 text-indigo-500"></i>
                                    Location Details
                                </h3>
                                <div class="space-y-4">
                                    <div><label class="text-xs font-semibold text-gray-400 uppercase">District</label>
                                        <p id="detail-district" class="text-gray-900 font-medium mt-0.5">-</p>
                                    </div>
                                    <div><label class="text-xs font-semibold text-gray-400 uppercase">City</label>
                                        <p id="detail-city" class="text-gray-900 font-medium mt-0.5">-</p>
                                    </div>
                                    <div><label class="text-xs font-semibold text-gray-400 uppercase">Address</label>
                                        <p id="detail-address" class="text-gray-900 font-medium mt-0.5">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
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

        function toggleTree(btn) {
            const content = btn.nextElementSibling;
            const parent = btn.parentElement;
            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                content.classList.add('expanded');
                parent.classList.add('expanded');
            } else {
                content.classList.add('collapsed');
                content.classList.remove('expanded');
                parent.classList.remove('expanded');
            }
        }

        async function loadUserDetails(id) {
            document.querySelectorAll('.user-item').forEach(el => {
                el.classList.remove('bg-indigo-50', 'text-indigo-700');
                if (el.dataset.id == id) el.classList.add('bg-indigo-50', 'text-indigo-700');
            });

            try {
                const response = await fetch(`user_management.php?fetch_user=1&id=${id}`);
                const result = await response.json();
                if (result.success) {
                    const u = result.data;
                    document.getElementById('detail-fullname').textContent = u.full_name || 'N/A';
                    document.getElementById('detail-username').textContent = '@' + u.username;
                    document.getElementById('detail-avatar').textContent = u.username.charAt(0).toUpperCase();
                    document.getElementById('detail-nic').textContent = u.nic_number || '-';
                    document.getElementById('detail-gender').textContent = u.gender || '-';
                    document.getElementById('detail-contact').textContent = u.contact_number || '-';
                    document.getElementById('detail-email').textContent = u.email || '-';
                    document.getElementById('detail-district').textContent = u.district || '-';
                    document.getElementById('detail-city').textContent = u.city || '-';
                    document.getElementById('detail-address').textContent = u.address || '-';
                    document.getElementById('empty-state').classList.add('hidden');
                    document.getElementById('user-details').classList.remove('hidden');
                }
            } catch (e) {
                console.error(e);
                alert('Failed to fetch user data');
            }
        }

        function copyDetails() {
            const name = document.getElementById('detail-fullname').textContent;
            const nic = document.getElementById('detail-nic').textContent;
            const contact = document.getElementById('detail-contact').textContent;
            const address = document.getElementById('detail-address').textContent;
            navigator.clipboard.writeText(`Name: ${name}\nNIC: ${nic}\nContact: ${contact}\nAddress: ${address}`).then(() => alert('Copied!'));
        }
    </script>
</body>

</html>