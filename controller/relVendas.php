<?php
include '../model/db_connect.php';

try {
    if ($_GET['tipo'] === 'vendas') {
        $query = "SELECT c.id, c.data, c.hora, c.desconto, c.total, u.nome AS usuario, cli.nome AS cliente, c.fpgto
                  FROM caixa c
                  LEFT JOIN usuario u ON u.id = c.idusuario
                  LEFT JOIN cliente cli ON cli.id = c.Idcliente";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }elseif (isset($_GET['idcaixa'])) {
        $idcaixa = $_GET['idcaixa'];
        $query = "SELECT c.id, c.idcaixa, p.descricao, c.val_unitario, c.qtd, (c.qtd * c.val_unitario) AS total 
                  FROM caixa_i c
                  INNER JOIN produto p ON p.id = c.idproduto
                  WHERE c.idcaixa = :idcaixa";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute(['idcaixa' => $idcaixa]);
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao carregar dados: ' . $e->getMessage()]);
}
?>
