
<?php

require "db.php";

$eventTitle = trim($_GET["event"] ?? "");
$message = "";
$success = false;

if ($eventTitle == "") {
    $message = "Please select an event from the events page.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $eventTitle = trim($_POST["event_title"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($email == "") {
        $message = "Please enter your student email.";
    } else {
        try {
            // Find the student using their email
            $studentSql = "SELECT student_id FROM students WHERE email = ?";
            $studentStmt = $conn->prepare($studentSql);
            $studentStmt->execute([$email]);
            $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                $message = "Student not found. Please register your student account first.";
            } else {
                // Find the selected event in the database
                $eventSql = "SELECT event_id FROM events WHERE title = ?";
                $eventStmt = $conn->prepare($eventSql);
                $eventStmt->execute([$eventTitle]);
                $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

                if (!$event) {
                    $message = "This event is not present in the database. Please add this event to the events table first.";
                } else {
                    // Check whether the student already registered
                    $checkSql = "SELECT registration_id
                                 FROM registrations
                                 WHERE student_id = ? AND event_id = ?";

                    $checkStmt = $conn->prepare($checkSql);
                    $checkStmt->execute([
                        $student["student_id"],
                        $event["event_id"]
                    ]);

                    if ($checkStmt->fetch()) {
                        $message = "You have already registered for this event.";
                    } else {
                        // Save the registration
                        $sql = "INSERT INTO registrations
                                (student_id, event_id, registration_date)
                                VALUES (?, ?, ?)";

                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            $student["student_id"],
                            $event["event_id"],
                            date("Y-m-d")
                        ]);

                        $message = "Event registration successful! Your registration has been saved in the database.";
                        $success = true;
                    }
                }
            }

        } catch (PDOException $e) {
            $message = "Unable to complete registration. Please check the database and try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentSphere | Event Registration</title>
    <link rel="stylesheet" href="register.css">
</head>

<body>

<header>
    <h1>StudentSphere</h1>
    <p>Your Complete Student Hub</p>
</header>

<section class="banner">
    <h2>Event Registration</h2>
    <p>Register for your selected StudentSphere event.</p>
</section>

<section>
    <div class="register-box">

        <?php if ($success) { ?>

            <h2>Registration Successful!</h2>

        <?php } else { ?>

            <h2>Register for an Event</h2>

        <?php } ?>

        <p><?php echo htmlspecialchars($message); ?></p>

        <?php if (!$success && $eventTitle != "") { ?>

            <form action="event_registration.php" method="POST">

                <label>Selected Event</label>
                <br><br>

                <input
                    type="text"
                    name="event_title"
                    value="<?php echo htmlspecialchars($eventTitle); ?>"
                    readonly
                >

                <br><br>

                <label>Student Email</label>
                <br><br>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your registered student email"
                    required
                >

                <br><br>

                <input type="submit" value="Register for Event">

            </form>

        <?php } ?>

        <br>

        <a href="events.html">Back to Events</a>

    </div>
</section>

<footer>
    <p>© 2026 StudentSphere</p>
</footer>

</body>
</html>