<?php

session_start();
include 'connect.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    //Assume customer table
    $sql = "SELECT * FROM customers WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        // verify password
        if ($row['password'] = $password) {
            $_SESSION['user_id'] = $row['id'];
            header('Location: view_customers.php');
            exit;
        } else {
            echo "Invalid Password.";
        }
    } else {
        echo "No user found.";
    }
}

?>