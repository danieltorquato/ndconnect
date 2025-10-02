<?php
require_once 'Config/Database.php';

// Função para buscar orçamentos com filtros
function buscarOrcamentos($filtros = []) {
    $database = new Database();
    $conn = $database->connect();

    $where = "1=1";
    $params = [];

    // Filtro por ID
    if (!empty($filtros['id'])) {
        $where .= " AND o.id = :id";
        $params[':id'] = $filtros['id'];
    }

    // Filtro por data de orçamento
    if (!empty($filtros['data_orcamento'])) {
        $where .= " AND DATE(o.data_orcamento) = :data_orcamento";
        $params[':data_orcamento'] = $filtros['data_orcamento'];
    }

    // Filtro por data de validade
    if (!empty($filtros['data_validade'])) {
        $where .= " AND DATE(o.data_validade) = :data_validade";
        $params[':data_validade'] = $filtros['data_validade'];
    }

    // Filtro por nome do cliente
    if (!empty($filtros['cliente'])) {
        $where .= " AND c.nome LIKE :cliente";
        $params[':cliente'] = '%' . $filtros['cliente'] . '%';
    }

    // Filtro por valor mínimo
    if (!empty($filtros['valor_min'])) {
        $where .= " AND o.total >= :valor_min";
        $params[':valor_min'] = $filtros['valor_min'];
    }

    // Filtro por valor máximo
    if (!empty($filtros['valor_max'])) {
        $where .= " AND o.total <= :valor_max";
        $params[':valor_max'] = $filtros['valor_max'];
    }

    $query = "SELECT o.*, c.nome as cliente_nome, c.email, c.telefone
              FROM orcamentos o
              LEFT JOIN clientes c ON o.cliente_id = c.id
              WHERE $where
              ORDER BY o.data_orcamento DESC, o.id DESC";

    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Processar filtros
$filtros = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['id'])) {
        $filtros['id'] = $_GET['id'];
    }
    if (!empty($_GET['data_orcamento'])) {
        $filtros['data_orcamento'] = $_GET['data_orcamento'];
    }
    if (!empty($_GET['data_validade'])) {
        $filtros['data_validade'] = $_GET['data_validade'];
    }
    if (!empty($_GET['cliente'])) {
        $filtros['cliente'] = $_GET['cliente'];
    }
    if (!empty($_GET['valor_min'])) {
        $filtros['valor_min'] = $_GET['valor_min'];
    }
    if (!empty($_GET['valor_max'])) {
        $filtros['valor_max'] = $_GET['valor_max'];
    }
}

$orcamentos = buscarOrcamentos($filtros);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Orçamentos - N.D Connect</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #0C2B59 0%, #E8622D 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .filters {
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .filters h3 {
            color: #0C2B59;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .filter-group input, .filter-group select {
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .filter-group input:focus, .filter-group select:focus {
            outline: none;
            border-color: #0C2B59;
            box-shadow: 0 0 0 3px rgba(12, 43, 89, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #0C2B59;
            color: white;
        }

        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-success {
            background: #25D366;
            color: white;
        }

        .btn-success:hover {
            background: #1ea952;
        }

        .results {
            padding: 30px;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .results-count {
            color: #6b7280;
            font-size: 14px;
        }

        .orcamentos-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .orcamentos-table th {
            background: #0C2B59;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
        }

        .orcamentos-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .orcamentos-table tr:hover {
            background: #f8f9fa;
        }

        .orcamentos-table tr:last-child td {
            border-bottom: none;
        }

        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-ativo {
            background: #dcfce7;
            color: #166534;
        }

        .status-expirado {
            background: #fef2f2;
            color: #dc2626;
        }

        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .no-results h3 {
            margin-bottom: 10px;
            color: #374151;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .pagination button:hover {
            background: #f3f4f6;
        }

        .pagination button.active {
            background: #0C2B59;
            color: white;
            border-color: #0C2B59;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }

            .orcamentos-table {
                font-size: 12px;
            }

            .orcamentos-table th,
            .orcamentos-table td {
                padding: 10px 8px;
            }

            .actions {
                flex-direction: column;
            }

            .results-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Histórico de Orçamentos</h1>
            <p>Gerencie e consulte todos os orçamentos gerados</p>
        </div>

        <div class="filters">
            <h3>🔍 Filtros de Pesquisa</h3>
            <form method="GET" action="">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="id">ID do Orçamento</label>
                        <input type="number" id="id" name="id" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>" placeholder="Ex: 123">
                    </div>

                    <div class="filter-group">
                        <label for="data_orcamento">Data do Orçamento</label>
                        <input type="date" id="data_orcamento" name="data_orcamento" value="<?php echo htmlspecialchars($_GET['data_orcamento'] ?? ''); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="data_validade">Data de Validade</label>
                        <input type="date" id="data_validade" name="data_validade" value="<?php echo htmlspecialchars($_GET['data_validade'] ?? ''); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="cliente">Nome do Cliente</label>
                        <input type="text" id="cliente" name="cliente" value="<?php echo htmlspecialchars($_GET['cliente'] ?? ''); ?>" placeholder="Ex: João Silva">
                    </div>

                    <div class="filter-group">
                        <label for="valor_min">Valor Mínimo (R$)</label>
                        <input type="number" id="valor_min" name="valor_min" value="<?php echo htmlspecialchars($_GET['valor_min'] ?? ''); ?>" placeholder="Ex: 100" step="0.01">
                    </div>

                    <div class="filter-group">
                        <label for="valor_max">Valor Máximo (R$)</label>
                        <input type="number" id="valor_max" name="valor_max" value="<?php echo htmlspecialchars($_GET['valor_max'] ?? ''); ?>" placeholder="Ex: 5000" step="0.01">
                    </div>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">🔍 Pesquisar</button>
                    <a href="historico_orcamentos.php" class="btn btn-secondary">🔄 Limpar Filtros</a>
                    <a href="index.php" class="btn btn-success">🏠 Voltar ao Sistema</a>
                </div>
            </form>
        </div>

        <div class="results">
            <div class="results-header">
                <h3>📋 Resultados da Pesquisa</h3>
                <div class="results-count">
                    <?php echo count($orcamentos); ?> orçamento(s) encontrado(s)
                </div>
            </div>

            <?php if (empty($orcamentos)): ?>
                <div class="no-results">
                    <h3>Nenhum orçamento encontrado</h3>
                    <p>Tente ajustar os filtros de pesquisa ou verifique se há orçamentos cadastrados.</p>
                </div>
            <?php else: ?>
                <table class="orcamentos-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Data Orçamento</th>
                            <th>Validade</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orcamentos as $orcamento): ?>
                            <?php
                            $dataOrcamento = date('d/m/Y', strtotime($orcamento['data_orcamento']));
                            $dataValidade = date('d/m/Y', strtotime($orcamento['data_validade']));
                            $valorTotal = number_format($orcamento['total'], 2, ',', '.');
                            $isExpirado = strtotime($orcamento['data_validade']) < time();
                            ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($orcamento['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td>
                                    <div><strong><?php echo htmlspecialchars($orcamento['cliente_nome']); ?></strong></div>
                                    <div style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars($orcamento['email']); ?></div>
                                </td>
                                <td><?php echo $dataOrcamento; ?></td>
                                <td><?php echo $dataValidade; ?></td>
                                <td><strong>R$ <?php echo $valorTotal; ?></strong></td>
                                <td>
                                    <span class="status <?php echo $isExpirado ? 'status-expirado' : 'status-ativo'; ?>">
                                        <?php echo $isExpirado ? 'Expirado' : 'Ativo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="simple_pdf.php?id=<?php echo $orcamento['id']; ?>" class="btn btn-primary btn-sm" target="_blank">
                                            👁️ Visualizar
                                        </a>
                                        <a href="pdf_real.php?id=<?php echo $orcamento['id']; ?>" class="btn btn-secondary btn-sm" target="_blank">
                                            📄 PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-submit form quando ID for preenchido
        document.getElementById('id').addEventListener('input', function() {
            if (this.value.length >= 3) {
                this.form.submit();
            }
        });

        // Confirmar exclusão (se implementada futuramente)
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este orçamento?')) {
                // Implementar exclusão aqui
                console.log('Excluir orçamento:', id);
            }
        }
    </script>
</body>
</html>
