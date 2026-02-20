<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth_check.php';
$conn = @new mysqli('localhost', 'root', '', 'kapetann');

if ($conn->connect_errno) {
    die("Database connection failed: " . $conn->connect_error);
}

$msg = '';
$uploadDir = '../images/';

// Fetch Product Data
if (!isset($_GET['id'])) {
    header('Location: shop.php');
    exit;
}

$id = (int) $_GET['id'];
$product = null;

$stmt = $conn->prepare("SELECT * FROM coffees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $product = $res->fetch_assoc();
} else {
    die("Product not found.");
}

// Fetch Categories
$categories = [];
$cat_res = $conn->query("SELECT category_id, name FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc())
        $categories[] = $row;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $old_price = $_POST['old_price'] ?? 0;
    $category_id = $_POST['category_id'] ?: NULL;
    $current_image = $_POST['current_image'] ?? '';

    $img_filename = $current_image;

    // Image Upload
    if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imageFile']['tmp_name'];
        $fileName = $_FILES['imageFile']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $dest_path = $uploadDir . $newFileName;
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $img_filename = $newFileName;
                // Optional: Delete old image if it's not a URL
                if ($current_image && strpos($current_image, 'http') === false && file_exists($uploadDir . $current_image)) {
                    @unlink($uploadDir . $current_image);
                }
            } else {
                $msg = "Error moving the uploaded file.";
            }
        } else {
            $msg = "Upload failed. File type is not allowed.";
        }
    }

    if (empty($msg)) {
        $up = $conn->prepare("UPDATE coffees SET name=?, price=?, old_price=?, category_id=?, image=? WHERE id=?");
        $up->bind_param("sddisi", $name, $price, $old_price, $category_id, $img_filename, $id);

        if ($up->execute()) {
            $msg = "Product updated successfully!";
            // Refresh product data
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
        } else {
            $msg = "Error updating database: " . $up->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Product | Meraki Admin</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <?php include 'styles.php'; ?>
    <style>
        .form-card {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .btn-update {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border-radius: 6px;
        }

        .btn-update:hover {
            background: var(--accent);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--text-muted);
            text-decoration: none;
        }

        .back-link:hover {
            color: var(--primary);
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <a href="shop.php" class="back-link">← Back to Menu</a>
            <h1>Edit Product</h1>
        </div>

        <div class="form-card">
            <?php if ($msg): ?>
                <div class="alert alert-success"
                    style="padding:10px; margin-bottom:20px; background:#e8f5e9; color:#2e7d32; border-radius:4px;">
                    <?php echo $msg; ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">

                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($cat['category_id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div class="form-group">
                        <label>Current Price</label>
                        <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Old Price (Optional)</label>
                        <input type="number" name="old_price" step="0.01" value="<?php echo $product['old_price']; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Image (Leave empty to keep current)</label>
                    <?php if ($product['image']): ?>
                        <div style="margin-bottom:10px;">
                            <img src="<?php echo (strpos($product['image'], 'http') === 0) ? $product['image'] : '../images/' . $product['image']; ?>"
                                height="50" style="vertical-align:middle; border-radius:4px;">
                            <span style="font-size:0.85em; color:#777; margin-left:10px;">Current Image</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="imageFile" accept="image/*">
                </div>

                <button class="btn-update">Update Product</button>
            </form>
        </div>
    </main>
</body>

</html>