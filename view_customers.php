<?php

ob_start();
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle Add
if (isset($_GET['action']) && $_GET['action'] == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $photo = $target_dir . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photo);
    }

    $sql = "INSERT INTO customers (name, email, address, mobile, photo, password) VALUES ('$name', '$email', '$address', '$mobile', '$photo', '$password')";
    mysqli_query($conn, $sql);
    header('Location: view_customers.php?search=' . urlencode(isset($_GET['search']) ? $_GET['search'] : '') . '&page=' . (isset($_GET['page']) ? $_GET['page'] : 1));
    exit;
}

// Handele Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM customers WHERE id = $id";
    mysqli_query($conn, $sql);
    header('Location: view_customers.php?search=' . urlencode(isset($_GET['search']) ? $_GET['search'] : '') . '&page=' . (isset($_GET['page']) ? $_GET['page'] : 1));
    exit;
}

// Handle Edit
if (isset($_GET['action']) && $_GET['action'] == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $password = !empty($_POST['password']) ? mysqli_real_escape_string($conn, $_POST['password']) : null;

    $photo_sql = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $photo = $target_dir . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photo);
        $photo_sql = ", photo = '$photo'";
    }

    $password_sql = $password ? ", password = '$password'" : '';

    $sql = "UPDATE customers SET name = '$name', email = '$email', address = '$address', mobile = '$mobile', $photo_sql $password_sql WHERE id = $id";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer Details</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2, h3 { color: #333; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .logout-btn { padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .logout-btn:hover { background: #c82333; }
        .search-bar { margin-bottom: 20px; }
        .search-bar input { padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        .search-bar button, .export-btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px; }
        .search-bar button:hover, .export-btn:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .actions a { margin-right: 10px; color: #007bff; text-decoration: none; }
        .actions a:hover { text-decoration: underline; }
        .add-form, .edit-form { display: none; background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .add-form input, .edit-form input { padding: 8px; margin: 5px 0; width: calc(100% - 16px); border: 1px solid #ccc; border-radius: 4px; }
        .add-form button, .edit-form button { padding: 8px 12px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .add-form button:hover, .edit-form button:hover { background: #218838; }
        .pagination { margin-top: 20px; text-align: center; }
        .pagination a { margin: 0 5px; padding: 8px 12px; text-decoration: none; color: #007bff; border: 1px solid #ddd; border-radius: 4px; }
        .pagination a.active, .pagination a:hover { background: #007bff; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-actions">
            <h2>Customer Details</h2>
            <a href="logout_process.php" class="logout-btn">Logout</a>
        </div>

        <!-- Search Bar -->
        <div class="search-bar">
            <form action="view_customers.php" method="GET">
                <input type="text" name="search" placeholder="Search by name or email" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
                <a href="export.php" class="export-btn">Export to Excel</a>
            </form>
        </div>

        <!-- Add New Record Form -->
        <button onclick="document.getElementById('add-form').style.display='block'">Add New Customer</button>
        <div id="add-form" class="add-form">
            <h3>Add New Customer</h3>
            <form action="view_customers.php?action=add" method="POST" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="address" placeholder="Address" required>
                <input type="tel" name="mobile" placeholder="Mobile" required>
                <input type="file" name="photo" accept="image/*">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Add</button>
            </form>
        </div>

         <!-- Customer Table -->
          <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Mobile</th>
                    <th>Photo</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                
                // Pagination
                $limit = 5; // Records per page
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $offset = ($page - 1) * $limit;
                
                // Search
                $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                $where = $search ? "WHERE name LIKE '%$search%' OR email LIKE '%$search%'" : '';

                //Fetch Customers
                $sql = "SELECT * FROM customers $where LIMIT $limit OFFSET $offset";
                $result = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['name']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>{$row['address']}</td>";
                    echo "<td>{$row['mobile']}</td>";
                    echo "<td>";
                    if ($row['photo']) {
                        echo "<img src = '{$row['photo']}' alt='Photo' width='50'>";
                    }
                    echo "</td>";
                    echo "<td class='actions'>";
                    echo "<a href='#' onclick='showEditForm({$row['id']}, \"{$row['name']}\", \"{$row['email']}\", \"{$row['address']}\", \"{$row['mobile']}\")'>Edit</a>";
                    echo "<a href='view_customers.php?action=delete&id={$row['id']}&search=" . urlencode($search) . "&page=$page' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";

                    // Inline Edit Form
                    echo "<tr class='edit-form' id='edit-form-{$row['id']}'>";
                    echo "<td colspan='7'>";
                    echo "<form action='view_customers.php?action=edit' method='POST' enctype='multipart/form-data'>";
                    echo "<input type='hidden' name='id' value='{$row['id']}'>";
                    echo "<input type='text' name='name' value='{$row['name']}' required>";
                    echo "<input type='email' name='email' value='{$row['email']}' required>";
                    echo "<input type='text' name='address' value='{$row['address']}' required>";
                    echo "<input type='tel' name='mobile' value='{$row['mobile']}' required>";
                    echo "<input type='file' name='photo' accept='image/*'>";
                    echo "<input type='password' name='password' placeholder='New Password (optional)'>";
                    echo "<button type='submit'>Update</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }

                //Pagination
                $sql_count = "SELECT COUNT(*) as total FROM customers $where";
                $count_result = mysqli_query($conn, $sql_count);
                $total_records = mysqli_fetch_assoc($count_result)['total'];
                $total_pages = ceil($total_records / $limit);

                echo "<div class='pagination'>";
                for ($i = 1; $i <= $total_pages; $i++) {
                    $active = $i == $page ? 'active' : '';
                    echo "<a href='view_customers.php?page=$i&search=" . urlencode($search) . "' class='$active'>$i</a>";
                }
                echo "</div>";

                ?>
            </tbody>
          </table>
    </div>

    <script>
        function showEditForm(id, name, email, address, mobile) {
            document.getElementById('edit-form-' + id).style.display = 'table-row';
        }
    </script>
</body>
</html>
<?php
ob_end_flush();
?>
