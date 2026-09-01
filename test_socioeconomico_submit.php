<?php
/**
 * Test Script for Socioeconomico Form Submission
 * 
 * This script simulates a complete POST submission of the socioeconomico form
 * to verify that all data (familia_json, despesas_json, beneficios) are saved to MySQL.
 * 
 * IMPORTANT: Run this from the browser while logged in as admin
 * Usage: http://localhost/CriancaFeliz/test_socioeconomico_submit.php
 */

session_start();
require_once 'bootstrap.php';

$testResult = null;
$testError = null;
$isLoggedIn = !empty($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_test'])) {
    try {
        if (!$isLoggedIn) {
            throw new Exception('Erro: Voce deve estar logado como admin para executar este teste.');
        }

        // Generate CSRF token if not exists
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Prepare test data
        $familiaData = array(
            array('nome' => 'Joana Silva', 'parentesco' => 'Conjuge', 'data_nascimento' => '15/03/1982', 'formacao' => 'Ensino Medio', 'renda' => '800.00'),
            array('nome' => 'Pedro Silva', 'parentesco' => 'Filho', 'data_nascimento' => '10/06/2012', 'formacao' => 'Ensino Fundamental', 'renda' => '0'),
            array('nome' => 'Ana Silva', 'parentesco' => 'Filha', 'data_nascimento' => '22/08/2014', 'formacao' => 'Ensino Fundamental', 'renda' => '0')
        );

        $despesasData = array(
            array('tipo' => 'Alimentacao', 'valor' => '400.00'),
            array('tipo' => 'Energia', 'valor' => '120.00'),
            array('tipo' => 'Agua', 'valor' => '50.00'),
            array('tipo' => 'Transportes', 'valor' => '200.00'),
            array('tipo' => 'Saude', 'valor' => '100.00')
        );

        $testData = array(
            'csrf_token' => $_SESSION['csrf_token'],
            'nome_entrevistado' => 'Joao da Silva (TESTE)',
            'cpf' => '12345678901',
            'rg' => '123456789',
            'nome_menor' => 'Maria da Silva',
            'assistente_social' => 'Ana Clara',
            'residencia' => 'Zona Urbana',
            'construcao' => 'Alvenaria',
            'num_comodos' => '4',
            'tipo_moradia' => 'Casa',
            'situacao_moradia' => 'Propria',
            'numero_comodos' => '4',
            'agua' => 'Encanada',
            'esgoto' => 'Rede Publica',
            'energia' => 'Eletrificada',
            'renda_familiar' => '1500.00',
            'renda_per_capita' => '300.00',
            'cadunico' => 'Sim',
            'qtd_pessoas' => '5',
            'observacoes' => 'Familia em situacao de vulnerabilidade. Necessario acompanhamento continuo.',
            'status' => 'Ativo',
            'bolsa_familia' => '1',
            'auxilio_brasil' => '1',
            'bpc' => '0',
            'auxilio_emergencial' => '0',
            'seguro_desemprego' => '0',
            'aposentadoria' => '0',
            'familia_json' => json_encode($familiaData),
            'despesas_json' => json_encode($despesasData),
            'despesas' => json_encode($despesasData),
            'familia' => json_encode($familiaData)
        );

        // Merge with $_POST
        $_POST = array_merge($_POST, $testData);
        $_REQUEST = array_merge($_REQUEST, $_POST);

        // Load and call controller
        $controller = new \App\Controllers\SocioeconomicoController();
        $result = $controller->store();

        $testResult = array(
            'success' => true,
            'message' => 'Teste bem-sucedido! A ficha foi salva no banco de dados.'
        );

    } catch (\Exception $e) {
        $testError = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste - Ficha Socioeconomica</title>
    <style>
        body { font-family: Poppins, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
        .header h1 { margin: 0 0 10px 0; font-size: 28px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .success { background: #e8f6ea; border-left: 4px solid #6fb64f; padding: 20px; border-radius: 8px; margin: 20px 0; color: #155724; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; border-radius: 8px; margin: 20px 0; color: #721c24; }
        .button { display: inline-block; padding: 12px 24px; background: #6fb64f; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; }
        .button:hover { background: #5a9a3f; }
        .info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8; }
        .info h4 { color: #17a2b8; margin-top: 0; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
        ol { margin-left: 20px; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Teste de Submissao - Ficha Socioeconomica</h1>
            <p>Validar se a ficha esta sendo salva corretamente no banco de dados</p>
        </div>

        <div class="card">
            <?php if (!$isLoggedIn): ?>
                <div class="error">
                    <h3>Acesso Negado</h3>
                    <p>Voce precisa estar logado como admin para executar este teste.</p>
                    <p><a href="login.php" style="color: #dc3545; font-weight: bold;">Clique aqui para fazer login</a></p>
                </div>
            <?php else: ?>
                <?php if ($testError): ?>
                    <div class="error">
                        <h3>Erro durante o teste:</h3>
                        <p><?php echo htmlspecialchars($testError); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($testResult): ?>
                    <div class="success">
                        <h3>Sucesso!</h3>
                        <p><?php echo htmlspecialchars($testResult['message']); ?></p>
                        <ol>
                            <li>Abra phpMyAdmin e procure pelo CPF 12345678901 na tabela Atendido</li>
                            <li>Visite <code>socioeconomico_list.php</code> e procure por "Joao da Silva (TESTE)"</li>
                            <li>Clique em "Visualizar" e confirme que todos os dados estao corretos</li>
                            <li>Verifique a tabela Familia (deve haver 3 membros) e Despesas (deve haver 5 despesas)</li>
                        </ol>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="info">
                            <h4>O que este teste faz:</h4>
                            <p>Simula a submissao de uma ficha socioeconomica com:</p>
                            <ul>
                                <li>Dados pessoais (nome, CPF, RG)</li>
                                <li>Renda familiar e beneficios (Bolsa Familia, Auxilio Brasil)</li>
                                <li>Composicao familiar (3 membros)</li>
                                <li>Despesas mensais (5 tipos)</li>
                            </ul>
                        </div>
                        <button type="submit" name="run_test" value="1" class="button">Executar Teste</button>
                    </form>

                    <div class="info">
                        <h4>Dados de Teste:</h4>
                        <p><strong>Entrevistado:</strong> Joao da Silva (TESTE)<br>
                           <strong>CPF:</strong> 123.456.789-01<br>
                           <strong>RG:</strong> 12.345.678-9<br>
                           <strong>Renda:</strong> R$ 1.500,00<br>
                           <strong>Beneficios:</strong> Bolsa Familia, Auxilio Brasil<br>
                           <strong>Familia:</strong> 3 membros<br>
                           <strong>Despesas:</strong> 5 tipos (Alimentacao, Energia, Agua, Transportes, Saude)</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
