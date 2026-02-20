<?php
// --- ERROR REPORTING ---
error_reporting(E_ALL);
ini_set('display_errors', 1); 
// --- END ERROR REPORTING ---

require_once 'auth_check.php';
$conn = @new mysqli('localhost', 'root', '', 'kapetann');
$msg = '';

if ($conn->connect_errno) {
    die("Database connection failed: " . $conn->connect_error);
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    $act = $_POST['action'];
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $id = $_POST['id'] ?? 0;

    if($act == 'add'){ 
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        
        if($stmt->execute()) {
            $msg = "Category Added successfully.";
        } else {
            $msg = "Error adding category: " . $stmt->error;
        }
        $stmt->close();
    }
    elseif($act == 'delete'){ 
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id=?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()) {
            $msg = "Category Deleted.";
            // IMPORTANT: Consider also updating 'coffees' table to set category_id to NULL 
            // for items belonging to this deleted category.
        } else {
            $msg = "Error deleting category: " . $stmt->error;
        }
        $stmt->close();
    }
}

$categories = []; 
$res = $conn->query("SELECT category_id, name, description FROM categories ORDER BY category_id DESC");

if ($res) {
    while($row = $res->fetch_assoc()) $categories[] = $row;
    $res->free();
} else {
    $msg .= " | Error fetching data: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Manage Categories | Tavern Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php include 'styles.php'; ?>
    <style>
        .categories-form-grid {
            display: grid;
            grid-template-columns: 1fr 2fr auto;
            gap: 20px 30px;
            align-items: end;
        }
        .form-row { display: flex; flex-direction: column; }
        .categories-form-grid label { font-weight: 500; color: #333; margin-bottom: 5px; font-size: 0.9em; }
        .categories-form-grid input, .categories-form-grid textarea {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s;
            background-color: #fcfcfc;
            width: 100%;
        }
        .categories-form-grid input:focus, .categories-form-grid textarea:focus {
            border-color: #D4A373;
            box-shadow: 0 0 0 2px rgba(212, 163, 115, 0.2);
            outline: none;
        }
        .categories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
        .category-card p { margin-bottom: 10px; font-size: 0.9em; color: #555; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><h1>Manage Categories</h1></div>
        <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

        <div class="card">
            <h3 style="margin-bottom:20px;">Add New Category</h3>
            
            <form method="post" class="categories-form-grid">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row"><label>Category Name</label><input type="text" name="name" required></div>
                
                <div class="form-row"><label>Description</label><textarea name="description" rows="1" style="resize:vertical;"></textarea></div>
                
                <div style="align-self: end;"><button class="btn-primary" style="min-width: 120px;">Add</button></div>
            </form>
        </div>

        <div class="categories-grid">
            <?php foreach($categories as $cat): ?>
            <div class="card category-card" style="padding:15px;">
                <h4 style="margin-top:10px;"><?php echo htmlspecialchars($cat['name']); ?></h4>
                <p><?php echo htmlspecialchars($cat['description'] ?: 'No description provided.'); ?></p>
                <form method="post" onsubmit="return confirm('WARNING: Deleting this category might unlink menu items. Proceed?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $cat['category_id']; ?>">
                    <button class="btn-small btn-delete" style="width:100%;">Delete</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>