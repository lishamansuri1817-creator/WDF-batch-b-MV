
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// End the session after 15 minutes of inactivity.
$timeoutDuration = 15 * 60;

if (isset($_SESSION["student_id"])) {
    if (
        isset($_SESSION["last_activity"]) &&
        (time() - $_SESSION["last_activity"]) > $timeoutDuration
    ) {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $cookie = session_get_cookie_params();

            setcookie(
                session_name(),
                "",
                time() - 42000,
                $cookie["path"],
                $cookie["domain"],
                $cookie["secure"],
                $cookie["httponly"]
            );
        }

        session_destroy();

        header("Location: login.php?timeout=1");
        exit;
    }

    $_SESSION["last_activity"] = time();
}

function requireLogin()
{
    if (!isset($_SESSION["student_id"])) {
        header("Location: login.php");
        exit;
    }
}

function requireAdmin()
{
    requireLogin();

    if (($_SESSION["role"] ?? "") !== "admin") {
        http_response_code(403);
        exit("Access denied. Admin permission is required.");
    }
}

function showSessionTimestamp()
{
    if (isset($_SESSION["login_time"])) {
        $timestamp = date(
            "d M Y, h:i:s A",
            $_SESSION["login_time"]
        );

        echo '
        <div style="
            display:block;
            width:100%;
            box-sizing:border-box;
            padding:10px;
            text-align:center;
            background:#f0f4f8;
            color:#263238;
            border-bottom:1px solid #d5dce3;
            font-family:Arial,sans-serif;
            font-size:14px;
        ">
            Session started: '
            . htmlspecialchars($timestamp, ENT_QUOTES, "UTF-8") .
        '</div>';
    }
}
?>