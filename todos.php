<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle Todo Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description'] ?? '');
            $category_id = empty($_POST['category_id']) ? null : $_POST['category_id'];
            
            if (!empty($title)) {
                $stmt = $pdo->prepare("INSERT INTO todos (user_id, category_id, title, description) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$user_id, $category_id, $title, $description])) {
                    $message = "Task added!";
                    $messageType = "success";
                }
            }
        } elseif ($_POST['action'] == 'toggle') {
            $todo_id = $_POST['todo_id'];
            $is_completed = $_POST['is_completed'] ? 0 : 1;
            
            $stmt = $pdo->prepare("UPDATE todos SET is_completed = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$is_completed, $todo_id, $user_id]);
            header("Location: todos.php");
            exit;
        } elseif ($_POST['action'] == 'delete') {
            $todo_id = $_POST['todo_id'];
            $stmt = $pdo->prepare("DELETE FROM todos WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$todo_id, $user_id])) {
                $message = "Task deleted.";
                $messageType = "success";
            }
        }
    }
}

// Fetch categories for dropdown
$stmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ? ORDER BY name ASC");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

// Fetch todos
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$query = "SELECT t.*, c.name as category_name FROM todos t LEFT JOIN categories c ON t.category_id = c.id WHERE t.user_id = ?";
$params = [$user_id];

if ($filter == 'active') {
    $query .= " AND t.is_completed = 0";
} elseif ($filter == 'completed') {
    $query .= " AND t.is_completed = 1";
}
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $query .= " AND t.category_id = ?";
    $params[] = $_GET['category'];
}

$query .= " ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$todos = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto pt-16 md:pt-0 bg-gray-50 h-full">
    <div class="p-8 max-w-5xl mx-auto">
        <header class="mb-8 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">My Tasks</h2>
                <p class="text-gray-500 mt-1">Manage your day effectively.</p>
            </div>
            
            <!-- Filter Nav -->
            <div class="flex gap-2 p-1 bg-gray-200 rounded-lg self-start md:self-auto">
                <a href="todos.php?filter=all<?= isset($_GET['category']) ? '&category='.$_GET['category'] : '' ?>" class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors <?= $filter == 'all' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-600 hover:text-gray-800' ?>">All</a>
                <a href="todos.php?filter=active<?= isset($_GET['category']) ? '&category='.$_GET['category'] : '' ?>" class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors <?= $filter == 'active' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-600 hover:text-gray-800' ?>">Active</a>
                <a href="todos.php?filter=completed<?= isset($_GET['category']) ? '&category='.$_GET['category'] : '' ?>" class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors <?= $filter == 'completed' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-600 hover:text-gray-800' ?>">Completed</a>
            </div>
        </header>

        <?php if($message): ?>
            <div class="mb-6 p-4 rounded-lg <?= $messageType == 'success' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add Todo Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Add New Task</h3>
                    <form method="POST" action="todos.php" class="space-y-4">
                        <input type="hidden" name="action" value="add">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Task Title</label>
                            <input type="text" name="title" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors" placeholder="What needs to be done?" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                            <textarea name="description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors" placeholder="Details..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors bg-white">
                                <option value="">No Category</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-2.5 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm flex justify-center items-center gap-2">
                            <i class="fas fa-plus"></i> Add Task
                        </button>
                    </form>
                </div>
            </div>

            <!-- Todos List -->
            <div class="lg:col-span-2 space-y-4">
                
                <!-- Category Filter -->
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-sm text-gray-500 font-medium">Filter by Category:</span>
                    <form method="GET" action="todos.php" class="inline-block" id="catFilterForm">
                        <?php if(isset($_GET['filter'])): ?>
                            <input type="hidden" name="filter" value="<?= htmlspecialchars($_GET['filter']) ?>">
                        <?php endif; ?>
                        <select name="category" onchange="document.getElementById('catFilterForm').submit()" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 py-1.5 px-3">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <?php if (empty($todos)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
                        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-clipboard-list text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800 mb-1">No tasks found</h3>
                        <p>Enjoy your day or create a new task!</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach($todos as $todo): ?>
                        <div class="bg-white rounded-xl shadow-sm border <?= $todo['is_completed'] ? 'border-gray-200 bg-gray-50/50' : 'border-gray-100 hover:border-indigo-200' ?> p-4 transition-all group">
                            <div class="flex items-start gap-4">
                                <!-- Toggle Complete Form -->
                                <form method="POST" action="todos.php" class="mt-1">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="todo_id" value="<?= $todo['id'] ?>">
                                    <input type="hidden" name="is_completed" value="<?= $todo['is_completed'] ?>">
                                    <button type="submit" class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center transition-colors <?= $todo['is_completed'] ? 'bg-green-500 text-white' : 'border-2 border-gray-300 hover:border-green-500' ?>">
                                        <?php if($todo['is_completed']): ?>
                                            <i class="fas fa-check text-xs"></i>
                                        <?php endif; ?>
                                    </button>
                                </form>
                                
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-base font-semibold <?= $todo['is_completed'] ? 'text-gray-400 line-through' : 'text-gray-800' ?>">
                                        <?= htmlspecialchars($todo['title']) ?>
                                    </h4>
                                    <?php if(!empty($todo['description'])): ?>
                                        <p class="text-sm mt-1 <?= $todo['is_completed'] ? 'text-gray-400 line-through' : 'text-gray-600' ?>">
                                            <?= nl2br(htmlspecialchars($todo['description'])) ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="flex items-center gap-3 mt-3">
                                        <?php if($todo['category_name']): ?>
                                            <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-md font-medium">
                                                <i class="fas fa-tag text-[10px]"></i> <?= htmlspecialchars($todo['category_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="text-xs text-gray-400 flex items-center gap-1">
                                            <i class="far fa-clock"></i> <?= date('M d, Y h:i A', strtotime($todo['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Delete Form -->
                                <form method="POST" action="todos.php" onsubmit="return confirm('Delete this task?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="todo_id" value="<?= $todo['id'] ?>">
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50 opacity-0 group-hover:opacity-100 focus:opacity-100">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
