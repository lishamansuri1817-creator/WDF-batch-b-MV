
<?php
require_once "auth.php";
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentSphere | Courses</title>

    <link rel="stylesheet" href="courses.css">

    <style>
        .session-time {
            background: #f0f4f8;
            color: #263238;
            text-align: center;
            padding: 10px;
            font-size: 14px;
        }

        .account-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 12px 20px;
            background: white;
            border-bottom: 1px solid #ddd;
        }

        .logout {
            background: #11666f;
            color: white;
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
        }

        .admin-tools {
            margin: 25px auto;
            padding: 20px;
            width: 85%;
            max-width: 1100px;
            background: white;
            border: 2px solid #11666f;
            border-radius: 10px;
            text-align: center;
        }

        .admin-tools a {
            display: inline-block;
            margin: 8px;
            padding: 10px 16px;
            background: #11666f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .course-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .course-card {
            width: 280px;
            padding: 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
        }

        .course-card h3 {
            color: #11666f;
        }

        .course-card a {
            display: inline-block;
            margin-top: 10px;
            color: #11666f;
            font-weight: bold;
        }

        .dark-mode .account-bar,
        .dark-mode .admin-tools,
        .dark-mode .course-card {
            background: #1e1e1e;
            color: #eeeeee;
            border-color: #444444;
        }
    </style>
</head>

<body>

<?php showSessionTimestamp(); ?>

<div class="account-bar">
    <span>
        Welcome,
        <?php
        echo htmlspecialchars(
            $_SESSION["name"],
            ENT_QUOTES,
            "UTF-8"
        );
        ?>
        |
        Role:
        <?php
        echo htmlspecialchars(
            $_SESSION["role"],
            ENT_QUOTES,
            "UTF-8"
        );
        ?>
    </span>

    <a href="logout.php" class="logout">Logout</a>
</div>

<a href="dashboard.html" class="dashboard-button">
    ← Back to Dashboard
</a>

<header>
    <h1>StudentSphere</h1>
    <p>Your Complete Student Hub</p>
</header>

<nav>
    <a href="courses.php">Courses</a>
    <a href="colleges.html">Colleges</a>
    <a href="resources.html">Resources</a>
    <a href="scholarships.html">Scholarships</a>
    <a href="events.php">Events</a>
    <a href="community.html">Community</a>
    <a href="profile.html">Profile</a>
    <a href="contact.html">Contact</a>
</nav>

<section class="banner">
    <h2>Explore Courses</h2>
    <p>Learn new skills and discover courses for your future.</p>
</section>

<?php if (($_SESSION["role"] ?? "") === "admin") { ?>
    <section class="admin-tools">
        <h2>Admin Controls</h2>
        <p>
            You are logged in as an administrator.
            You can manage course information from the admin area.
        </p>

        <a href="course_add.php">Add Course</a>
        <a href="course_manage.php">Edit or Delete Courses</a>
    </section>
<?php } ?>

<section>
    <h2 style="text-align:center;">Available Courses</h2>

    <div class="course-container">

        <div class="course-card">
            <h3>Web Development</h3>
            <p>
                Learn HTML, CSS, JavaScript and PHP to create
                interactive websites.
            </p>
            <a href="resources.html">Explore Resources</a>
        </div>

        <div class="course-card">
            <h3>Java Programming</h3>
            <p>
                Understand object-oriented programming,
                classes, objects and exception handling.
            </p>
            <a href="resources.html">Explore Resources</a>
        </div>

        <div class="course-card">
            <h3>Database Management</h3>
            <p>
                Learn about MySQL, database design,
                tables and SQL queries.
            </p>
            <a href="resources.html">Explore Resources</a>
        </div>

        <div class="course-card">
            <h3>Data Structures</h3>
            <p>
                Study arrays, linked lists, stacks, queues,
                trees and graphs.
            </p>
            <a href="resources.html">Explore Resources</a>
        </div>

        <div class="course-card">
            <h3>Computer Networks</h3>
            <p>
                Understand network models, protocols,
                communication and data transmission.
            </p>
            <a href="resources.html">Explore Resources</a>
        </div>

        <div class="course-card">
            <h3>Digital Electronics</h3>
            <p>
                Explore logic gates, Boolean algebra,
                digital circuits and basic electronics.
            </p>
            <a href="resources.html">Explore Resources</a>
        </div>

    </div>
</section>

<footer>
    <p>© 2026 StudentSphere</p>
</footer>

<script src="theme.js"></script>

</body>
</html>
