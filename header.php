<header class="bg-white shadow-sm z-10">
    <div class="flex items-center justify-between px-6 py-4">
        <!-- Mobile Menu Button -->
        <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-indigo-600 focus:outline-none">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>

        <!-- Search Bar -->
        <div class="hidden md:flex items-center bg-gray-100 rounded-lg px-3 py-2 w-64">
            <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
            <input type="text" placeholder="Search..." class="bg-transparent border-none focus:outline-none ml-2 text-sm text-gray-600 w-full">
        </div>

        <!-- Right Side Actions -->
        <div class="flex items-center gap-4">
            <!-- Notifications -->
            <button class="relative p-2 text-gray-400 hover:text-indigo-600 transition-colors">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Profile Dropdown -->
            <div class="relative group">
                <button class="flex items-center gap-2 focus:outline-none">
                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold">
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                    </div>
                    <span class="hidden md:block text-sm font-medium text-gray-700"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                </button>
            </div>
        </div>
    </div>
</header>