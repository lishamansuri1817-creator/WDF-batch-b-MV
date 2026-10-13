
<?php
require_once "auth.php";
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentSphere | Events</title>
    <link rel="stylesheet" href="events.css">
</head>

<body>

<?php showSessionTimestamp(); ?>

<div class="account-bar">
    <span>
        Welcome,
        <?php
        echo htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8");
        ?>
        |
        Role:
        <?php
        echo htmlspecialchars($_SESSION["role"], ENT_QUOTES, "UTF-8");
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
    <h2>Upcoming Events</h2>
    <p>Participate in workshops, hackathons and seminars.</p>
</section>

<?php if (($_SESSION["role"] ?? "") === "admin") { ?>

<section class="admin-tools">
    <h2>Admin Controls</h2>

    <p>
        You are logged in as an administrator.
        You can manage the events displayed on this website.
    </p>

    <a href="event_add.php">Add New Event</a>
    <a href="event_manage.php">Edit or Delete Events</a>
</section>

<?php } ?>

<section>
    <h2>Featured Events</h2>

    <div class="event-controls">

        <input
            type="text"
            id="searchInput"
            placeholder="Search events..."
        >

        <select id="locationFilter">
            <option value="all">All Locations</option>
        </select>

        <select id="sortSelect">
            <option value="default">Sort By</option>
            <option value="nameAsc">Name: A to Z</option>
            <option value="nameDesc">Name: Z to A</option>
            <option value="dateAsc">Date: Earliest First</option>
            <option value="dateDesc">Date: Latest First</option>
        </select>

    </div>

    <div id="loadingMessage">
        Loading events...
    </div>

    <div id="eventContainer" class="event-container"></div>

    <p id="noResults" class="no-results">
        No events found.
    </p>

    <div class="pagination">

        <button id="previousButton">
            Previous
        </button>

        <span id="pageInfo">Page 1</span>

        <button id="nextButton">
            Next
        </button>

    </div>
</section>

<section>
    <h2>Event Schedule</h2>

    <table>
        <tr>
            <th>Event</th>
            <th>Date</th>
            <th>Venue</th>
        </tr>

        <tr>
            <td>Hackathon</td>
            <td>15 Sept</td>
            <td>Ahmedabad</td>
        </tr>

        <tr>
            <td>AI Workshop</td>
            <td>25 Sept</td>
            <td>Online</td>
        </tr>

        <tr>
            <td>Career Fair</td>
            <td>10 Oct</td>
            <td>Vadodara</td>
        </tr>

        <tr>
            <td>Coding Contest</td>
            <td>20 Oct</td>
            <td>Surat</td>
        </tr>
    </table>
</section>

<footer>
    <p>© 2026 StudentSphere</p>
</footer>

<script src="theme.js"></script>
<script src="events.js"></script>

</body>
</html>
