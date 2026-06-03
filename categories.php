<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = ''; // 'success' or 'error'

// Handle Category Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO categories (user_id, name) VALUES (?, ?)");
            if ($stmt->execute([$user_id, $name])) {
                $message = "Category added successfully.";
                $messageType = "success";
            } else {
                $message = "Failed to add category.";
                $messageType = "error";
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $category_id = $_POST['category_id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$category_id, $user_id])) {
            $message = "Category deleted.";
            $messageType = "success";
        }
    }
}

// Fetch categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto pt-16 md:pt-0 bg-gray-50 h-full">
    <div class="p-8 max-w-4xl mx-auto">
        <header class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Categories</h2>
            <p class="text-gray-500 mt-1">Organize your tasks with categories.</p>
        </header>

        <?php if($message): ?>
            <div class="mb-6 p-4 rounded-lg <?= $messageType == 'success' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Add Category Form -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Add New Category</h3>
                    <form method="POST" action="categories.php">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                            <input type="text" name="name" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors" placeholder="e.g. Work, Personal" required>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-2.5 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm flex justify-center items-center gap-2">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </form>
                </div>
            </div>

            <!-- Categories List -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-800">Your Categories</h3>
                    </div>
                    <?php if (empty($categories)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <p>No categories found.</p>
                        </div>
                    <?php else: ?>
                        <ul class="divide-y divide-gray-100">
                            <?php foreach($categories as $category): ?>
                            <li class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-800 font-medium text-lg"><?= htmlspecialchars($category['name']) ?></p>
                                    </div>
                                </div>
                                <form method="POST" action="categories.php" onsubmit="return confirm('Are you sure you want to delete this category? All tasks associated with it will lose this category tag.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors p-2 rounded-full hover:bg-red-50 opacity-0 group-hover:opacity-100 focus:opacity-100">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
