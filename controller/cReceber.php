<?php  
include '../model/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO financeiro (descricao, dataEmi, tipo, dataVenci, valor, baixa, juros, desconto) VALUES (?, ?, 'E', ?, ?, 'N', ?, ?)");
    $success = $stmt->execute([
        $data['descricao'], 
        $data['dataEmi'], 
        $data['dataVenci'], 
        $data['valor'], 
        $data['juros'], 
        $data['desconto']
    ]);
    echo json_encode(['success' => $success]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT id, descricao, dataEmi, dataVenci, valor, juros, desconto FROM financeiro WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $conta = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($conta);

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
    $stmt = $pdo->prepare("SELECT id, descricao, dataEmi, dataVenci, baixa, valor, juros, desconto FROM financeiro WHERE descricao LIKE ? AND baixa = 'N' AND tipo = 'E'");
    $stmt->execute([$searchTerm]);
    $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($contas);

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM financeiro WHERE id = ?");
    $success = $stmt->execute([$id]);
    echo json_encode(['success' => $success]);
}elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("UPDATE financeiro SET baixa = ? WHERE id = ?");
    $success = $stmt->execute([$data['baixa'], $data['id']]);
    echo json_encode(['success' => $success]);
}
?>
