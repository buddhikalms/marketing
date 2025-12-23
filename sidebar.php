<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Backdrop for Mobile -->
<div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-gray-900/50 z-20 hidden md:hidden glass">
</div>

<aside id="sidebar"
    class="fixed inset-y-0 left-0 w-64 bg-indigo-900 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-30 flex flex-col shadow-2xl">
    <!-- Brand -->
    <div class="h-16 flex items-center justify-center border-b border-indigo-800">
        <div class="flex items-center gap-2 font-bold text-xl tracking-wide">
            <i data-lucide="layout-dashboard" class="w-6 h-6 text-indigo-400"></i>
            <span>Marketing<span class="text-indigo-400">Sys</span></span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
        <a href="dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_page == 'dashboard.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white'; ?>">
            <i data-lucide="home" class="w-5 h-5"></i>
            <span>Dashboard</span>
        </a>

        <!-- <a href="dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_page == 'profile.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white'; ?>">
            <i data-lucide="user" class="w-5 h-5"></i>
            <span>Profile</span>
        </a> -->

        <a href="refferels.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_page == 'refferels.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white'; ?>">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span>Referrals</span>
        </a>

        <a href="tree_view.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_page == 'tree_view.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white'; ?>">
            <i data-lucide="git-fork" class="w-5 h-5"></i>
            <span>Network Tree</span>
        </a>

        <a href="user_management.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_page == 'user_management.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white'; ?>">
            <i data-lucide="users-round" class="w-5 h-5"></i>
            <span>User Management</span>
        </a>

        <a href="payments.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_page == 'payments.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white'; ?>">
            <i data-lucide="credit-card" class="w-5 h-5"></i>
            <span>Payments</span>
        </a>

        <a href="settings.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_page == 'settings.php' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-indigo-200 hover:bg-indigo-800 hover:text-white'; ?>">
            <i data-lucide="settings" class="w-5 h-5"></i>
            <span>Settings</span>
        </a>
    </nav>

    <!-- Logout -->
    <div class="p-4 border-t border-indigo-800">
        <a href="logout.php"
            class="flex items-center gap-3 px-4 py-2 text-red-300 hover:bg-indigo-800 hover:text-red-200 rounded-lg transition-colors">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>