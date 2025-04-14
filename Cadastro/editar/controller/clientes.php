<?php
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT id, nome, cpf, telefone, email, logradouro, numero, bairro FROM cliente WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($cliente);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("UPDATE cliente SET nome = ?, cpf = ?, telefone = ?, email = ?, logradouro = ?, numero = ?, bairro = ? WHERE id = ?");
    $success = $stmt->execute([$data['nome'], $data['cpf'], $data['telefone'], $data['email'], $data['logradouro'], $data['numero'], $data['bairro'], $data['id']]);
    echo json_encode(['success' => $success]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM cliente WHERE id = ?");
    $success = $stmt->execute([$id]);
    echo json_encode(['success' => $success]);
}
?>
