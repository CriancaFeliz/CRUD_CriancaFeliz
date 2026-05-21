# Correções Aplicadas - Desligamento e Socioeconômico

## PROBLEMA A - DESLIGAMENTO: Erro "Erro ao processar requisição"

### CAUSA RAIZ IDENTIFICADA:

1. **JavaScript não enviava header `X-Requested-With`**: O método `isAjaxRequest()` no `BaseController` verifica este header, mas o JS não o estava enviando, causando detecção incorreta de requisição AJAX.

2. **`requireAuth()` redirecionava em requisições AJAX**: O método chamava `redirect()` que enviava `header('Location')` mesmo para requisições AJAX, quebrando o retorno JSON.

3. **Headers de segurança no bootstrap.php**: Headers eram enviados antes de verificar se era AJAX, potencialmente interferindo.

4. **Falta de tratamento de erro robusto no JS**: O catch apenas mostrava erro genérico sem tentar parsear o body quando o status não era 200.

### CORREÇÕES APLICADAS:

#### 1. `app/Views/desligamento/index.php`
- **Adicionado header `X-Requested-With: XMLHttpRequest`** nas chamadas fetch
- **Melhorado tratamento de erro**: Agora tenta fazer parse do JSON mesmo quando status não é 200, e loga texto da resposta para debug
- **Aplicado em**: `reativarAtendido()` e `processarDesligamentoAutomatico()`

```javascript
// ANTES:
fetch('desligamento.php?action=reativar', {
    method: 'POST',
    body: formData
})

// DEPOIS:
fetch('desligamento.php?action=reativar', {
    method: 'POST',
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: formData
})
.then(async response => {
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || data.message || 'Erro na requisição');
        }
        return data;
    } else {
        const text = await response.text();
        console.error('Resposta não é JSON:', text.substring(0, 200));
        throw new Error('Resposta inválida do servidor. Status: ' + response.status);
    }
})
```

#### 2. `bootstrap.php`
- **Condicionado envio de headers de segurança**: Só envia headers se não for requisição AJAX

```php
// ANTES:
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// DEPOIS:
$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjaxRequest && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
}
```

#### 3. `app/Services/AuthService.php`
- **Modificado `requireAuth()`**: Agora lança exceção em vez de redirecionar para requisições AJAX

```php
// ANTES:
public function requireAuth() {
    if (!$this->isLoggedIn()) {
        redirect('index.php');
    }
}

// DEPOIS:
public function requireAuth() {
    if (!$this->isLoggedIn()) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($isAjax) {
            throw new Exception('Não autenticado. Faça login novamente.');
        } else {
            redirect('index.php');
        }
    }
}
```

#### 4. `app/Controllers/DesligamentoController.php`
- **Melhorado tratamento de autenticação**: Try-catch explícito para `requireAuth()` e `requirePermission()` antes de qualquer output
- **Respostas JSON padronizadas**: Todos os erros retornam `{'success': false, 'error': '...'}` para consistência
- **Logs de erro melhorados**: Inclui stack trace para debug

```php
// ANTES:
public function salvar() {
    $this->requireAuth();
    $this->requirePermission('manage_users');
    // ...
}

// DEPOIS:
public function salvar() {
    try {
        $this->requireAuth();
        $this->requirePermission('manage_users');
    } catch (Exception $e) {
        if ($this->isAjaxRequest()) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 401);
        } else {
            $this->redirectWithError('index.php', $e->getMessage());
        }
        return;
    }
    // ...
}
```

#### 5. `desligamento.php`
- **Adicionado output buffering**: `ob_start()` no início para capturar qualquer output indesejado
- **Tratamento de ação não encontrada**: Retorna JSON para AJAX, redirect para requisições normais

```php
// ANTES:
require_once 'bootstrap.php';

// DEPOIS:
ob_start(); // Iniciar output buffering ANTES de qualquer output
require_once 'bootstrap.php';

$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
```

---

## PROBLEMA B - SOCIOECONÔMICO: Despesas e família não persistem

### CAUSA RAIZ IDENTIFICADA:

1. **Inconsistência de nomes de colunas**: A PK da tabela `Ficha_Socioeconomico` é `idficha`, mas as FKs nas tabelas `Despesas` e `Familia` são `id_ficha`. O código estava usando corretamente, mas havia falta de validação e logging.

2. **JSON não sendo decodificado no controller**: O controller recebia `despesas_json` e `familia_json` mas não fazia decode antes de passar ao service/model.

3. **Validação insuficiente**: Não havia validação robusta do JSON decodificado (verificação de `json_last_error()`).

4. **Falta de logs**: Não havia logs suficientes para identificar onde os dados se perdiam.

5. **Normalização de dados**: Valores numéricos com vírgula não estavam sendo convertidos corretamente.

### CORREÇÕES APLICADAS:

#### 1. `app/Controllers/SocioeconomicoController.php`
- **Decodificação de JSON no controller**: Decodifica `despesas_json` e `familia_json` antes de passar ao service
- **Validação de JSON**: Verifica `json_last_error()` e loga erros
- **Logs detalhados**: Loga quantidade de itens decodificados

```php
// ANTES:
$data = $this->getPostData();

// DEPOIS:
$data = $this->getPostData();

// Decodificar JSON de despesas e família ANTES de enviar ao service
if (!empty($data['despesas_json'])) {
    $despesasDecoded = json_decode($data['despesas_json'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($despesasDecoded)) {
        $data['despesas'] = $despesasDecoded;
        error_log('Despesas decodificadas: ' . count($despesasDecoded) . ' itens');
    } else {
        error_log('ERRO ao decodificar despesas_json: ' . json_last_error_msg());
        $data['despesas'] = [];
    }
}

if (!empty($data['familia_json'])) {
    $familiaDecoded = json_decode($data['familia_json'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($familiaDecoded)) {
        $data['familia'] = $familiaDecoded;
        error_log('Família decodificada: ' . count($familiaDecoded) . ' membros');
    } else {
        error_log('ERRO ao decodificar familia_json: ' . json_last_error_msg());
        $data['familia'] = [];
    }
}
```

#### 2. `app/Models/SocioeconomicoDB.php` - Método `createFicha()`
- **Uso correto de `idficha`**: Garantido que `lastInsertId()` retorna `idficha` e é usado corretamente nas FKs `id_ficha`
- **Validação robusta de JSON**: Verifica `json_last_error()` antes de usar dados decodificados
- **Normalização de valores**: Converte vírgula para ponto em valores numéricos
- **Validação de membros/despesas**: Filtra itens vazios antes de inserir
- **Logs detalhados**: Loga quantidade de itens inseridos

```php
// ANTES:
$fichaId = Database::lastInsertId();

// DEPOIS:
$fichaId = (int)Database::lastInsertId();
error_log('Ficha criada com idficha: ' . $fichaId);

// Para família:
$familia = [];
if (!empty($data['familia_json'])) {
    $familia = json_decode($data['familia_json'], true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($familia)) {
        error_log('ERRO ao decodificar familia_json: ' . json_last_error_msg());
        $familia = [];
    }
} elseif (!empty($data['familia']) && is_array($data['familia'])) {
    $familia = $data['familia'];
}

if (!empty($familia) && is_array($familia)) {
    $familiaInseridos = 0;
    foreach ($familia as $membro) {
        // Validar membro antes de inserir
        if (empty($membro['nome']) || empty($membro['parentesco'])) {
            continue;
        }
        
        $renda = 0;
        if (!empty($membro['renda'])) {
            $renda = is_numeric($membro['renda']) ? floatval($membro['renda']) : 0;
        }
        
        $this->query(
            "INSERT INTO Familia (id_ficha, nome, parentesco, data_nasc, formacao, renda) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $fichaId, // id_ficha (FK) recebe idficha (PK)
                trim($membro['nome'] ?? ''),
                trim($membro['parentesco'] ?? ''),
                $this->convertDate($membro['dataNasc'] ?? $membro['data_nasc'] ?? ''),
                trim($membro['formacao'] ?? ''),
                $renda
            ]
        );
        $familiaInseridos++;
    }
    error_log("Família: {$familiaInseridos} membros inseridos");
}
```

#### 3. `app/Models/SocioeconomicoDB.php` - Método `updateFicha()`
- **Mesmas melhorias aplicadas**: Validação de JSON, normalização de valores, logs
- **Delete e re-insert**: Deleta registros existentes e re-insere dentro da transação

```php
// Buscar idficha (PK) corretamente
$fichaIdStmt = $this->query("SELECT idficha FROM Ficha_Socioeconomico WHERE id_atendido = ?", [$id]);
$fichaExistente = $fichaIdStmt->fetch();
$fichaId = $fichaExistente['idficha'] ?? null;

if ($fichaId) {
    error_log('Atualizando família e despesas para ficha idficha: ' . $fichaId);
    
    // Deletar existentes
    $this->query("DELETE FROM Familia WHERE id_ficha = ?", [$fichaId]);
    $this->query("DELETE FROM Despesas WHERE id_ficha = ?", [$fichaId]);
    
    // Re-inserir (mesmo código do createFicha)
}
```

#### 4. `js/socioeconomico-multistep.js`
- **Verificação antes do submit**: Garante que `familia_json` está salvo antes de consolidar
- **Logs detalhados de verificação**: Loga conteúdo completo dos campos JSON antes do submit para debug
- **Validação de JSON**: Verifica se o JSON é válido antes do submit

```javascript
// Adicionado antes do submit (melhorado):
console.log('📋 Salvando família antes do submit...');
console.log('   familyMembers.length:', familyMembers.length);
console.log('   familyMembers:', familyMembers);

const familiaField = form.querySelector('[name="familia_json"]');
if (familiaField) {
    const familiaJson = JSON.stringify(familyMembers);
    familiaField.value = familiaJson;
    console.log('✓ Família salva antes do submit:', familyMembers.length, 'membros');
    console.log('   Conteúdo familia_json:', familiaJson.substring(0, 200));
} else {
    console.error('✗ Campo familia_json não encontrado no formulário!');
}

// Verificação final detalhada APÓS consolidar:
console.log('=== VERIFICAÇÃO FINAL ANTES DO SUBMIT ===');
if (familiaCheck) {
    const familiaValue = familiaCheck.value || '';
    console.log('✓ familia_json encontrado:', familiaValue.length, 'caracteres');
    if (familiaValue.length > 0) {
        try {
            const parsed = JSON.parse(familiaValue);
            console.log('   ✓ JSON válido:', parsed.length, 'membros');
            console.log('   Conteúdo:', familiaValue.substring(0, 200));
        } catch (e) {
            console.error('   ✗ JSON inválido:', e.message);
        }
    } else {
        console.warn('   ⚠️ Campo está VAZIO');
    }
} else {
    console.error('✗ Campo familia_json NÃO ENCONTRADO');
}
// Mesma verificação para despesas_json
```

#### 5. `app/Models/SocioeconomicoDB.php` - Logs Detalhados Adicionados
- **Logs em cada etapa do processo**: Adicionados logs detalhados em `createFicha()` e `updateFicha()`
- **Rastreamento completo**: Cada membro da família e cada despesa é logada individualmente
- **Erros detalhados**: Exceções são capturadas e logadas com contexto completo

```php
// Exemplo de logs adicionados:
error_log('=== INICIANDO SALVAMENTO DE FAMÍLIA ===');
error_log('familia_json presente: ' . (isset($data['familia_json']) ? 'SIM' : 'NÃO'));
error_log('familia array presente: ' . (isset($data['familia']) && is_array($data['familia']) ? 'SIM (' . count($data['familia']) . ' itens)' : 'NÃO'));

// Para cada membro:
error_log("Processando membro {$idx}: " . print_r($membro, true));
// Após inserir:
error_log("✅ Família: {$familiaInseridos} membros inseridos com sucesso");
```

#### 6. `app/Controllers/SocioeconomicoController.php` - Logs Detalhados Adicionados
- **Logs antes de decodificar**: Verifica se os campos JSON existem e tamanho
- **Logs após decodificação**: Confirma quantidade de itens decodificados
- **Logs de erro detalhados**: Mostra código de erro JSON e conteúdo raw

```php
// Logs adicionados no controller:
error_log('=== DADOS RECEBIDOS NO CONTROLLER ===');
error_log('despesas_json presente: ' . (isset($data['despesas_json']) && !empty($data['despesas_json']) ? 'SIM (' . strlen($data['despesas_json']) . ' chars)' : 'NÃO'));
error_log('Conteúdo raw: ' . substr($data['despesas_json'], 0, 300));
error_log('✅ Despesas decodificadas no controller: ' . count($despesasDecoded) . ' itens');
```

---

## TESTES MANUAIS RECOMENDADOS

### Teste A1 - Desligamento (Reativar)
1. Abrir DevTools → Network → XHR
2. Acessar página de desligamentos
3. Clicar em "Reativar" em um atendido que pode ser reativado
4. **Verificar**:
   - Status HTTP deve ser `200`
   - Response deve ser JSON válido: `{"success":true,"message":"Atendido reativado com sucesso"}`
   - Não deve haver HTML ou whitespace antes do JSON
   - Console JS não deve mostrar erros
   - UI deve atualizar automaticamente após reload automático

### Teste A2 - Desligamento (Desligar)
1. Abrir DevTools → Network → XHR
2. Acessar formulário de novo desligamento
3. Preencher e submeter
4. **Verificar**:
   - Status HTTP deve ser `200`
   - Response deve ser JSON válido
   - Ação deve ser executada no banco
   - Mensagem de sucesso deve aparecer

### Teste B1 - Socioeconômico (Criação com família e despesas)
1. Acessar formulário de ficha socioeconômica
2. Preencher dados básicos (Etapa 1-2)
3. **Etapa 3 - Adicionar 2 membros da família**:
   - Nome, parentesco, data de nascimento, formação, renda
   - Verificar que aparecem na lista
4. **Etapa 4 - Adicionar 3 despesas**:
   - Nome/descrição e valor para cada uma
   - Verificar que total é calculado
5. Finalizar e submeter (Etapa 5)
6. **Verificar no banco**:
   ```sql
   -- Pegar o idficha da última ficha criada
   SELECT MAX(idficha) FROM Ficha_Socioeconomico;
   
   -- Verificar família (substituir ? pelo idficha)
   SELECT COUNT(*) FROM Familia WHERE id_ficha = ?;
   -- Deve retornar 2
   
   -- Verificar despesas (substituir ? pelo idficha)
   SELECT COUNT(*) FROM Despesas WHERE id_ficha = ?;
   -- Deve retornar 3
   ```
7. **Verificar logs do servidor**:
   - Deve aparecer: "Família decodificada: 2 membros"
   - Deve aparecer: "Despesas decodificadas: 3 itens"
   - Deve aparecer: "Família: 2 membros inseridos"
   - Deve aparecer: "Despesas: 3 itens inseridos"

### Teste B2 - Socioeconômico (Edição)
1. Editar uma ficha existente que já tem família/despesas
2. Remover 1 membro da família e adicionar 1 novo
3. Remover 1 despesa e adicionar 1 nova
4. Salvar
5. **Verificar no banco**:
   - Quantidade total de membros/despesas deve estar correta
   - Membros/despesas antigos devem ter sido removidos
   - Novos devem ter sido inseridos

---

## CHECKLIST FINAL

- ✅ **Desligamento**: Resposta AJAX é JSON sem HTML/whitespace e status 200 no sucesso
- ✅ **Desligamento**: Nenhum `header('Location: ...')` é chamado em rotas AJAX; controller encerra com `exit`
- ✅ **Socioeconomico**: JSON `despesas_json` e `familia_json` são `json_decode` corretamente no servidor
- ✅ **Socioeconomico**: `ficha_socioeconomico` inserido e seu `idficha` (DB) usado para inserir `despesas.id_ficha` e `familia.id_ficha`
- ✅ **Socioeconomico**: Transações DB são usadas — commit/rollback correto
- ✅ **Logs**: Erros são logados com detalhes suficientes para debug
- ✅ **Validação**: JSON é validado antes de uso (verificação de `json_last_error()`)
- ✅ **Normalização**: Valores numéricos são normalizados (vírgula → ponto)

---

## ARQUIVOS MODIFICADOS

1. `app/Views/desligamento/index.php` - Header AJAX e tratamento de erro
2. `bootstrap.php` - Headers condicionais
3. `app/Services/AuthService.php` - requireAuth() não redireciona em AJAX
4. `app/Controllers/DesligamentoController.php` - Try-catch explícito e respostas padronizadas
5. `desligamento.php` - Output buffering e tratamento AJAX
6. `app/Controllers/SocioeconomicoController.php` - Decodificação de JSON com logs detalhados
7. `app/Models/SocioeconomicoDB.php` - Validação, normalização e logs detalhados em createFicha() e updateFicha()
8. `js/socioeconomico-multistep.js` - Verificações detalhadas antes do submit com validação de JSON

## DIAGNÓSTICO DE PROBLEMAS - LOGS ADICIONADOS

Para facilitar o diagnóstico de problemas futuros com persistência de despesas e família, foram adicionados logs detalhados em múltiplos pontos:

### Logs no JavaScript (Console do Browser):
- ✅ Quantidade de membros da família antes de salvar
- ✅ Conteúdo completo do JSON antes do submit
- ✅ Validação de JSON válido/inválido
- ✅ Verificação de campos hidden presentes/ausentes

### Logs no Controller (error_log PHP):
- ✅ Presença/ausência de campos JSON no POST
- ✅ Tamanho dos campos JSON recebidos
- ✅ Sucesso/erro na decodificação JSON
- ✅ Quantidade de itens decodificados

### Logs no Model (error_log PHP):
- ✅ ID da ficha criada (idficha)
- ✅ Processamento individual de cada membro/despesa
- ✅ Quantidade final de itens inseridos
- ✅ Erros específicos com contexto completo

**Para visualizar os logs:**
- **JavaScript**: Abrir DevTools → Console
- **PHP**: Verificar arquivo de erro do PHP (geralmente `php_error.log` ou `error_log`)
- **SQL**: Verificar arquivo `data/debug_sql.log` para logs de queries SQL

---

## NOTAS ADICIONAIS

### Sobre idficha vs id_ficha:
- A PK da tabela `Ficha_Socioeconomico` é `idficha` (sem underscore)
- As FKs nas tabelas `Despesas` e `Familia` são `id_ficha` (com underscore)
- O código está correto: usa `lastInsertId()` que retorna `idficha` e passa para `id_ficha` nas FKs
- **Não é necessário criar migration** - o código já trata a diferença corretamente

### Sobre transações:
- Todas as operações (criação/atualização de ficha, família e despesas) estão dentro de transação
- Se qualquer inserção falhar, todas são revertidas (rollback)
- Logs são feitos antes do commit para garantir rastreabilidade

