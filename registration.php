<?php
session_start();
if (isset($_SESSION["user"])) {
   header("Location: index.php");
   exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php
        if (isset($_POST["submit"])) {
           $fullName = htmlspecialchars($_POST["fullname"]);
           $email = htmlspecialchars($_POST["email"]);
           $password = $_POST["password"];
           $passwordRepeat = $_POST["repeat_password"];
           
           $passwordHash = password_hash($password, PASSWORD_DEFAULT);

           $errors = array();
           
           if (empty($fullName) OR empty($email) OR empty($password) OR empty($passwordRepeat)) {
               array_push($errors,"All fields are required");
           }
           if (!preg_match("/^[a-zA-Z\s]{2,16}$/", $fullName)) {
               array_push($errors, "Full Name must be between 2 to 16 characters and should not contain numbers or special characters");
           }
           if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
               array_push($errors, "Email is not valid");
           }
           if (strlen($password) < 2 || strlen($password) > 8 || 
               !preg_match("/[A-Z]/", $password) || 
               !preg_match("/[0-9]/", $password) || 
               !preg_match("/[\W]/", $password)) {
               array_push($errors, "Password must be between 2 to 8 characters long, include at least one uppercase letter, one number, and one special character");
           }
           if ($password !== $passwordRepeat) {
               array_push($errors,"Password does not match");
           }
           require_once "database.php";
           $sql = "SELECT * FROM users WHERE email = ?";
           $stmt = mysqli_prepare($conn, $sql);
           mysqli_stmt_bind_param($stmt, "s", $email);
           mysqli_stmt_execute($stmt);
           mysqli_stmt_store_result($stmt);
           $rowCount = mysqli_stmt_num_rows($stmt);
           if ($rowCount > 0) {
               array_push($errors,"Email already exists!");
           }
           $sql = "SELECT * FROM users WHERE full_name = ?";
           $stmt = mysqli_prepare($conn, $sql);
           mysqli_stmt_bind_param($stmt, "s", $fullName);
           mysqli_stmt_execute($stmt);
           mysqli_stmt_store_result($stmt);
           $rowCount = mysqli_stmt_num_rows($stmt);
           if ($rowCount > 0) {
               array_push($errors,"Full Name already exists!");
           }
           if (count($errors) > 0) {
               foreach ($errors as $error) {
                   echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>";
               }
           } else {
               $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
               $stmt = mysqli_prepare($conn, $sql);
               mysqli_stmt_bind_param($stmt, "sss", $fullName, $email, $passwordHash);
               $executeResult = mysqli_stmt_execute($stmt);
               if ($executeResult) {
                   echo "<div class='alert alert-success'>You are registered successfully.</div>";
               } else {
                   die("Something went wrong");
               }
           }
        }
        ?>
        <form action="registration.php" method="post">
            <div class="form-group">
                <input type="text" class="form-control" name="fullname" placeholder="Full Name:" required>
            </div>
            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="Email:" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password:" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="repeat_password" placeholder="Repeat Password:" required>
            </div>
            <div class="form-btn">
                <input type="submit" class="btn btn-primary" value="Register" name="submit">
            </div>
        </form>
        <div>
        <div><p>Already Registered <a href="login.php">Login Here</a></p></div>
      </div>
    </div>
</body>
</html>
