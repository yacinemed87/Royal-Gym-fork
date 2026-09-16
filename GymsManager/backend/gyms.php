<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db_connect.php";

function connect_gym($db_name)
{
    global $servername, $username, $password;
    $conn = new mysqli($servername, $username, $password, $db_name);

    if ($conn->connect_error) {
        return null;
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

// gives you the gym's row from registry
function get_gym($typed_name)
{
    global $connReg;

    if ($connReg == null) {
        return null;
    }

    $stmt = $connReg->prepare("SELECT * FROM gyms WHERE name = ? OR db_name = ?");
    $stmt->bind_param("ss", $typed_name, $typed_name);
    $stmt->execute();

    $gym = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $gym;
}

function get_gym_info()
{
    global $connGym;
    if(isset($connGym)){
        $stmt = $connGym->prepare("SELECT * FROM gyminfo");
        $stmt->execute();

        $info = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return $info;
    }
    return null;
}
