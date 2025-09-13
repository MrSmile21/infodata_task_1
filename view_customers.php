<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer Details</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        .actions a { margin-right: 10px; color: #007bff; text-decoration: none; }
        .actions a:hover { text-decoration: underline; }
        form { margin-bottom: 20px; }
        input { padding: 8px; margin: 5px 0; }
        button { padding: 8px 12px; background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        .edit-form { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Customer Details</h2>

        <!-- Add new record -->
         <h3>Add New Customer</h3>
         <form action="view_customers.php?action=add" method="post" encytype="multipart/form-data">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="tel" name="mobile" placeholder="Mobile" required>
            <input type="file" name="photo" accept="image/*">
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Add</button>

         </form>

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
                session_start();
                include 'connect.php';

                //Handle Add
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

                    $sql = "INSERT INTO customers (name, email, address, mobile, photo, password) VALUES('$name', '$email', '$address', '$mobile', '$photo', '$password')";
                    mysqli_query($conn, $sql);
                    header('Location: view_customer.php');
                    exit;
                } 

                //Handle delete
                if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
                    $id = intval($_GET['id']);
                    $sql = "DELETE FROM customers WHERE id = $id";
                    mysqli_query($conn, $sql);
                    header('Location: view_customers.php');
                    exit;
                }

                //Handle Edit (Form Submission)
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
                    $sql = "UPDATE customers SET name = '$name', email = '$email', address = '$address', mobile = '$mobile' $photo_sql $password_sql WHERE id = $id ";
                    mysqli_query($conn, $sql);
                    header('Location: view_customer.php');
                    exit;
                }

                //Fetch Customers
                $sql = "SELECT * FROM customers";
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
                        echo "<img src = '{$row['photo']} alt='Photo' width='50'>";
                    }
                    echo "</td>";
                    echo "<td class='actions'>";
                    echo "<a href='#' onclick='showEditForm({$row['id']}, \"{$row['name']}\", \"{$row['email']}\", \"{$row['address']}\", \"{$row['mobile']}\")'>Edit</a>";
                    echo "<a href='view_customers.php?action=delete&id={$row['id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
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