
<?php

$errors = [];
$registrationSuccessful = false;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit();
}

require "db_mysqli.php";

$name = trim($_POST["name"] ?? "");
$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";

// Backend validation

if ($name === "" || strlen($name) > 100) {
    $errors[] = "Please enter a valid name (maximum 100 characters).";
}

if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
    $errors[] = "Username must be 3-50 characters and contain only letters, numbers or underscores.";
}

if ($email === "" || strlen($email) > 254 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if (strlen($password) < 7) {
    $errors[] = "Password must be at least 7 characters long.";
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors[] = "Password must contain at least one number.";
} elseif (!preg_match('/[@#]/', $password)) {
    $errors[] = "Password must contain @ or #.";
}

if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

// Continue only when validation succeeds

if (empty($errors)) {

    try {

        // Check duplicate email

        $checkEmail = $conn->prepare(
            "SELECT student_id FROM students WHERE email = ?"
        );

        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        $emailExists = ($checkEmail->num_rows > 0);

        $checkEmail->close();

        if ($emailExists) {
            $errors[] = "This email is already registered.";
        }

        // Check duplicate username

        if (empty($errors)) {

            $checkUsername = $conn->prepare(
                "SELECT student_id FROM students WHERE username = ?"
            );

            $checkUsername->bind_param("s", $username);
            $checkUsername->execute();
            $checkUsername->store_result();

            $usernameExists = ($checkUsername->num_rows > 0);

            $checkUsername->close();

            if ($usernameExists) {
                $errors[] = "This username is already taken.";
            }
        }

        // Hash password and insert new student

        if (empty($errors)) {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $sql = "INSERT INTO students
                    (name, username, email, password)
                    VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ssss",
                $name,
                $username,
                $email,
                $hashedPassword
            );

            $stmt->execute();
            $stmt->close();

            $registrationSuccessful = true;
        }

    } catch (mysqli_sql_exception $e) {

        // Handle a duplicate-key error or database failure safely

        if ($e->getCode() === 1062) {
            $errors[] = "This email or username is already registered. Please try another.";
        } else {
            error_log("Registration database error: " . $e->getMessage());
            $errors[] = "Unable to save registration. Please try again.";
        }
    }
}

$conn->close();

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

<?php if ($registrationSuccessful) { ?>

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
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
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
