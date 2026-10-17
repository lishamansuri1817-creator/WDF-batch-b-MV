
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "db_mysqli.php";

$sql = "SELECT * FROM events ORDER BY `date` ASC";
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);

    echo json_encode([
        "error" => "Unable to fetch events from database."
    ]);

    exit;
}

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        "id" => $row["id"] ?? null,
        "title" => $row["title"] ?? "",
        "date" => $row["date"] ?? "",
        "location" => $row["location"] ?? "",
        "category" => $row["category"] ?? "",
        "image" => $row["image"] ?? ""
    ];
}

echo json_encode(
    $events,
    JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
);
?>