<?php
include '../model/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO produto (descricao, qtdEstoque, preco) VALUES (?, ?, ?)");
    $success = $stmt->execute([$data['descricao'], $data['qtdEstoque'], $data['preco']]);
    echo json_encode(['success' => $success]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT id, descricao, qtdEstoque, preco FROM produto WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($produto);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
    $stmt = $pdo->prepare("SELECT id, descricao, qtdEstoque, preco FROM produto WHERE descricao LIKE ?");
    $stmt->execute([$searchTerm]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($produtos);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("UPDATE produto SET descricao = ?, qtdEstoque = ?, preco = ? WHERE id = ?");
    $success = $stmt->execute([$data['descricao'], $data['qtdEstoque'], $data['preco'], $data['id']]);
    echo json_encode(['success' => $success]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $stmt = $pdo->prepare("DELETE FROM produto WHERE id = ?");
    $success = $stmt->execute([$_GET['id']]);
    echo json_encode(['success' => $success]);
}
?>
