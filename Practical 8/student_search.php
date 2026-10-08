<?php

require "db.php";

$email = "sanu@gmail.com";

$sql = "SELECT * FROM students WHERE email = ?";

$stmt = $conn->prepare($sql);

$stmt->execute([$email]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Search</title>
</head>

<body>

<h2>Student Details</h2>

<?php

if ($student) {
    echo "Student ID: " . $student["student_id"] . "<br>";
    echo "Name: " . $student["name"] . "<br>";
    echo "Email: " . $student["email"] . "<br>";
} else {
    echo "Student not found.";
}

?>

</body>
</html>