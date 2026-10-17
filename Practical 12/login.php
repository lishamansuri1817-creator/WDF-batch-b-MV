
<?php
require_once "auth.php";
require_once "db_mysqli.php";

$error = "";
$notice = "";

if (isset($_GET["timeout"])) {
    $notice = "Your session expired due to inactivity. Please log in again.";
} elseif (isset($_GET["loggedout"])) {
    $notice = "You have logged out successfully.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $selectedRole = $_POST["role"] ?? "";

    if (
        $email === "" ||
        $password === "" ||
        !in_array($selectedRole, ["student", "admin"], true)
    ) {
        $error = "Please fill in all fields and select a role.";
    } else {
        $stmt = $conn->prepare(
            "SELECT student_id, name, email, password, role
             FROM students
             WHERE email = ?"
        );

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (
                $user &&
                password_verify($password, $user["password"]) &&
                strtolower($user["role"]) === $selectedRole
            ) {
                session_regenerate_id(true);

                $_SESSION["student_id"] = $user["student_id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = strtolower($user["role"]);
                $_SESSION["login_time"] = time();
                $_SESSION["last_activity"] = time();

                if ($_SESSION["role"] === "admin") {
                    header("Location: events.php");
                } else {
                    header("Location: courses.php");
                }
                exit;
            } else {
                $error = "Invalid email, password, or selected role.";
            }
        } else {
            $error = "Unable to process login. Check your database connection.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentSphere | Login</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            color: #263238;
        }

        .login-box {
            width: 90%;
            max-width: 420px;
            margin: 65px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }

        h1 {
            color: #11666f;
            text-align: center;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #bbb;
            border-radius: 5px;
            font-size: 15px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 12px;
            background: #11666f;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #0d4f56;
        }

        .error {
            color: #a61b1b;
            background: #fff0f0;
            padding: 10px;
            border-radius: 5px;
        }

        .notice {
            color: #14532d;
            background: #e9f8ed;
            padding: 10px;
            border-radius: 5px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #11666f;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h1>StudentSphere</h1>
        <p class="subtitle">Login to your account</p>

        <?php if ($notice !== "") { ?>
            <p class="notice">
                <?php echo htmlspecialchars($notice, ENT_QUOTES, "UTF-8"); ?>
            </p>
        <?php } ?>

        <?php if ($error !== "") { ?>
            <p class="error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
            </p>
        <?php } ?>

        <form action="login.php" method="POST">
            <label for="role">Login As</label>
            <select name="role" id="role" required>
                <option value="">Select your role</option>
                <option value="student"
                    <?php echo (($_POST["role"] ?? "") === "student") ? "selected" : ""; ?>>
                    Student
                </option>
                <option value="admin"
                    <?php echo (($_POST["role"] ?? "") === "admin") ? "selected" : ""; ?>>
                    Admin
                </option>
            </select>

            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?php echo htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit">Login</button>
        </form>

        <p class="register-link">
            Don't have an account?
            <a href="register.html">Register here</a>
        </p>
    </div>
</body>
</html>
