
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

    // Confirm that the uploaded file can actually be read as an image.
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $date = $_POST["date"] ?? "";
    $location = trim($_POST["location"] ?? "");
    $category = trim($_POST["category"] ?? "");

    if ($title === "" || $date === "" ||
        $location === "" || $category === "") {
        $message = "Please fill in all required fields.";
    } else {
        $upload = uploadPoster();

        if ($upload["error"] !== "") {
            $message = $upload["error"];
        } else {
            $image = $upload["path"];

            $stmt = $conn->prepare(
                "INSERT INTO events
                (title, `date`, location, category, image)
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
                    $message = "Unable to add the event.";

                    // Remove the uploaded poster if saving the event failed.
                    if ($image !== "" && is_file(__DIR__ . "/" . $image)) {
                        unlink(__DIR__ . "/" . $image);
                    }
                }

                $stmt->close();
            } else {
                $message = "Unable to prepare the database query.";

                if ($image !== "" && is_file(__DIR__ . "/" . $image)) {
                    unlink(__DIR__ . "/" . $image);
                }
            }
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

        .event-form button,
        .event-form a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #11666f;
            color: white;
            border: none;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .message {
            padding: 10px;
            background: #e2f5e8;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<?php showSessionTimestamp(); ?>

<div class="event-form">
    <h2>Add New Event</h2>

    <?php if ($message !== ""): ?>
        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="title">Event Title *</label>
        <input type="text" id="title" name="title" required>

        <label for="date">Event Date *</label>
        <input type="date" id="date" name="date" required>

        <label for="location">Location *</label>
        <input type="text" id="location" name="location" required>

        <label for="category">Category *</label>
        <input type="text" id="category" name="category" required>

        <label for="poster">Event Poster (optional)</label>
        <input
            type="file"
            id="poster"
            name="poster"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
        >

        <p>Allowed: JPG, PNG, WebP. Maximum size: 2 MB.</p>

        <button type="submit">Add Event</button>
        <a href="events.php">Back to Events</a>
    </form>
</div>

</body>
</html>
