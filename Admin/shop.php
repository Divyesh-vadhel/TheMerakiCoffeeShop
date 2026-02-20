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

// Get message from session if it exists
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']); // Clear it after retrieving
}

$uploadDir = '../images/';

// 1. Fetch Categories for the Dropdown
$categories = [];
$cat_res = $conn->query("SELECT category_id, name FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc())
        $categories[] = $row;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $act = $_POST['action'];

    if ($act == 'add') {
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $old_price = $_POST['old_price'] ?? 0;
        $category_id = $_POST['category_id'] ?: NULL; // Get the selected category ID
        $img_filename = '';

        // --- FILE UPLOAD LOGIC ---
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
                } else {
                    $msg = "Error moving the uploaded file.";
                }
            } else {
                $msg = "Upload failed. File type is not allowed.";
            }
        }
        // --- END FILE UPLOAD LOGIC ---

        if (empty($msg) || strpos($msg, 'Error') === false) {
            // Updated INSERT query with category_id
            $stmt = $conn->prepare("INSERT INTO coffees (name, price, old_price, category_id, image) VALUES (?, ?, ?, ?, ?)");
            // 'sddis' means string, decimal, decimal, integer (for category_id), string
            $stmt->bind_param("sddis", $name, $price, $old_price, $category_id, $img_filename);

            if ($stmt->execute()) {
                $_SESSION['msg'] = "Item Added successfully.";
            } else {
                $_SESSION['msg'] = "Error adding item to database: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['msg'] = $msg;
        }

        header("Location: shop.php");
        exit();

    } elseif ($act == 'delete') {
        $id = $_POST['id'] ?? 0;

        // Delete the image file from the server
        $img_res = $conn->query("SELECT image FROM coffees WHERE id=$id");
        if ($img_row = $img_res->fetch_assoc()) {
            $image_path = $uploadDir . $img_row['image'];
            if (file_exists($image_path) && !is_dir($image_path)) {
                @unlink($image_path);
            }
        }

        $stmt = $conn->prepare("DELETE FROM coffees WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Item Deleted.";
        } else {
            $_SESSION['msg'] = "Error deleting item: " . $stmt->error;
        }
        $stmt->close();

        header("Location: shop.php");
        exit();
    }
}

// 2. Updated SELECT query to retrieve category_id and join with category name
$items = [];
$sql = "SELECT c.id, c.name, c.price, c.old_price, c.image, cat.name AS category_name 
        FROM coffees c 
        LEFT JOIN categories cat ON c.category_id = cat.category_id
        ORDER BY c.id DESC";

$res = $conn->query($sql);

if ($res) {
    while ($row = $res->fetch_assoc())
        $items[] = $row;
    $res->free();
} else {
    $msg .= " | Error fetching data: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Menu | Tavern Admin</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <?php include 'styles.php'; ?>
    <style>
        /* NEW FORM STYLING */
        .add-item-form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px 30px;
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            flex-direction: column;
        }

        .add-item-form-grid label {
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        .add-item-form-grid input:not([type="file"]),
        .add-item-form-grid select {
            /* Apply styles to select box too */
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #fcfcfc;
        }

        .add-item-form-grid input:focus,
        .add-item-form-grid select:focus {
            border-color: #D4A373;
            box-shadow: 0 0 0 2px rgba(212, 163, 115, 0.2);
            outline: none;
        }

        .add-item-form-grid button {
            grid-column: 2 / 3;
            justify-self: end;
            align-self: end;
            margin-top: 20px;
            width: 150px;
        }

        .add-item-form-grid .full-width {
            grid-column: 1 / -1;
        }

        /* Item Display Styles */
        .price-display {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .old-price-admin {
            color: #888;
            text-decoration: line-through;
            font-size: 0.9em;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .add-item-form-grid {
                grid-template-columns: 1fr;
            }

            .add-item-form-grid button {
                grid-column: 1 / 2;
                width: 100%;
                justify-self: stretch;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <h1>Manage Menu</h1>
        </div>
        <?php if ($msg)
            echo "<div class='alert alert-success'>$msg</div>"; ?>

        <div class="card">
            <h3 style="margin-bottom:20px;">Add New Item</h3>

            <form method="post" enctype="multipart/form-data" class="add-item-form-grid">
                <input type="hidden" name="action" value="add">

                <div class="form-row"><label>Name</label><input type="text" name="name" required></div>

                <div class="form-row"><label>Image (Select File)</label><input type="file" name="imageFile"
                        accept="image/*" required></div>

                <div class="form-row"><label>Current Price</label><input type="number" name="price" step="0.01"
                        required></div>

                <div class="form-row"><label>Discount Price (Old)</label><input type="number" name="old_price"
                        step="0.01"></div>

                <div class="form-row">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">-- Select Category (Optional) --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <p class="full-width" style="font-size:0.9em; color:#555;">*If Discount Price (Old) is higher than
                    Current Price, a discount will be shown on the menu.</p>

                <button class="btn-primary">Add Item</button>
            </form>
        </div>

        <div class="card-grid">
            <?php foreach ($items as $i):
                $img = (strpos($i['image'], 'http') === 0) ? $i['image'] : "../images/" . $i['image'];
                $hasDiscount = isset($i['old_price']) && $i['old_price'] > $i['price'] && $i['old_price'] > 0;
                ?>
                <div class="card" style="padding:15px;">
                    <img src="<?php echo $img; ?>" style="width:100%; height:150px; object-fit:cover; border-radius:8px;">
                    <h4 style="margin-top:10px;"><?php echo htmlspecialchars($i['name']); ?></h4>
                    <p style="font-size:0.85em; color:var(--text-muted); margin-bottom:5px;">
                        Category: <strong><?php echo htmlspecialchars($i['category_name'] ?? 'Uncategorized'); ?></strong>
                    </p>

                    <div class="price-display">
                        <?php if ($hasDiscount): ?>
                            <p class="old-price-admin">₹<?php echo number_format($i['old_price'], 2); ?></p>
                        <?php endif; ?>

                        <p style="color:var(--accent); font-weight:bold;">₹<?php echo number_format($i['price'], 2); ?></p>
                    </div>

                    <div style="display:flex; gap:10px; margin-top:10px;">
                        <a href="edit_product.php?id=<?php echo $i['id']; ?>" class="btn-small"
                            style="text-decoration:none; text-align:center; padding:8px 0; background:var(--accent); color:white; border-radius:4px; flex:1;">Update</a>

                        <form method="post" onsubmit="return confirm('Delete?');" style="flex:1;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                            <button class="btn-small btn-delete"
                                style="width:100%; height:100%; border:none; background:#ff5252; color:white; border-radius:4px; cursor:pointer;">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>

</html>