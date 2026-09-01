<?php
/**
 * Debug: Verificar como o formulário calcula a renda
 */

require_once 'bootstrap.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug: Cálculo de Renda</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .box { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .test { background: #fff; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
        input { padding: 8px; margin: 5px 0; width: 200px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .resultado { margin-top: 20px; padding: 15px; background: #e7f3ff; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Debug: Cálculo de Renda Familiar</h1>
    <p>Este script simula como o JavaScript consolida dados de renda.</p>

    <div class="box">
        <h2>Teste: Simular Adição de Membros da Família</h2>
        
        <div class="test">
            <h3>Membro 1: Pai (Renda: 2000)</h3>
            <p>Nome: João da Silva</p>
            <p>Parentesco: Pai</p>
            <p>Renda: 2000.00</p>
        </div>

        <div class="test">
            <h3>Membro 2: Mãe (Renda: 1500)</h3>
            <p>Nome: Maria da Silva</p>
            <p>Parentesco: Mãe</p>
            <p>Renda: 1500.00</p>
        </div>

        <div class="resultado">
            <h3>Resultado esperado:</h3>
            <p><strong>familia_json:</strong></p>
            <pre id="familiaJson"></pre>
            <p><strong>Renda Familiar Total:</strong> R$ <span id="rendaTotal">0.00</span></p>
            <p><strong>Renda Per Capita:</strong> R$ <span id="rendaPerCapita">0.00</span> (total ÷ 2 membros)</p>
        </div>

        <button onclick="testRendaCalculation()">Testar Cálculo</button>
    </div>

    <div class="box">
        <h2>Verificar Renda Cadastrada no Banco</h2>
        <p><a href="fix_renda_marina.php" class="btn">Ver Detalhes de Marina Carla</a></p>
    </div>

    <script>
        function testRendaCalculation() {
            // Simular família
            const familia = [
                { id: 1, nome: 'João da Silva', parentesco: 'Pai', renda: 2000 },
                { id: 2, nome: 'Maria da Silva', parentesco: 'Mãe', renda: 1500 }
            ];

            // Exibir familia_json
            document.getElementById('familiaJson').textContent = JSON.stringify(familia, null, 2);

            // Calcular renda (nova lógica corrigida)
            let rendaFamiliar = 0;
            let temFamiliaComRenda = false;

            familia.forEach(m => {
                if (m.renda && parseFloat(m.renda) > 0) {
                    console.log(`Adicionando renda de ${m.nome}: ${m.renda}`);
                    rendaFamiliar += parseFloat(m.renda);
                    temFamiliaComRenda = true;
                }
            });

            // Calcular per capita
            let membros = familia.length;
            let rendaPerCapita = membros > 0 ? (rendaFamiliar / membros) : 0;

            document.getElementById('rendaTotal').textContent = rendaFamiliar.toFixed(2);
            document.getElementById('rendaPerCapita').textContent = rendaPerCapita.toFixed(2);

            console.log(`✓ Renda Familiar: R$ ${rendaFamiliar.toFixed(2)}`);
            console.log(`✓ Renda Per Capita: R$ ${rendaPerCapita.toFixed(2)}`);
            console.log(`✓ Membros: ${membros}`);
        }

        // Testar ao carregar
        window.addEventListener('load', testRendaCalculation);
    </script>
</body>
</html>
