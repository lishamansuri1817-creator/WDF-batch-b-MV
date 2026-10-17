
<?php
require_once "auth.php";
requireAdmin();
require_once "db_mysqli.php";

$message = "";

function uploadPoster()
{
    $maxSize = 2 * 1024 * 1024; // 2 MB

    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"
    ];

    if (!isset($_FILES["poster"]) ||
        $_FILES["poster"]["error"] === UPLOAD_ERR_NO_FILE) {
        return ["path" => "", "error" => ""];
    }

    $file = $_FILES["poster"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return ["path" => "", "error" => "Poster upload failed."];
    }

    if ($file["size"] > $maxSize) {
        return ["path" => "", "error" => "Poster must be 2 MB or smaller."];
    }

    if (!is_uploaded_file($file["tmp_name"])) {
        return ["path" => "", "error" => "Invalid uploaded file."];
    }

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileInfo->file($file["tmp_name"]);

    if (!isset($allowedTypes[$mimeType])) {
        return [
            "path" => "",
            "error" => "Only JPG, PNG, and WebP image files are allowed."
        ];
    }

    if (@getimagesize($file["tmp_name"]) === false) {
        return ["path" => "", "error" => "The uploaded file is not a valid image."];
    }

    $uploadFolder = __DIR__ . "/uploads/events/";

    if (!is_dir($uploadFolder) &&
        !mkdir($uploadFolder, 0755, true)) {
        return ["path" => "", "error" => "Could not create the upload folder."];
    }

    if (!is_writable($uploadFolder)) {
        return ["path" => "", "error" => "The upload folder is not writable."];
    }

    $newName = bin2hex(random_bytes(16)) . "." . $allowedTypes[$mimeType];
    $destination = $uploadFolder . $newName;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        return ["path" => "", "error" => "Could not save the poster."];
    }

    return [
        "path" => "uploads/events/" . $newName,
        "error" => ""
    ];
}

// Delete event.
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_event"])) {

    $id = (int)($_POST["event_id"] ?? 0);

    $stmt = $conn->prepare(
        "SELECT image FROM events WHERE event_id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $oldEvent = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($oldEvent) {
        $stmt = $conn->prepare(
            "DELETE FROM events WHERE event_id = ?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $message = "Event deleted successfully!";

            $oldImage = $oldEvent["image"] ?? "";

            // Delete only files stored in our event-upload folder.
            if (strpos($oldImage, "uploads/events/") === 0) {
                $filePath = __DIR__ . "/" . $oldImage;

                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
        } else {
            $message = "Unable to delete the event.";
        }

        $stmt->close();
    } else {
        $message = "Event not found.";
    }
}

// Update event.
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_event"])) {

    $id = (int)($_POST["event_id"] ?? 0);
    $title = trim($_POST["title"] ?? "");
    $date = $_POST["date"] ?? "";
    $location = trim($_POST["location"] ?? "");
    $category = trim($_POST["category"] ?? "");

    $stmt = $conn->prepare(
        "SELECT image FROM events WHERE event_id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $oldEvent = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$oldEvent) {
        $message = "Event not found.";
    } elseif ($title === "" || $date === "" ||
              $location === "" || $category === "") {
        $message = "Please fill in all required fields.";
    } else {
        $upload = uploadPoster();

        if ($upload["error"] !== "") {
            $message = $upload["error"];
        } else {
            // Keep the existing poster if no new file was selected.
            $oldImage = $oldEvent["image"] ?? "";
            $image = ($upload["path"] !== "")
                ? $upload["path"]
                : $oldImage;

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

                // Remove the old uploaded poster only after a successful update.
                if ($upload["path"] !== "" &&
                    strpos($oldImage, "uploads/events/") === 0) {
                    $oldPath = __DIR__ . "/" . $oldImage;

                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }
            } else {
                $message = "Unable to update the event.";

                // Clean up the new file if the database update failed.
                if ($upload["path"] !== "") {
                    $newPath = __DIR__ . "/" . $upload["path"];

                    if (is_file($newPath)) {
                        unlink($newPath);
                    }
                }
            }

            $stmt->close();
        }
    }
}

// Load event for editing.
$editEvent = null;

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT * FROM events WHERE event_id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editEvent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Load all events.
$result = $conn->query(
    "SELECT * FROM events ORDER BY event_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            border-radius: 5px;
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

        .poster-preview {
            max-width: 120px;
            max-height: 100px;
            object-fit: contain;
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
    <a class="btn" href="events.php">Back to Events</a>

    <h1>Manage Events</h1>

    <?php if ($message !== ""): ?>
        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <?php if ($editEvent): ?>
        <div class="form-box">
            <h2>Edit Event</h2>

            <form method="POST" enctype="multipart/form-data"
                  action="event_manage.php?edit=<?= (int)$editEvent["event_id"] ?>">

                <input type="hidden" name="event_id"
                       value="<?= (int)$editEvent["event_id"] ?>">

                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" required
                       value="<?= htmlspecialchars($editEvent["title"] ?? "", ENT_QUOTES, "UTF-8") ?>">

                <label for="date">Date</label>
                <input type="date" id="date" name="date" required
                       value="<?= htmlspecialchars($editEvent["date"] ?? "", ENT_QUOTES, "UTF-8") ?>">

                <label for="location">Location</label>
                <input type="text" id="location" name="location" required
                       value="<?= htmlspecialchars($editEvent["location"] ?? "", ENT_QUOTES, "UTF-8") ?>">

                <label for="category">Category</label>
                <input type="text" id="category" name="category" required
                       value="<?= htmlspecialchars($editEvent["category"] ?? "", ENT_QUOTES, "UTF-8") ?>">

                <p>Current poster:</p>

                <?php if (!empty($editEvent["image"])): ?>
                    <img class="poster-preview"
                         src="<?= htmlspecialchars($editEvent["image"], ENT_QUOTES, "UTF-8") ?>"
                         alt="Current event poster">
                <?php else: ?>
                    <p>No poster uploaded.</p>
                <?php endif; ?>

                <label for="poster">Upload a new poster (optional)</label>
                <input type="file" id="poster" name="poster"
                       accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

                <p>Allowed: JPG, PNG, WebP. Maximum size: 2 MB.</p>

                <button type="submit" name="update_event">Save Changes</button>
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
            <th>Poster</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($event = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$event["event_id"] ?></td>
                    <td><?= htmlspecialchars($event["title"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                    <td><?= htmlspecialchars($event["date"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                    <td><?= htmlspecialchars($event["location"] ?? "", ENT_QUOTES, "UTF-8") ?></td>

                    <td>
                        <?php if (!empty($event["image"])): ?>
                            <img class="poster-preview"
                                 src="<?= htmlspecialchars($event["image"], ENT_QUOTES, "UTF-8") ?>"
                                 alt="Event poster">
                        <?php else: ?>
                            No poster
                        <?php endif; ?>
                    </td>

                    <td>
                        <a class="btn"
                           href="event_manage.php?edit=<?= (int)$event["event_id"] ?>">
                            Edit
                        </a>

                        <form method="POST" style="display:inline"
                              onsubmit="return confirm('Are you sure you want to delete this event?');">
                            <input type="hidden" name="event_id"
                                   value="<?= (int)$event["event_id"] ?>">
                            <button class="delete" type="submit" name="delete_event">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No events found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
