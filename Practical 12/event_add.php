
<?php
require_once "auth.php";
require_once "db_mysqli.php";

requireAdmin();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $date = $_POST["date"] ?? "";
    $location = trim($_POST["location"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $image = trim($_POST["image"] ?? "");

    if ($title === "" || $date === "" ||
        $location === "" || $category === "") {
        $message = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO events (title, date, location, category, image)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param(
                "sssss",
                $title,
                $date,
                $location,
                $category,
                $image
            );

            if ($stmt->execute()) {
                $message = "Event added successfully!";
            } else {
                $message = "Unable to add event. Check your events table.";
            }

            $stmt->close();
        } else {
            $message = "Unable to prepare the database query.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Event | StudentSphere</title>
    <link rel="stylesheet" href="events.css">
    <style>
        .event-form {
            max-width: 500px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
        }
        .event-form label {
            display: block;
            margin-top: 15px;
        }
        .event-form input {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }
        .event-form button, .event-form a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #11666f;
            color: white;
            border: none;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<?php showSessionTimestamp(); ?>

<div class="event-form">
    <h2>Add New Event</h2>

    <?php if ($message !== "") { ?>
        <p>
            <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
        </p>
    <?php } ?>

    <form method="POST">
        <label for="title">Event Title *</label>
        <input type="text" id="title" name="title" required>

        <label for="date">Event Date *</label>
        <input type="date" id="date" name="date" required>

        <label for="location">Location *</label>
        <input type="text" id="location" name="location" required>

        <label for="category">Category *</label>
        <input type="text" id="category" name="category" required>

        <label for="image">Image filename or URL (optional)</label>
        <input type="text" id="image" name="image">

        <button type="submit">Add Event</button>
        <a href="events.php">Back to Events</a>
    </form>
</div>

</body>
</html>