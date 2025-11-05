<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);


    $stmt = $conn->prepare("SELECT image FROM movies WHERE movie_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();

    if ($movie) {

        $del = $conn->prepare("DELETE FROM movies WHERE movie_id = ?");
        $del->bind_param("i", $id);
        if ($del->execute()) {

            if (!empty($movie['image']) && file_exists("../uploads/" . $movie['image'])) {
                unlink("../uploads/" . $movie['image']);
            }
        }
        $del->close();
    }
}

header("Location: dashboard.php");
exit;
?>
