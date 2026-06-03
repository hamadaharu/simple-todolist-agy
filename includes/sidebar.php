<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-white border-r border-gray-200 h-full flex flex-col shadow-sm hidden md:flex">
    <div class="h-16 flex items-center px-6 border-b border-gray-200">
        <h1 class="text-xl font-bold text-indigo-600 flex items-center gap-2">
            <i class="fas fa-check-square"></i> TodoMaster
        </h1>
    </div>
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $currentPage == 'index.php' ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
            <i class="fas fa-home w-5"></i> Dashboard
        </a>
        <a href="todos.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $currentPage == 'todos.php' ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
            <i class="fas fa-tasks w-5"></i> My Todos
        </a>
        <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $currentPage == 'categories.php' ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
            <i class="fas fa-tags w-5"></i> Categories
        </a>
    </nav>
    <div class="p-4 border-t border-gray-200">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
            <i class="fas fa-sign-out-alt w-5"></i> Logout
        </a>
    </div>
</aside>

<!-- Mobile header -->
<div class="md:hidden fixed top-0 left-0 right-0 h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 z-10">
    <h1 class="text-xl font-bold text-indigo-600"><i class="fas fa-check-square"></i> TodoMaster</h1>
    <a href="logout.php" class="text-gray-500 hover:text-red-600"><i class="fas fa-sign-out-alt text-xl"></i></a>
</div>
