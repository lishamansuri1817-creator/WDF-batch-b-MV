
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require "db.php";

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";

    $errors = array();

    if ($name == "") {
        $errors[] = "Name is required.";
    }

    if ($email == "") {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($password == "") {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 7) {
        $errors[] = "Password must be at least 7 characters long.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number.";
    } elseif (!preg_match("/[@#]/", $password)) {
        $errors[] = "Password must contain @ or #.";
    }

    if ($confirmPassword == "") {
        $errors[] = "Please confirm your password.";
    } elseif ($password != $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (count($errors) == 0) {

        try {
            // Check whether this email already exists
            $checkSql = "SELECT student_id FROM students WHERE email = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$email]);

            if ($checkStmt->fetch()) {
                $errors[] = "This email is already registered.";
            } else {
                // Hash the password before saving it
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert the student into the database
                $sql = "INSERT INTO students (name, email, password)
                        VALUES (?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->execute([$name, $email, $hashedPassword]);

                $registrationSuccessful = true;
            }

        } catch (PDOException $e) {
            $errors[] = "Unable to save registration. Please try again.";
        }
    }

} else {
    header("Location: register.html");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentSphere | Registration</title>
    <link rel="stylesheet" href="register.css">
</head>

<body>

<header>
    <h1>StudentSphere</h1>
    <p>Your Complete Student Hub</p>
</header>

<?php if (!empty($registrationSuccessful)) { ?>

    <section class="banner">
        <h2>Registration Successful!</h2>
        <p>Your account has been created successfully.</p>
    </section>

    <section>
        <div class="register-box">
            <h2>Welcome to StudentSphere!</h2>
            <p>Your registration has been saved in the database.</p>
            <br>
            <a href="login.html">Go to Login</a>
        </div>
    </section>

<?php } else { ?>

    <section class="banner">
        <h2>Registration Failed</h2>
        <p>Please correct the following errors.</p>
    </section>

    <section>
        <div class="register-box">
            <h2>Errors</h2>

            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php } ?>
            </ul>

            <br>
            <a href="register.html">Go Back to Registration</a>
        </div>
    </section>

<?php } ?>

<footer>
    <p>© 2026 StudentSphere</p>
</footer>

</body>
</html>