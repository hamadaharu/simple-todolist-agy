<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_todos = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = ? AND is_completed = 1");
$stmt->execute([$user_id]);
$completed_todos = $stmt->fetchColumn();

$pending_todos = $total_todos - $completed_todos;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_categories = $stmt->fetchColumn();

// Get recent todos
$stmt = $pdo->prepare("SELECT t.*, c.name as category_name FROM todos t LEFT JOIN categories c ON t.category_id = c.id WHERE t.user_id = ? ORDER BY t.created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_todos = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto pt-16 md:pt-0 bg-gray-50">
    <div class="p-8">
        <header class="mb-8 flex justify-between items-end">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Hello, <?= htmlspecialchars($_SESSION['username']) ?>! 👋</h2>
                <p class="text-gray-500 mt-1">Here is a summary of your tasks today.</p>
            </div>
            <a href="todos.php" class="hidden md:inline-flex bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-medium transition-colors shadow-sm items-center gap-2">
                <i class="fas fa-plus"></i> New Task
            </a>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Tasks</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $total_todos ?></p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $completed_todos ?></p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center text-xl">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $pending_todos ?></p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Categories</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $total_categories ?></p>
                </div>
            </div>
        </div>

        <!-- Recent Tasks -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Recent Tasks</h3>
                <a href="todos.php" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">View All &rarr;</a>
            </div>
            <div class="p-0">
                <?php if (empty($recent_todos)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                        <p>No tasks found. Start by creating one!</p>
                        <a href="todos.php" class="inline-block mt-4 text-indigo-600 font-medium hover:underline">Create Task</a>
                    </div>
                <?php else: ?>
                    <ul class="divide-y divide-gray-100">
                        <?php foreach($recent_todos as $todo): ?>
                        <li class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <?php if($todo['is_completed']): ?>
                                    <div class="w-6 h-6 rounded-full bg-green-100 text-green-500 flex items-center justify-center">
                                        <i class="fas fa-check text-xs"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300"></div>
                                <?php endif; ?>
                                <div>
                                    <p class="text-gray-800 font-medium <?= $todo['is_completed'] ? 'line-through text-gray-400' : '' ?>"><?= htmlspecialchars($todo['title']) ?></p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <?php if($todo['category_name']): ?>
                                            <span class="text-xs px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-full font-medium">
                                                <?= htmlspecialchars($todo['category_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="text-xs text-gray-400"><i class="far fa-clock"></i> <?= date('M d, Y', strtotime($todo['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
