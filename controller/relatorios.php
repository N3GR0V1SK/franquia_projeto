<?php
include '../model/db_connect.php';

try {
    $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'S';
    $baixa = isset($_GET['baixa']) ? $_GET['baixa'] : null;
    $query = "SELECT id, descricao, dataemi, dataVenci, valor, baixa, juros, desconto 
              FROM financeiro 
              WHERE tipo = :tipo";
    
    if ($baixa !== null) {
        $query .= " AND baixa = :baixa";
    }
    
    $stmt = $pdo->prepare($query);
    
    if ($baixa !== null) {
        $stmt->execute(['tipo' => $tipo, 'baixa' => $baixa]);
    } else {
        $stmt->execute(['tipo' => $tipo]);
    }

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao carregar dados: ' . $e->getMessage()]);
}
?>
