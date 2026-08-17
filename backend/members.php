<?php
require_once 'db_connect.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // Return all members, with their plan name from the plans table
    case 'GET':
        $result = $conn->query(
            "SELECT m.id, m.name, m.gender, m.email, m.phone, m.joinDate,
                    p.id AS plan_id, p.name AS plan
             FROM members m
             LEFT JOIN plans p ON m.plan_id = p.id
             ORDER BY m.joinDate DESC"
        );

        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }

        echo json_encode($members);
        break;

    // Add a new member (JS sends plan name, we look up its ID)
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        $name = trim($data['name']);
        $gender = trim($data['gender']);
        $email = trim($data['email']);
        $phone = trim($data['phone']);
        $planName = trim($data['plan']);
        $joinDate = date('Y-m-d');

        // Look up the plan ID from the plan name
        $planStmt = $conn->prepare("SELECT id FROM plans WHERE name = ?");
        $planStmt->bind_param("s", $planName);
        $planStmt->execute();
        $planResult = $planStmt->get_result();
        $planRow = $planResult->fetch_assoc();

        if (!$planRow) {
            http_response_code(400);
            echo json_encode(["error" => "Plan not found: $planName"]);
            break;
        }
        $planId = $planRow['id'];

        // Check for duplicate email
        $check = $conn->prepare("SELECT id FROM members WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            http_response_code(409);
            echo json_encode(["error" => "Email already registered"]);
            break;
        }

        $stmt = $conn->prepare(
            "INSERT INTO members (name, gender, email, phone, plan_id, joinDate)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", $name, $gender, $email, $phone, $planId, $joinDate);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Member added", "id" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to add member"]);
        }
        break;

    // Update an existing member
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        $id = intval($data['id']);
        $name = trim($data['name']);
        $gender = trim($data['gender']);
        $email = trim($data['email']);
        $phone = trim($data['phone']);
        $planName = trim($data['plan']);

        // Look up plan ID
        $planStmt = $conn->prepare("SELECT id FROM plans WHERE name = ?");
        $planStmt->bind_param("s", $planName);
        $planStmt->execute();
        $planRow = $planStmt->get_result()->fetch_assoc();

        if (!$planRow) {
            http_response_code(400);
            echo json_encode(["error" => "Plan not found: $planName"]);
            break;
        }
        $planId = $planRow['id'];

        // Check email not used by another member
        $check = $conn->prepare("SELECT id FROM members WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            http_response_code(409);
            echo json_encode(["error" => "Email already used by another member"]);
            break;
        }

        $stmt = $conn->prepare(
            "UPDATE members SET name=?, gender=?, email=?, phone=?, plan_id=? WHERE id=?"
        );
        $stmt->bind_param("ssssis", $name, $gender, $email, $phone, $planId, $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Member updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update member"]);
        }
        break;

    // Delete a member
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = intval($data['id']);

        $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Member deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to delete member"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

$conn->close();
?>