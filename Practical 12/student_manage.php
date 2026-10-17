
<?php
require_once "auth.php";
requireAdmin();

require_once "db_mysqli.php";

$message = "";
$messageType = "";

if (!isset($_SESSION["student_csrf"])) {
    $_SESSION["student_csrf"] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION["student_csrf"];

// Handle add, edit and delete operations
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["student_csrf"], $_POST["csrf_token"])
    ) {
        $message = "Invalid request. Please try again.";
        $messageType = "error";
    } else {

        $action = $_POST["action"] ?? "";

        // ADD STUDENT
        if ($action === "add") {

            $name = trim($_POST["name"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $username = trim($_POST["username"] ?? "");
            $password = $_POST["password"] ?? "";
            $role = $_POST["role"] ?? "student";

            if (
                $name === "" || $email === "" ||
                $username === "" || $password === ""
            ) {
                $message = "Please fill in all required fields.";
                $messageType = "error";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Please enter a valid email address.";
                $messageType = "error";
            } elseif (
                strlen($name) > 100 ||
                strlen($email) > 100 ||
                strlen($username) > 50
            ) {
                $message = "One or more fields exceed the allowed length.";
                $messageType = "error";
            } elseif (strlen($password) < 8) {
                $message = "Password must contain at least 8 characters.";
                $messageType = "error";
            } elseif (!in_array($role, ["student", "admin"], true)) {
                $message = "Invalid role selected.";
                $messageType = "error";
            } else {

                $check = $conn->prepare(
                    "SELECT student_id FROM students
                     WHERE email = ? OR username = ?"
                );
                $check->bind_param("ss", $email, $username);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $message = "Email or username already exists.";
                    $messageType = "error";
                } else {
                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $stmt = $conn->prepare(
                        "INSERT INTO students
                         (name, email, username, password, role)
                         VALUES (?, ?, ?, ?, ?)"
                    );
                    $stmt->bind_param(
                        "sssss",
                        $name,
                        $email,
                        $username,
                        $hashedPassword,
                        $role
                    );

                    if ($stmt->execute()) {
                        $message = "Student added successfully!";
                        $messageType = "success";
                    } else {
                        $message = "Unable to add student.";
                        $messageType = "error";
                    }
                    $stmt->close();
                }
                $check->close();
            }
        }

        // EDIT STUDENT
        elseif ($action === "edit") {

            $id = filter_var(
                $_POST["student_id"] ?? "",
                FILTER_VALIDATE_INT
            );
            $name = trim($_POST["name"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $username = trim($_POST["username"] ?? "");
            $newPassword = $_POST["password"] ?? "";
            $role = $_POST["role"] ?? "";

            if (
                !$id || $name === "" || $email === "" ||
                $username === ""
            ) {
                $message = "Please enter valid student details.";
                $messageType = "error";
            } elseif (
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($name) > 100 ||
                strlen($email) > 100 ||
                strlen($username) > 50
            ) {
                $message = "Please check the entered details.";
                $messageType = "error";
            } elseif (!in_array($role, ["student", "admin"], true)) {
                $message = "Invalid role selected.";
                $messageType = "error";
            } elseif (
                $newPassword !== "" && strlen($newPassword) < 8
            ) {
                $message = "New password must contain at least 8 characters.";
                $messageType = "error";
            } else {

                // Do not allow an administrator to remove
                // their own admin access from this page.
                if (
                    $id == $_SESSION["student_id"] &&
                    $role !== "admin"
                ) {
                    $message = "You cannot remove your own admin role.";
                    $messageType = "error";
                } else {
                    $check = $conn->prepare(
                        "SELECT student_id FROM students
                         WHERE (email = ? OR username = ?)
                         AND student_id != ?"
                    );
                    $check->bind_param("ssi", $email, $username, $id);
                    $check->execute();
                    $check->store_result();

                    if ($check->num_rows > 0) {
                        $message = "Email or username is already in use.";
                        $messageType = "error";
                    } else {
                        if ($newPassword !== "") {
                            $hashedPassword = password_hash(
                                $newPassword,
                                PASSWORD_DEFAULT
                            );

                            $stmt = $conn->prepare(
                                "UPDATE students
                                 SET name=?, email=?, username=?,
                                     password=?, role=?
                                 WHERE student_id=?"
                            );
                            $stmt->bind_param(
                                "sssssi",
                                $name,
                                $email,
                                $username,
                                $hashedPassword,
                                $role,
                                $id
                            );
                        } else {
                            $stmt = $conn->prepare(
                                "UPDATE students
                                 SET name=?, email=?, username=?, role=?
                                 WHERE student_id=?"
                            );
                            $stmt->bind_param(
                                "ssssi",
                                $name,
                                $email,
                                $username,
                                $role,
                                $id
                            );
                        }

                        if ($stmt->execute()) {
                            $message = "Student updated successfully!";
                            $messageType = "success";
                        } else {
                            $message = "Unable to update student.";
                            $messageType = "error";
                        }

                        $stmt->close();
                    }
                    $check->close();
                }
            }
        }

        // DELETE STUDENT
        elseif ($action === "delete") {

            $id = filter_var(
                $_POST["student_id"] ?? "",
                FILTER_VALIDATE_INT
            );

            if (!$id) {
                $message = "Invalid student ID.";
                $messageType = "error";
            } elseif ($id == $_SESSION["student_id"]) {
                $message = "You cannot delete your own account.";
                $messageType = "error";
            } else {
                $stmt = $conn->prepare(
                    "DELETE FROM students WHERE student_id = ?"
                );
                $stmt->bind_param("i", $id);

                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $message = "Student deleted successfully!";
                    $messageType = "success";
                } else {
                    $message = "Student not found or could not be deleted.";
                    $messageType = "error";
                }

                $stmt->close();
            }
        }
    }
}

// Load a student for editing
$editStudent = null;
$editId = filter_input(INPUT_GET, "edit", FILTER_VALIDATE_INT);

if ($editId) {
    $stmt = $conn->prepare(
        "SELECT student_id, name, email, username, role
         FROM students WHERE student_id = ?"
    );
    $stmt->bind_param("i", $editId);
    $stmt->execute();

    $result = $stmt->get_result();
    $editStudent = $result->fetch_assoc();

    $stmt->close();

    if (!$editStudent) {
        $message = "Student record not found.";
        $messageType = "error";
    }
}

// SEARCH AND FILTER
$search = trim($_GET["search"] ?? "");
$filterRole = $_GET["role"] ?? "";

if (!in_array($filterRole, ["", "student", "admin"], true)) {
    $filterRole = "";
}

$sql = "SELECT student_id, name, email, username, role
        FROM students WHERE 1=1";

$types = "";
$params = [];

if ($search !== "") {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR username LIKE ?)";
    $searchTerm = "%" . $search . "%";

    $types .= "sss";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($filterRole !== "") {
    $sql .= " AND role = ?";
    $types .= "s";
    $params[] = $filterRole;
}

$sql .= " ORDER BY student_id DESC";

$stmt = $conn->prepare($sql);

if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | StudentSphere</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            background: #f3f6fb;
            color: #263449;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1150px;
            margin: auto;
        }

        h1 {
            color: #253d70;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #667085;
            margin-bottom: 25px;
        }

        .panel {
            background: white;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 3px 12px #0000000d;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccd4e0;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        button, .button {
            display: inline-block;
            border: none;
            border-radius: 6px;
            padding: 10px 15px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .primary {
            background: #3157a4;
            color: white;
        }

        .edit {
            background: #e8efff;
            color: #244a91;
        }

        .delete {
            background: #ffe9e9;
            color: #b42318;
        }

        .cancel {
            background: #e9edf3;
            color: #263449;
        }

        .message {
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .success {
            background: #e3f8e9;
            color: #176b35;
        }

        .error {
            background: #ffe7e7;
            color: #a51d1d;
        }

        .search-grid {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
            gap: 10px;
            align-items: center;
        }

        .search-grid input,
        .search-grid select {
            margin-bottom: 0;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th, td {
            text-align: left;
            padding: 13px 10px;
            border-bottom: 1px solid #e6eaf0;
        }

        th {
            background: #edf2fb;
            color: #253d70;
        }

        .role {
            display: inline-block;
            padding: 5px 9px;
            background: #edf2ff;
            border-radius: 20px;
            font-size: 12px;
        }

        .actions {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        .actions form {
            margin: 0;
        }

        .actions button {
            margin: 0;
        }

        .empty {
            text-align: center;
            padding: 25px;
            color: #667085;
        }

        .top-link {
            display: inline-block;
            margin-bottom: 18px;
            color: #3157a4;
            text-decoration: none;
        }

        @media (max-width: 650px) {
            body {
                padding: 14px;
            }

            .form-grid,
            .search-grid {
                grid-template-columns: 1fr;
            }

            .panel {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<?php showSessionTimestamp(); ?>

<div class="container">

    <a class="top-link" href="index.html">
        &larr; Back to StudentSphere
    </a>

    <h1>Student Management</h1>
    <p class="subtitle">
        Add, view, edit, delete, search and filter student records.
    </p>

    <?php if ($message !== ""): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <!-- ADD OR EDIT FORM -->
    <div class="panel">
        <h2>
            <?= $editStudent ? "Edit Student" : "Add New Student" ?>
        </h2>

        <form method="POST" action="student_manage.php">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($csrfToken) ?>">

            <input type="hidden" name="action"
                   value="<?= $editStudent ? "edit" : "add" ?>">

            <?php if ($editStudent): ?>
                <input type="hidden" name="student_id"
                       value="<?= (int)$editStudent["student_id"] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div>
                    <label for="name">Full Name</label>
                    <input id="name" name="name" type="text"
                           maxlength="100" required
                           value="<?= htmlspecialchars($editStudent["name"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email"
                           maxlength="100" required
                           value="<?= htmlspecialchars($editStudent["email"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                </div>

                <div>
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text"
                           maxlength="50" required
                           value="<?= htmlspecialchars($editStudent["username"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                </div>

                <div>
                    <label for="password">
                        <?= $editStudent
                            ? "New Password (leave blank to keep current password)"
                            : "Password" ?>
                    </label>

                    <input id="password" name="password" type="password"
                           <?= $editStudent ? "" : "required" ?>
                           minlength="8" autocomplete="new-password"
                           placeholder="<?= $editStudent ? "Leave blank to keep current password" : "Minimum 8 characters" ?>">
                </div>

                <div>
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="student"
                            <?= ($editStudent["role"] ?? "student") === "student" ? "selected" : "" ?>>
                            Student
                        </option>

                        <option value="admin"
                            <?= ($editStudent["role"] ?? "") === "admin" ? "selected" : "" ?>>
                            Admin
                        </option>
                    </select>
                </div>
            </div>

            <button type="submit" class="primary">
                <?= $editStudent ? "Save Changes" : "Add Student" ?>
            </button>

            <?php if ($editStudent): ?>
                <a class="button cancel" href="student_manage.php">
                    Cancel
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- SEARCH AND FILTER -->
    <div class="panel">
        <h2>Search and Filter Students</h2>

        <form method="GET" action="student_manage.php"
              class="search-grid">

            <input type="search" name="search"
                   placeholder="Search name, email or username..."
                   value="<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?>">

            <select name="role">
                <option value="">All Roles</option>
                <option value="student"
                    <?= $filterRole === "student" ? "selected" : "" ?>>
                    Students
                </option>
                <option value="admin"
                    <?= $filterRole === "admin" ? "selected" : "" ?>>
                    Admins
                </option>
            </select>

            <button type="submit" class="primary">Search</button>

            <a class="button cancel" href="student_manage.php">
                Reset
            </a>
        </form>
    </div>

    <!-- STUDENT TABLE -->
    <div class="panel">
        <h2>Student Records</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($students->num_rows > 0): ?>

                    <?php while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$student["student_id"] ?></td>

                            <td>
                                <?= htmlspecialchars($student["name"], ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($student["email"], ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($student["username"] ?? "", ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <span class="role">
                                    <?= htmlspecialchars($student["role"], ENT_QUOTES, "UTF-8") ?>
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a class="button edit"
                                       href="student_manage.php?edit=<?= (int)$student["student_id"] ?>">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="student_manage.php"
                                          onsubmit="return confirm('Are you sure you want to delete this student?');">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?= htmlspecialchars($csrfToken) ?>">

                                        <input type="hidden"
                                               name="action" value="delete">

                                        <input type="hidden"
                                               name="student_id"
                                               value="<?= (int)$student["student_id"] ?>">

                                        <button type="submit" class="delete">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty">
                            No student records found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
