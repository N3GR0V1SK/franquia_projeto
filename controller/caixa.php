<?php
require_once '../model/db_connect.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['comanda']) && !empty($_GET['comanda'])) {
            $comanda = intval($_GET['comanda']);

            $query = "
                SELECT 
                    p.id AS codigo, 
                    p.descricao, 
                    c.qtd AS quantidade, 
                    c.val_unitario AS valorUnitario, 
                    (c.qtd * c.val_unitario) AS valorTotal,
                    u.nome
                FROM 
                    comanda c
                INNER JOIN 
                    produto p ON p.id = c.idproduto
                INNER JOIN usuario u
                    ON u.id = c.idUsu
                WHERE 
                    c.status = 'A' 
                    AND c.comanda = :comanda
            ";
    
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':comanda', $comanda, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($result) {
                echo json_encode($result);
            } else {
                echo json_encode(["X"]);
            }
            exit;
        }
    } if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert') {
        if (
            isset($_POST['numeroComanda'], $_POST['quantidade'], $_POST['senhaUsuario']) &&
            (!empty($_POST['codigoProduto']) || !empty($_POST['descricaoProduto'])) &&
            !empty($_POST['senhaUsuario'])
        ) {
            $numeroComanda = intval($_POST['numeroComanda']);
            $codigoProduto = !empty($_POST['codigoProduto']) ? intval($_POST['codigoProduto']) : null;
            $descricaoProduto = !empty($_POST['descricaoProduto']) ? $_POST['descricaoProduto'] : null;
            $quantidade = floatval($_POST['quantidade']);
            $senhaUsuario = $_POST['senhaUsuario'];
    
            try {
                $query = "SELECT id FROM usuario WHERE senha = :senha LIMIT 1";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':senha', $senhaUsuario, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$user) {
                    echo json_encode(["error" => "Senha inválida."]);
                    exit;
                }
    
                $idUsuario = $user['id'];
    
                $query = "
                    INSERT INTO comanda (comanda, status, idUsu, idproduto, val_unitario, qtd)
                    SELECT :numeroComanda, 'A', :idUsu, p.id, p.preco, :quantidade
                    FROM produto p
                    WHERE 1=1
                ";
    
                if (!empty($codigoProduto)) {
                    $query .= " AND p.id = :codigoProduto";
                }
                if (!empty($descricaoProduto)) {
                    $query .= " AND p.descricao LIKE :descricaoProduto";
                }
    
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':numeroComanda', $numeroComanda, PDO::PARAM_INT);
                $stmt->bindParam(':idUsu', $idUsuario, PDO::PARAM_INT);
                $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_STR);
    
                if (!empty($codigoProduto)) {
                    $stmt->bindParam(':codigoProduto', $codigoProduto, PDO::PARAM_INT);
                }
                if (!empty($descricaoProduto)) {
                    $descricaoProdutoParam = '%' . $descricaoProduto . '%';
                    $stmt->bindParam(':descricaoProduto', $descricaoProdutoParam, PDO::PARAM_STR);
                }
    
                if ($stmt->execute()) {
                    echo json_encode(["success" => "Produto inserido com sucesso."]);
                } else {
                    echo json_encode(["error" => "Falha ao inserir o produto."]);
                }
            } catch (PDOException $e) {
                echo json_encode(["error" => "Erro no servidor: " . $e->getMessage()]);
            }
            exit;
        } else {
            echo json_encode(["error" => "Dados incompletos para realizar a operação."]);
            exit;
        }
    } if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (isset($_POST['numeroComanda'], $_POST['idProdutoExcluir']) && !empty($_POST['numeroComanda']) && !empty($_POST['idProdutoExcluir'])) {
            $numeroComanda = intval($_POST['numeroComanda']);
            $idProdutoExcluir = intval($_POST['idProdutoExcluir']);
    
            try {
                $query = "
                    DELETE FROM comanda
                    WHERE comanda = :numeroComanda AND status = 'A' AND idproduto = :idProdutoExcluir
                ";
    
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':numeroComanda', $numeroComanda, PDO::PARAM_INT);
                $stmt->bindParam(':idProdutoExcluir', $idProdutoExcluir, PDO::PARAM_INT);
    
                if ($stmt->execute()) {
                    echo json_encode(["success" => "Produto excluído com sucesso."]);
                } else {
                    echo json_encode(["error" => "Falha ao excluir o produto."]);
                }
            } catch (PDOException $e) {
                echo json_encode(["error" => "Erro no servidor: " . $e->getMessage()]);
            }
            exit;
        } else {
            echo json_encode(["error" => "Dados incompletos para realizar a operação."]);
            exit;
        }
    }if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalize') {
        if (
            isset($_POST['numeroComanda'], $_POST['senhaUsuario'], $_POST['desconto'], $_POST['totalComanda'], $_POST['formaPagamento']) &&
            !empty($_POST['numeroComanda']) && !empty($_POST['senhaUsuario']) && !empty($_POST['totalComanda'])
        ) {
            $numeroComanda = intval($_POST['numeroComanda']);
            $senhaUsuario = $_POST['senhaUsuario'];
            $desconto = floatval($_POST['desconto']);
            $totalComanda = floatval($_POST['totalComanda']);
            $formaPagamento = $_POST['formaPagamento'];
            $codigoCliente = !empty($_POST['codigoCliente']) ? intval($_POST['codigoCliente']) : null;
    
            try {
                $query = "SELECT id FROM usuario WHERE senha = :senha LIMIT 1";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':senha', $senhaUsuario, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$user) {
                    echo json_encode(["error" => "Senha inválida."]);
                    exit;
                }
    
                $idUsuario = $user['id'];
    
                $query = "
                    UPDATE comanda 
                    SET status = 'F' 
                    WHERE comanda = :numeroComanda AND status = 'A'
                ";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':numeroComanda', $numeroComanda, PDO::PARAM_INT);
                $stmt->execute();

                $query = "
                    INSERT INTO caixa (data, hora, idusuario, desconto, total, idcliente, fpgto) 
                    VALUES (NOW(), NOW(), :idUsuario, :desconto, :totalComanda, :idCliente, :formaPagamento)
                ";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
                $stmt->bindParam(':desconto', $desconto, PDO::PARAM_STR);
                $stmt->bindParam(':totalComanda', $totalComanda, PDO::PARAM_STR);
                $stmt->bindParam(':idCliente', $codigoCliente, PDO::PARAM_INT);
                $stmt->bindParam(':formaPagamento', $formaPagamento, PDO::PARAM_STR);
                $stmt->execute();
            $idCaixa = $pdo->lastInsertId();


            $query = "
                INSERT INTO caixa_i (idcaixa, idproduto, descricao, val_unitario, qtd, total)
                SELECT :idCaixa, c.idproduto, p.descricao, c.val_unitario, c.qtd, (c.qtd * c.val_unitario)
                FROM comanda c
                INNER JOIN produto p ON p.id = c.idproduto
                WHERE c.comanda = :numeroComanda AND c.status = 'F'
            ";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idCaixa', $idCaixa, PDO::PARAM_INT);
            $stmt->bindParam(':numeroComanda', $numeroComanda, PDO::PARAM_INT);
            $stmt->execute();
    
                echo json_encode(["success" => "Comanda finalizada com sucesso."]);
            } catch (PDOException $e) {
                echo json_encode(["error" => "Erro no servidor: " . $e->getMessage()]);
            }
            exit;
        } else {
            echo json_encode(["error" => "Dados incompletos para realizar a operação."]);
            exit;
        }
    } if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'buscarCliente') {
        $codigoCliente = !empty($_GET['codigoCliente']) ? intval($_GET['codigoCliente']) : null;
        $nomeCliente = !empty($_GET['nomeCliente']) ? $_GET['nomeCliente'] : null;
    
        try {
            $query = "SELECT id AS codigo, nome FROM cliente WHERE 1=1";
            if ($codigoCliente) {
                $query .= " AND id = :codigoCliente";
            }
            if ($nomeCliente) {
                $query .= " AND nome LIKE CONCAT('%', :nomeCliente, '%')";
            }
    
            $stmt = $pdo->prepare($query);
            if ($codigoCliente) {
                $stmt->bindParam(':codigoCliente', $codigoCliente, PDO::PARAM_INT);
            }
            if ($nomeCliente) {
                $stmt->bindParam(':nomeCliente', $nomeCliente, PDO::PARAM_STR);
            }
    
            $stmt->execute();
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($cliente) {
                echo json_encode(["success" => true, "codigo" => $cliente['codigo'], "nome" => $cliente['nome']]);
            } else {
                echo json_encode(["success" => false]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "Erro no servidor: " . $e->getMessage()]);
        }
        exit;
    } if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'buscarProduto') {
        $codigoProduto = !empty($_GET['codigoProduto']) ? intval($_GET['codigoProduto']) : null;
        $descricaoProduto = !empty($_GET['descricaoProduto']) ? $_GET['descricaoProduto'] : null;
    
        try {
            $query = "SELECT id AS codigo, descricao FROM produto WHERE 1=1";
            if ($codigoProduto) {
                $query .= " AND id = :codigoProduto";
            }
            if ($descricaoProduto) {
                $query .= " AND descricao LIKE CONCAT('%', :descricaoProduto, '%')";
            }
    
            $stmt = $pdo->prepare($query);
            if ($codigoProduto) {
                $stmt->bindParam(':codigoProduto', $codigoProduto, PDO::PARAM_INT);
            }
            if ($descricaoProduto) {
                $stmt->bindParam(':descricaoProduto', $descricaoProduto, PDO::PARAM_STR);
            }
    
            $stmt->execute();
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($produto) {
                echo json_encode(["success" => true, "codigo" => $produto['codigo'], "descricao" => $produto['descricao']]);
            } else {
                echo json_encode(["success" => false]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "Erro no servidor: " . $e->getMessage()]);
        }
        exit;
    }
    echo json_encode(["error" => "Operação não reconhecida."]);
} catch (PDOException $e) {

    echo json_encode(["error" => "Erro no servidor: " . $e->getMessage()]);
}
