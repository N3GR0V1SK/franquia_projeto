<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $senhaUsuario = $_POST['senhaUsuario'];

    try {
        include '../model/db_connect.php';

        $query = "SELECT id, verCad, verFinanc FROM usuario WHERE senha = :senha LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':senha', $senhaUsuario, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(["error" => "Senha inválida."]);
            exit;
        }

        switch ($action) {
            case 'acessarCadastro':
                if ($user['verCad'] === 'S') {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["error" => "Você não tem permissão para acessar o cadastro."]);
                }
                break;

            case 'acessarFinanceiro':
                if ($user['verFinanc'] === 'S') {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["error" => "Você não tem permissão para acessar o financeiro."]);
                }
                break;

            case 'acessarRelatorio':
                if ($user['verFinanc'] === 'S') {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["error" => "Você não tem permissão para acessar os relatórios."]);
                }
                break;

            default:
                echo json_encode(["error" => "Ação inválida."]);
                break;
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erro no servidor: " . $e->getMessage()]);
    }
    exit;
}
?>
