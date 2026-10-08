<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

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
    $name = htmlspecialchars($name);
    $email = htmlspecialchars($email);
    if (count($errors) > 0) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Error</title>
<link rel="stylesheet" href="register.css">
</head>
<body>
<header>
<h1>StudentSphere</h1>
<p>Your Complete Student Hub</p>
</header>
<section class="banner">
<h2>Registration Failed</h2>
<p>Please correct the following errors.</p>
</section>
<section>
<div class="register-box">
<h2>Errors</h2>
<ul>
<?php
foreach ($errors as $error) {
echo "<li>" . $error . "</li>";
}
?>
</ul>
<br>
<a href="register.html">Go Back to Registration</a>
</div>
</section>

<footer>
<p>© 2026 StudentSphere</p>
</footer>
</body>
</html>
<?php
    } else {
        $file = "registrations.csv";
        $newFile = !file_exists($file);
        $handle = fopen($file, "a");
        if ($handle !== false) {
            if ($newFile) {
                fputcsv($handle, array("Name", "Email", "Password"));
            }
            fputcsv($handle, array($name, $email, $password));
            fclose($handle);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Successful</title>
<link rel="stylesheet" href="register.css">
</head>
<body>
<header>
<h1>StudentSphere</h1>
<p>Your Complete Student Hub</p>
</header>
<section class="banner">
<h2>Registration Successful!</h2>
<p>Your account has been created successfully.</p>
</section>
<section>
<div class="register-box">
<h2>Welcome to StudentSphere!</h2>
<p>Your registration has been saved successfully.</p>
<br>
<a href="login.html">Go to Login</a>
</div>
</section>
<footer>
<p>© 2026 StudentSphere</p>
</footer>
</body>
</html>
<?php
        } else {
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Error</title>
<link rel="stylesheet" href="register.css">
</head>
<body>
<header>
<h1>StudentSphere</h1>
<p>Your Complete Student Hub</p>
</header>
<section class="banner">
<h2>Error</h2>
<p>Unable to save your registration.</p>
</section>
<section>
<div class="register-box">
<h2>Something went wrong</h2>
<p>Your registration could not be saved.</p>
<br>
<a href="register.html">Go Back</a>
</div>
</section>
<footer>
<p>© 2026 StudentSphere</p>
</footer>
</body>
</html>
<?php
        }
    }
} else {
header("Location: register.html");
exit();
}
?>