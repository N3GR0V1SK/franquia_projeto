<?php
session_start();
include '../model/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE login = ? AND senha = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: ../menu.html");
        exit();
    } else {
        echo "<script>alert('Usuário ou senha incorretos!'); window.location.href = '../index.html';</script>";
    }
}
?>