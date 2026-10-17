
<?php
require_once "auth.php";
requireAdmin();
require_once "db_mysqli.php";

$message = "";

// Delete event
if (isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];

    $stmt = $conn->prepare(
        "DELETE FROM events WHERE event_id = ?"
    );
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Event deleted successfully!";
    } else {
        $message = "Unable to delete event.";
    }

    $stmt->close();
}

// Update event
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_event"])) {

    $id = (int) $_POST["event_id"];
    $title = trim($_POST["title"]);
    $date = $_POST["date"];
    $location = trim($_POST["location"]);
    $category = trim($_POST["category"]);
    $image = trim($_POST["image"]);

    $stmt = $conn->prepare(
        "UPDATE events
         SET title = ?, `date` = ?, location = ?,
             category = ?, image = ?
         WHERE event_id = ?"
    );

    $stmt->bind_param(
        "sssssi",
        $title,
        $date,
        $location,
        $category,
        $image,
        $id
    );

    if ($stmt->execute()) {
        $message = "Event updated successfully!";
    } else {
        $message = "Unable to update event.";
    }

    $stmt->close();
}

// Get event selected for editing
$editEvent = null;

if (isset($_GET["edit"])) {
    $id = (int) $_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT * FROM events WHERE event_id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $editEvent = $result->fetch_assoc();

    $stmt->close();
}

// Get all events
$result = $conn->query(
    "SELECT * FROM events ORDER BY event_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Events | StudentSphere</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f4f6f9;
            color: #222;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1, h2 {
            color: #243b55;
        }

        .message {
            background: #e2f5e8;
            padding: 12px;
            margin: 15px 0;
        }

        .form-box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        input {
            display: block;
            width: 95%;
            padding: 10px;
            margin: 8px 0 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #243b55;
            color: white;
        }

        .btn, button {
            display: inline-block;
            padding: 9px 13px;
            border: none;
            text-decoration: none;
            color: white;
            background: #243b55;
            cursor: pointer;
            border-radius: 4px;
            margin: 2px;
        }

        .delete {
            background: #c0392b;
        }

        .back {
            margin-bottom: 15px;
        }

        @media (max-width: 650px) {
            body {
                margin: 10px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 6px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <a class="btn back" href="events.php">Back to Events</a>

    <h1>Manage Events</h1>

    <?php if ($message !== ""): ?>
        <p class="message">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <?php if ($editEvent): ?>
        <div class="form-box">
            <h2>Edit Event</h2>

            <form method="POST"
                  action="event_manage.php?edit=<?= (int)$editEvent['event_id'] ?>">

                <input
                    type="hidden"
                    name="event_id"
                    value="<?= (int)$editEvent['event_id'] ?>"
                >

                <label>Event Title</label>
                <input
                    type="text"
                    name="title"
                    required
                    value="<?= htmlspecialchars($editEvent['title'] ?? '') ?>"
                >

                <label>Date</label>
                <input
                    type="date"
                    name="date"
                    required
                    value="<?= htmlspecialchars($editEvent['date'] ?? '') ?>"
                >

                <label>Location</label>
                <input
                    type="text"
                    name="location"
                    required
                    value="<?= htmlspecialchars($editEvent['location'] ?? '') ?>"
                >

                <label>Category</label>
                <input
                    type="text"
                    name="category"
                    required
                    value="<?= htmlspecialchars($editEvent['category'] ?? '') ?>"
                >

                <label>Image Path</label>
                <input
                    type="text"
                    name="image"
                    value="<?= htmlspecialchars($editEvent['image'] ?? '') ?>"
                    placeholder="images/event.jpg"
                >

                <button type="submit" name="update_event">
                    Save Changes
                </button>

                <a class="btn" href="event_manage.php">Cancel</a>
            </form>
        </div>
    <?php endif; ?>

    <h2>Existing Events</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Date</th>
            <th>Location</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($event = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?= (int)$event['event_id'] ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($event['title'] ?? '') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($event['date'] ?? '') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($event['location'] ?? '') ?>
                    </td>

                    <td>
                        <a
                            class="btn"
                            href="event_manage.php?edit=<?= (int)$event['event_id'] ?>"
                        >
                            Edit
                        </a>

                        <a
                            class="btn delete"
                            href="event_manage.php?delete=<?= (int)$event['event_id'] ?>"
                            onclick="return confirm('Are you sure you want to delete this event?');"
                        >
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="5">No events found.</td>
            </tr>

        <?php endif; ?>
    </table>

</div>
</body>
</html>