<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'config.php';
    $id_cliente = intval($_POST['id_cliente']);
    $id_vendedor = intval($_POST['id_vendedor']);
    $produtos = $_POST['produtos'] ?? [];
    $valor_total = 0;
    $itens = [];
    // Buscar preços e validar estoque
    foreach ($produtos as $id_produto => $qtd) {
        $qtd = intval($qtd);
        if ($qtd > 0) {
            $sql = "SELECT preco, quantidade_estoque FROM produtos WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_produto);
            $stmt->execute();
            $stmt->bind_result($preco, $estoque);
            if ($stmt->fetch()) {
                if ($qtd > $estoque) {
                    $stmt->close();
                    $conn->close();
                    header("Location: form_cadastrar_venda.php?status=error&msg=estoque_insuficiente");
                    exit;
                }
                $valor_total += $preco * $qtd;
                $itens[] = [
                    'id_produto' => $id_produto,
                    'quantidade' => $qtd,
                    'preco_unitario' => $preco
                ];
            }
            $stmt->close();
        }
    }
    if (empty($itens)) {
        $conn->close();
        header("Location: form_cadastrar_venda.php?status=error&msg=nenhum_produto");
        exit;
    }
    // Registrar venda
    $sql = "INSERT INTO vendas (id_cliente, id_vendedor, valor_total) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iid", $id_cliente, $id_vendedor, $valor_total);
    if ($stmt->execute()) {
        $id_venda = $stmt->insert_id;
        $stmt->close();
        // Registrar itens e atualizar estoque
        foreach ($itens as $item) {
            $sql = "INSERT INTO itens_venda (id_venda, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $id_venda, $item['id_produto'], $item['quantidade'], $item['preco_unitario']);
            $stmt->execute();
            $stmt->close();
            // Atualizar estoque
            $sql = "UPDATE produtos SET quantidade_estoque = quantidade_estoque - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['quantidade'], $item['id_produto']);
            $stmt->execute();
            $stmt->close();
        }
        $conn->close();
        header("Location: historico.php?status=success&msg=venda_cadastrada");
        exit;
    } else {
        $conn->close();
        header("Location: form_cadastrar_venda.php?status=error&msg=erro_cadastro");
        exit;
    }
} else {
    header("Location: form_cadastrar_venda.php");
    exit;
} 