<?php
include '../model/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO usuario (nome, login, senha, verCad, verFinanc) VALUES (?, ?, ?, ?, ?)");
    $success = $stmt->execute([$data['nome'], $data['login'], $data['senha'], $data['verCad'], $data['verFinanc']]);
    echo json_encode(['success' => $success]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT id, nome, login, verCad, verFinanc FROM usuario WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($usuario);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
    $stmt = $pdo->prepare("SELECT id, nome, login, verCad, verFinanc FROM usuario WHERE nome LIKE ?");
    $stmt->execute([$searchTerm]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usuarios);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("UPDATE usuario SET nome = ?, login = ?, senha = ?, verCad = ?, verFinanc = ? WHERE id = ?");
    $success = $stmt->execute([$data['nome'], $data['login'], $data['senha'], $data['verCad'], $data['verFinanc'], $data['id']]);
    echo json_encode(['success' => $success]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id = ?");
    $success = $stmt->execute([$_GET['id']]);
    echo json_encode(['success' => $success]);
}
?>
