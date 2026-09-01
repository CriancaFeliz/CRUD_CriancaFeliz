<?php

/**
 * Model para fichas socioeconômicas - MYSQL COMPLETO
 */
class SocioeconomicoDB extends BaseModelDB {
    
    public function __construct() {
        parent::__construct('Atendido', 'idatendido');
    }
    
    /**
     * Converter data dd/mm/yyyy para yyyy-mm-dd
     */
    private function convertDate($date) {
        if (empty($date)) return null;
        
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }
        
        return $date;
    }
    
    /**
     * Formatar data yyyy-mm-dd para dd/mm/yyyy
     */
    private function formatDate($date) {
        if (empty($date)) return '';
        
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            return "{$matches[3]}/{$matches[2]}/{$matches[1]}";
        }
        
        return $date;
    }
    
    /**
     * Normalizar dados
     */
    private function normalizeData($data) {
        // Normalizar CPF
        if (isset($data['cpf'])) {
            $data['cpf'] = preg_replace('/\D+/', '', $data['cpf']);
        }
        
        // Normalizar RG
        if (isset($data['rg'])) {
            $data['rg'] = preg_replace('/\D+/', '', $data['rg']);
        }
        
        // Normalizar CEP
        if (isset($data['cep'])) {
            $data['cep'] = preg_replace('/\D+/', '', $data['cep']);
        }
        
        // Normalizar telefones
        $telefoneFields = ['telefone', 'celular', 'contato_emergencia'];
        foreach ($telefoneFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = preg_replace('/\D+/', '', $data[$field]);
            }
        }
        
        return $data;
    }
    
    /**
     * Calcular idade
     */
    public function calculateAge($dataNascimento) {
        if (empty($dataNascimento)) return 0;
        
        $date = $this->convertDate($dataNascimento);
        $birthDate = new DateTime($date);
        $today = new DateTime();
        return $birthDate->diff($today)->y;
    }
    
    /**
     * Categorizar por idade
     */
    public function categorizeByAge($age) {
        if ($age < 12) return 'Criança';
        if ($age < 18) return 'Adolescente';
        return 'Adulto';
    }
    
    /**
     * Criar ficha socioeconômica
     */
    public function createFicha($data) {
        try {
            Database::beginTransaction();
            
            // Normalizar dados
            $data = $this->normalizeData($data);
            
            // 1. Criar Atendido (se não existir)
            $atendidoData = [
                'nome' => $data['nome_entrevistado'] ?? $data['nome_completo'] ?? '',
                'cpf' => $data['cpf'] ?? '',
                'rg' => $data['rg'] ?? '',
                'data_nascimento' => $this->convertDate($data['data_nascimento'] ?? ''),
                'data_acolhimento' => $this->convertDate($data['data_acolhimento'] ?? ''),
                'data_cadastro' => date('Y-m-d'),
                'endereco' => $data['endereco'] ?? null,
                'numero' => $data['numero'] ?? null,
                'complemento' => $data['complemento'] ?? null,
                'bairro' => $data['bairro'] ?? null,
                'cidade' => $data['cidade'] ?? null,
                'cep' => $data['cep'] ?? null,
                'status' => 'Ativo',
                'faixa_etaria' => $this->calculateAge($data['data_nascimento'] ?? '')
            ];
            
            $atendido = $this->create($atendidoData);
            $atendidoId = $atendido['idatendido'];
            
            // 2. Criar Ficha Socioeconômica
            // Converter renda_familiar para número (remover R$, pontos de milhar, converter vírgula em ponto)
            $rendaFamiliar = 0;
            if (!empty($data['renda_familiar'])) {
                $renda = $data['renda_familiar'];
                // Remover R$ e espaços
                $renda = str_replace(['R$', ' '], '', $renda);
                // Se tiver vírgula, é formato brasileiro (1.800,00)
                if (strpos($renda, ',') !== false) {
                    // Remover pontos de milhar e converter vírgula em ponto
                    $renda = str_replace('.', '', $renda);
                    $renda = str_replace(',', '.', $renda);
                }
                // Agora converter para float
                $rendaFamiliar = floatval($renda);
                error_log("DEBUG: renda_familiar original: " . $data['renda_familiar'] . " → convertido: " . $rendaFamiliar);
            }
            
            // Garantir que colunas de benefícios existam na tabela (compatibilidade)
            try {
                $colsStmt = $this->query("SHOW COLUMNS FROM Ficha_Socioeconomico");
                $colsArr = array_column($colsStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
            } catch (Exception $e) {
                $colsArr = [];
            }

            $benefitCols = [
                'bolsa_familia' => "TINYINT(1) DEFAULT 0",
                'auxilio_brasil' => "TINYINT(1) DEFAULT 0",
                'bpc' => "TINYINT(1) DEFAULT 0",
                'auxilio_emergencial' => "TINYINT(1) DEFAULT 0",
                'seguro_desemprego' => "TINYINT(1) DEFAULT 0",
                'aposentadoria' => "TINYINT(1) DEFAULT 0"
            ];

            foreach ($benefitCols as $col => $ddl) {
                if (!in_array($col, $colsArr)) {
                    try {
                        $this->query("ALTER TABLE Ficha_Socioeconomico ADD COLUMN {$col} {$ddl}");
                        error_log("Coluna adicionada: {$col}");
                    } catch (Exception $e) {
                        error_log("Falha ao adicionar coluna {$col}: " . $e->getMessage());
                    }
                }
            }

            // Determinar flags de benefícios a partir dos campos disponíveis
            $bolsa = (!empty($data['bolsa_familia'])) ? 1 : 0;
            $auxilio = (!empty($data['auxilio_brasil'])) ? 1 : 0;
            $bpc = (!empty($data['bpc'])) ? 1 : 0;
            $auxEmerg = (!empty($data['auxilio_emergencial'])) ? 1 : 0;
            $seguro = (!empty($data['seguro_desemprego'])) ? 1 : 0;
            $aposentadoria = (!empty($data['aposentadoria'])) ? 1 : 0;

            if (!$bolsa && !empty($data['renda_bolsa'])) {
                $val = floatval(str_replace([',','R$','.'],['','.',''],$data['renda_bolsa']));
                if ($val > 0) $bolsa = 1;
            }

            // Preparar dados da ficha para inserção (mapa coluna => valor)
            $fichaData = [
                'id_atendido' => $atendidoId,
                'agua' => isset($data['agua']) ? 1 : 0,
                'esgoto' => isset($data['esgoto']) ? 1 : 0,
                'energia' => isset($data['energia']) ? 1 : 0,
                'renda_familiar' => $rendaFamiliar,
                'qtd_pessoas' => $data['pessoas_casa'] ?? $data['qtd_pessoas'] ?? 0,
                'cond_residencia' => $data['situacao_moradia'] ?? $data['cond_residencia'] ?? null,
                'moradia' => $data['tipo_moradia'] ?? $data['moradia'] ?? null,
                'nr_veiculos' => $data['nr_veiculos'] ?? 0,
                'observacoes' => $data['observacoes'] ?? null,
                'entrevistado' => $data['nome_entrevistado'] ?? $data['nome_completo'] ?? '',
                'residencia' => $data['residencia'] ?? null,
                'nr_comodos' => $data['numero_comodos'] ?? $data['nr_comodos'] ?? 0,
                'construcao' => $data['construcao'] ?? null,
                'nome_menor' => $data['nome_menor'] ?? null,
                'assistente_social' => $data['assistente_social'] ?? null,
                'cadunico' => $data['cadunico'] ?? null,
                'renda_per_capita' => isset($data['renda_per_capita']) ? floatval($data['renda_per_capita']) : ( ($data['pessoas_casa'] ?? $data['qtd_pessoas'] ?? 0) ? ($rendaFamiliar / max(1, intval($data['pessoas_casa'] ?? $data['qtd_pessoas'] ?? 0))) : null ),
                'bolsa_familia' => $bolsa,
                'auxilio_brasil' => $auxilio,
                'bpc' => $bpc,
                'auxilio_emergencial' => $auxEmerg,
                'seguro_desemprego' => $seguro,
                'aposentadoria' => $aposentadoria
            ];

            // Filtrar apenas colunas que existem na tabela (para evitar erro de coluna desconhecida)
            // $colsArr já foi obtido acima (SHOW COLUMNS)
            $insertCols = [];
            $insertVals = [];
            foreach ($fichaData as $col => $val) {
                if (in_array($col, $colsArr)) {
                    $insertCols[] = $col;
                    // converter booleanos/flags para 0/1
                    if (is_bool($val)) $val = $val ? 1 : 0;
                    $insertVals[] = $val;
                }
            }

            if (empty($insertCols)) {
                throw new Exception('Nenhuma coluna válida encontrada em Ficha_Socioeconomico para inserir. Verifique o schema.');
            }

            $placeholders = implode(', ', array_fill(0, count($insertCols), '?'));
            $colsList = implode(', ', $insertCols);
            $sql = "INSERT INTO Ficha_Socioeconomico ({$colsList}) VALUES ({$placeholders})";

            try {
                // Log detalhado para debug (arquivo em DATA_PATH)
                $debugFile = defined('DATA_PATH') ? DATA_PATH . '/debug_sql.log' : __DIR__ . '/../../data/debug_sql.log';
                $logEntry = [
                    'time' => date('c'),
                    'action' => 'insert_ficha_socioeconomico',
                    'sql' => $sql,
                    'params' => $insertVals
                ];
                @file_put_contents($debugFile, json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);

                $this->query($sql, $insertVals);
                $fichaId = (int)Database::lastInsertId();
                error_log('Ficha criada com idficha: ' . $fichaId);
                @file_put_contents($debugFile, json_encode(['time'=>date('c'),'result'=>'ok','idficha'=>$fichaId]) . PHP_EOL, FILE_APPEND);
            } catch (Exception $e) {
                error_log('ERRO ao inserir Ficha_Socioeconomico: ' . $e->getMessage());
                @file_put_contents($debugFile, json_encode(['time'=>date('c'),'error'=>$e->getMessage()]) . PHP_EOL, FILE_APPEND);
                throw $e;
            }
            
            // 3. Salvar Família (se houver)
            // Aceitar tanto familia_json quanto familia array
            error_log('=== INICIANDO SALVAMENTO DE FAMÍLIA ===');
            error_log('familia_json presente: ' . (isset($data['familia_json']) ? 'SIM' : 'NÃO'));
            error_log('familia array presente: ' . (isset($data['familia']) && is_array($data['familia']) ? 'SIM (' . count($data['familia']) . ' itens)' : 'NÃO'));
            
            $familia = [];
            if (!empty($data['familia_json'])) {
                error_log('Decodificando familia_json...');
                $familia = json_decode($data['familia_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($familia)) {
                    error_log('ERRO ao decodificar familia_json: ' . json_last_error_msg());
                    error_log('Conteúdo familia_json: ' . substr($data['familia_json'], 0, 200));
                    $familia = [];
                } else {
                    error_log('familia_json decodificado com sucesso: ' . count($familia) . ' membros');
                }
            } elseif (!empty($data['familia']) && is_array($data['familia'])) {
                error_log('Usando array familia diretamente: ' . count($data['familia']) . ' membros');
                $familia = $data['familia'];
            } else {
                error_log('NENHUM dado de família encontrado (nem familia_json nem familia array)');
            }
            
            if (!empty($familia) && is_array($familia)) {
                error_log('Inserindo ' . count($familia) . ' membros da família na tabela Familia com id_ficha = ' . $fichaId);
                $familiaInseridos = 0;
                foreach ($familia as $idx => $membro) {
                    error_log("Processando membro {$idx}: " . print_r($membro, true));
                    
                    // Validar membro antes de inserir
                    if (empty($membro['nome']) || empty($membro['parentesco'])) {
                        error_log("Membro da família #{$idx} ignorado - falta nome ou parentesco");
                        continue;
                    }
                    
                    $renda = 0;
                    if (!empty($membro['renda'])) {
                        // Converter renda para float
                        $renda = is_numeric($membro['renda']) ? floatval($membro['renda']) : 0;
                    }
                    
                    try {
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
                        error_log("Membro #{$idx} inserido com sucesso");
                    } catch (Exception $e) {
                        error_log("ERRO ao inserir membro #{$idx}: " . $e->getMessage());
                        throw $e;
                    }
                }
                error_log("✅ Família: {$familiaInseridos} membros inseridos com sucesso");
            } else {
                error_log('⚠️ Nenhum membro da família para inserir (array vazio ou inválido)');
            }
            
            // 4. Salvar Despesas (se houver)
            // Aceitar tanto despesas_json quanto despesas array
            error_log('=== INICIANDO SALVAMENTO DE DESPESAS ===');
            error_log('despesas_json presente: ' . (isset($data['despesas_json']) ? 'SIM' : 'NÃO'));
            error_log('despesas array presente: ' . (isset($data['despesas']) && is_array($data['despesas']) ? 'SIM (' . count($data['despesas']) . ' itens)' : 'NÃO'));
            
            $despesas = [];
            if (!empty($data['despesas_json'])) {
                error_log('Decodificando despesas_json...');
                $despesas = json_decode($data['despesas_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($despesas)) {
                    error_log('ERRO ao decodificar despesas_json: ' . json_last_error_msg());
                    error_log('Conteúdo despesas_json: ' . substr($data['despesas_json'], 0, 200));
                    $despesas = [];
                } else {
                    error_log('despesas_json decodificado com sucesso: ' . count($despesas) . ' itens');
                }
            } elseif (!empty($data['despesas']) && is_array($data['despesas'])) {
                error_log('Usando array despesas diretamente: ' . count($data['despesas']) . ' itens');
                $despesas = $data['despesas'];
            } else {
                error_log('NENHUM dado de despesas encontrado (nem despesas_json nem despesas array)');
            }
            
            if (!empty($despesas) && is_array($despesas)) {
                error_log('Inserindo ' . count($despesas) . ' despesas na tabela Despesas com id_ficha = ' . $fichaId);
                $despesasInseridas = 0;
                foreach ($despesas as $idx => $despesa) {
                    error_log("Processando despesa {$idx}: " . print_r($despesa, true));
                    
                    // Normalizar valor
                    $valor = 0;
                    if (!empty($despesa['valor'])) {
                        $valorStr = is_string($despesa['valor']) ? str_replace(',', '.', $despesa['valor']) : $despesa['valor'];
                        $valor = floatval($valorStr);
                    } elseif (!empty($despesa['valor_despesa'])) {
                        $valorStr = is_string($despesa['valor_despesa']) ? str_replace(',', '.', $despesa['valor_despesa']) : $despesa['valor_despesa'];
                        $valor = floatval($valorStr);
                    }
                    
                    // Normalizar tipo/nome
                    $tipo = trim($despesa['tipo'] ?? $despesa['tipo_renda'] ?? $despesa['nome'] ?? '');
                    
                    // Normalizar renda
                    $renda = 0;
                    if (!empty($despesa['renda'])) {
                        $rendaStr = is_string($despesa['renda']) ? str_replace(',', '.', $despesa['renda']) : $despesa['renda'];
                        $renda = floatval($rendaStr);
                    } elseif (!empty($despesa['valor_renda'])) {
                        $rendaStr = is_string($despesa['valor_renda']) ? str_replace(',', '.', $despesa['valor_renda']) : $despesa['valor_renda'];
                        $renda = floatval($rendaStr);
                    }
                    
                    // Inserir se tiver pelo menos valor ou tipo
                    if ($valor > 0 || !empty($tipo)) {
                        try {
                            $this->query(
                                "INSERT INTO Despesas (id_ficha, valor_despesa, tipo_renda, valor_renda) VALUES (?, ?, ?, ?)",
                                [$fichaId, $valor, $tipo, $renda] // id_ficha (FK) recebe idficha (PK)
                            );
                            $despesasInseridas++;
                            error_log("Despesa #{$idx} inserida com sucesso: {$tipo} = R$ {$valor}");
                        } catch (Exception $e) {
                            error_log("ERRO ao inserir despesa #{$idx}: " . $e->getMessage());
                            throw $e;
                        }
                    } else {
                        error_log("Despesa #{$idx} ignorada (sem valor e sem tipo)");
                    }
                }
                error_log("✅ Despesas: {$despesasInseridas} itens inseridos com sucesso");
            } else {
                error_log('⚠️ Nenhuma despesa para inserir (array vazio ou inválido)');
            }
            
            Database::commit();
            
            return $this->getFicha($atendidoId);
            
        } catch (Exception $e) {
            Database::rollback();
            error_log('Erro ao criar ficha socioeconômica: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Buscar ficha completa
     */
    public function getFicha($id) {
        $stmt = $this->query("
            SELECT 
                a.*,
                f.*
            FROM Atendido a
            LEFT JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
            WHERE a.idatendido = ?
        ", [$id]);
        
        $ficha = $stmt->fetch();
        
        if ($ficha) {
            // Mapear campos
            $ficha['id'] = $ficha['idatendido'];
            $ficha['nome_entrevistado'] = $ficha['nome'];
            $ficha['nome_completo'] = $ficha['nome'];
            $ficha['data_nascimento'] = $this->formatDate($ficha['data_nascimento']);
            $ficha['idade'] = $this->calculateAge($ficha['data_nascimento']);
            $ficha['categoria'] = $this->categorizeByAge($ficha['idade']);
            
            // Usar idficha corretamente (pode ser idficha ou id_ficha dependendo do schema)
            $fichaId = $ficha['idficha'] ?? $ficha['id_ficha'] ?? null;
            
            // Buscar família se ficha existe
            if ($fichaId) {
                $stmt = $this->query("SELECT * FROM Familia WHERE id_ficha = ?", [$fichaId]);
                $ficha['familia'] = $stmt->fetchAll();
                
                // Buscar despesas
                $stmt = $this->query("SELECT * FROM Despesas WHERE id_ficha = ?", [$fichaId]);
                $ficha['despesas'] = $stmt->fetchAll();
            } else {
                $ficha['familia'] = [];
                $ficha['despesas'] = [];
            }
            
            // Garantir campos numéricos
            $ficha['renda_familiar'] = floatval($ficha['renda_familiar'] ?? 0);
            $ficha['qtd_pessoas'] = intval($ficha['qtd_pessoas'] ?? 0);
            $ficha['numero_membros'] = $ficha['qtd_pessoas'];
            $ficha['nr_comodos'] = intval($ficha['nr_comodos'] ?? $ficha['numero_comodos'] ?? 0);
            $ficha['numero_comodos'] = $ficha['nr_comodos'];
            $ficha['nr_veiculos'] = intval($ficha['nr_veiculos'] ?? 0);

            // Mapear nomes compatíveis com as views
            $ficha['tipo_moradia'] = $ficha['tipo_moradia'] ?? $ficha['moradia'] ?? null;
            $ficha['situacao_moradia'] = $ficha['situacao_moradia'] ?? $ficha['cond_residencia'] ?? null;
            $ficha['nome_menor'] = $ficha['nome_menor'] ?? null;
            $ficha['assistente_social'] = $ficha['assistente_social'] ?? null;
            $ficha['cadunico'] = $ficha['cadunico'] ?? null;

            // Calcular renda per capita se não estiver presente
            if (empty($ficha['renda_per_capita'])) {
                $ficha['renda_per_capita'] = ($ficha['qtd_pessoas'] > 0) ? ($ficha['renda_familiar'] / max(1, $ficha['qtd_pessoas'])) : 0;
            }

            // Preparar mapa de despesas por tipo (facilita exibição de Agua/Energia)
            $despesasMap = [];
            if (!empty($ficha['despesas']) && is_array($ficha['despesas'])) {
                foreach ($ficha['despesas'] as $d) {
                    $tipo = mb_strtolower(trim($d['tipo'] ?? ($d['tipo_renda'] ?? '')));
                    $valor = floatval($d['valor_despesa'] ?? $d['valor'] ?? $d['valor_renda'] ?? 0);
                    if (!isset($despesasMap[$tipo])) $despesasMap[$tipo] = 0;
                    $despesasMap[$tipo] += $valor;
                }
            }
            $ficha['despesas_map'] = $despesasMap;
            // Expor despesas específicas de interesse
            $ficha['despesa_agua'] = $despesasMap['agua'] ?? ($despesasMap['água'] ?? null);
            $ficha['despesa_energia'] = $despesasMap['energia'] ?? null;
        }
        
        return $ficha;
    }
    
    /**
     * Listar todas as fichas
     */
    public function listFichas($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countStmt = $this->query("
            SELECT COUNT(*) as total 
            FROM Atendido a
            INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
        ");
        $countResult = $countStmt->fetch();
        
        // Try to select with all benefit columns, fallback gracefully if columns don't exist
        try {
            $stmt = $this->query("
                SELECT 
                    a.idatendido as id,
                    a.idatendido,
                    a.nome,
                    a.nome as nome_entrevistado,
                    a.nome as nome_completo,
                    a.cpf,
                    COALESCE(a.data_acolhimento, a.data_cadastro) as data_acolhimento,
                    a.data_nascimento,
                    a.status,
                    COALESCE(f.renda_familiar, 0) as renda_familiar,
                    COALESCE(f.qtd_pessoas, 0) as qtd_pessoas,
                    COALESCE(f.numero_comodos, f.nr_comodos, 0) as numero_comodos,
                    COALESCE(f.nome_menor, '') as nome_menor,
                    COALESCE(f.bolsa_familia, 0) as bolsa_familia,
                    COALESCE(f.auxilio_brasil, 0) as auxilio_brasil,
                    COALESCE(f.bpc, 0) as bpc,
                    COALESCE(f.auxilio_emergencial, 0) as auxilio_emergencial,
                    COALESCE(f.seguro_desemprego, 0) as seguro_desemprego,
                    COALESCE(f.aposentadoria, 0) as aposentadoria
                FROM Atendido a
                INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
                ORDER BY a.data_cadastro DESC
                LIMIT ? OFFSET ?
            ", [$perPage, $offset]);
        } catch (Exception $e) {
            // Fallback: Select only columns that definitely exist
            error_log("Error with benefit columns in listFichas, using fallback: " . $e->getMessage());
            $stmt = $this->query("
                SELECT 
                    a.idatendido as id,
                    a.idatendido,
                    a.nome,
                    a.nome as nome_entrevistado,
                    a.nome as nome_completo,
                    a.cpf,
                    COALESCE(a.data_acolhimento, a.data_cadastro) as data_acolhimento,
                    a.data_nascimento,
                    a.status,
                    f.renda_familiar,
                    f.qtd_pessoas
                FROM Atendido a
                INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
                ORDER BY a.data_cadastro DESC
                LIMIT ? OFFSET ?
            ", [$perPage, $offset]);
        }
        
        $fichas = $stmt->fetchAll();
        
        // Formatar datas e adicionar dados calculados
        foreach ($fichas as &$ficha) {
            $ficha['data_nascimento'] = $this->formatDate($ficha['data_nascimento']);
            $ficha['data_acolhimento'] = $this->formatDate($ficha['data_acolhimento'] ?? '');
            $ficha['idade'] = $this->calculateAge($ficha['data_nascimento']);
            $ficha['categoria'] = $this->categorizeByAge($ficha['idade']);

            // Construir lista de benefícios a partir das flags
            $beneficios = [];
            if (!empty($ficha['bolsa_familia'])) $beneficios[] = 'Bolsa Família';
            if (!empty($ficha['auxilio_brasil'])) $beneficios[] = 'Auxílio Brasil';
            if (!empty($ficha['bpc'])) $beneficios[] = 'BPC';
            if (!empty($ficha['auxilio_emergencial'])) $beneficios[] = 'Auxílio Emergencial';
            if (!empty($ficha['seguro_desemprego'])) $beneficios[] = 'Seguro Desemprego';
            if (!empty($ficha['aposentadoria'])) $beneficios[] = 'Aposentadoria';
            $ficha['beneficios_list'] = $beneficios;
            
            // Garantir que nome_completo exista (compatibilidade com view)
            if (empty($ficha['nome_completo'])) {
                $ficha['nome_completo'] = $ficha['nome_entrevistado'];
            }
        }
        
        // Contar total
        $stmt = $this->query("
            SELECT COUNT(*) as total 
            FROM Atendido a
            INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
        ");
        $result = $stmt->fetch();
        $total = $result['total'];
        
        return [
            'data' => $fichas,
            'total' => $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage,
            // Compatibilidade
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Atualizar ficha
     */
    public function updateFicha($id, $data) {
        try {
            Database::beginTransaction();
            
            // Normalizar dados
            $data = $this->normalizeData($data);
            
            // 1. Atualizar Atendido
            $atendidoData = [
                'nome' => $data['nome_entrevistado'] ?? $data['nome_completo'] ?? '',
                'cpf' => $data['cpf'] ?? '',
                'rg' => $data['rg'] ?? '',
                'data_nascimento' => $this->convertDate($data['data_nascimento'] ?? '')
            ];
            
            $this->update($id, $atendidoData);
            
            // 2. Atualizar Ficha Socioeconômica
            // Converter renda_familiar para número (remover R$, pontos de milhar, converter vírgula em ponto)
            $rendaFamiliar = 0;
            if (!empty($data['renda_familiar'])) {
                $renda = $data['renda_familiar'];
                // Remover R$ e espaços
                $renda = str_replace(['R$', ' '], '', $renda);
                // Se tiver vírgula, é formato brasileiro (1.800,00)
                if (strpos($renda, ',') !== false) {
                    // Remover pontos de milhar e converter vírgula em ponto
                    $renda = str_replace('.', '', $renda);
                    $renda = str_replace(',', '.', $renda);
                }
                // Agora converter para float
                $rendaFamiliar = floatval($renda);
                error_log("DEBUG UPDATE: renda_familiar original: " . $data['renda_familiar'] . " → convertido: " . $rendaFamiliar);
            }
            
            // Garantir colunas de benefícios (caso não existam ainda)
            try {
                $colsStmt = $this->query("SHOW COLUMNS FROM Ficha_Socioeconomico");
                $colsArr = array_column($colsStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
            } catch (Exception $e) {
                $colsArr = [];
            }

            $benefitCols = [
                'bolsa_familia' => "TINYINT(1) DEFAULT 0",
                'auxilio_brasil' => "TINYINT(1) DEFAULT 0",
                'bpc' => "TINYINT(1) DEFAULT 0",
                'auxilio_emergencial' => "TINYINT(1) DEFAULT 0",
                'seguro_desemprego' => "TINYINT(1) DEFAULT 0",
                'aposentadoria' => "TINYINT(1) DEFAULT 0"
            ];

            foreach ($benefitCols as $col => $ddl) {
                if (!in_array($col, $colsArr)) {
                    try {
                        $this->query("ALTER TABLE Ficha_Socioeconomico ADD COLUMN {$col} {$ddl}");
                        error_log("Coluna adicionada (update): {$col}");
                    } catch (Exception $e) {
                        error_log("Falha ao adicionar coluna {$col} no update: " . $e->getMessage());
                    }
                }
            }

            $bolsa = (!empty($data['bolsa_familia'])) ? 1 : 0;
            $auxilio = (!empty($data['auxilio_brasil'])) ? 1 : 0;
            $bpc = (!empty($data['bpc'])) ? 1 : 0;
            $auxEmerg = (!empty($data['auxilio_emergencial'])) ? 1 : 0;
            $seguro = (!empty($data['seguro_desemprego'])) ? 1 : 0;
            $aposentadoria = (!empty($data['aposentadoria'])) ? 1 : 0;
            if (!$bolsa && !empty($data['renda_bolsa'])) {
                $val = floatval(str_replace([',','R$','.'],['','.',''],$data['renda_bolsa']));
                if ($val > 0) $bolsa = 1;
            }

            // Preparar dados para UPDATE (mapa coluna => valor)
            $updateData = [
                'agua' => isset($data['agua']) ? 1 : 0,
                'esgoto' => isset($data['esgoto']) ? 1 : 0,
                'energia' => isset($data['energia']) ? 1 : 0,
                'renda_familiar' => $rendaFamiliar,
                'qtd_pessoas' => $data['pessoas_casa'] ?? $data['qtd_pessoas'] ?? 0,
                'cond_residencia' => $data['situacao_moradia'] ?? $data['cond_residencia'] ?? null,
                'moradia' => $data['tipo_moradia'] ?? $data['moradia'] ?? null,
                'nr_veiculos' => $data['nr_veiculos'] ?? 0,
                'observacoes' => $data['observacoes'] ?? null,
                'nr_comodos' => $data['numero_comodos'] ?? $data['nr_comodos'] ?? 0,
                'construcao' => $data['construcao'] ?? null,
                'nome_menor' => $data['nome_menor'] ?? null,
                'assistente_social' => $data['assistente_social'] ?? null,
                'cadunico' => $data['cadunico'] ?? null,
                'renda_per_capita' => isset($data['renda_per_capita']) ? floatval($data['renda_per_capita']) : ( ($data['pessoas_casa'] ?? $data['qtd_pessoas'] ?? 0) ? ($rendaFamiliar / max(1, intval($data['pessoas_casa'] ?? $data['qtd_pessoas'] ?? 0))) : null ),
                'bolsa_familia' => $bolsa,
                'auxilio_brasil' => $auxilio,
                'bpc' => $bpc,
                'auxilio_emergencial' => $auxEmerg,
                'seguro_desemprego' => $seguro,
                'aposentadoria' => $aposentadoria
            ];

            // Garantir colunas existentes antes de atualizar
            try {
                $colsStmt = $this->query("SHOW COLUMNS FROM Ficha_Socioeconomico");
                $colsArr = array_column($colsStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
            } catch (Exception $e) {
                $colsArr = [];
            }

            $setParts = [];
            $values = [];
            foreach ($updateData as $col => $val) {
                if (in_array($col, $colsArr)) {
                    $setParts[] = "$col = ?";
                    if (is_bool($val)) $val = $val ? 1 : 0;
                    $values[] = $val;
                }
            }

            if (!empty($setParts)) {
                $sql = "UPDATE Ficha_Socioeconomico SET " . implode(', ', $setParts) . " WHERE id_atendido = ?";
                $values[] = $id;
                try {
                    // Log detalhado de UPDATE
                    $debugFile = defined('DATA_PATH') ? DATA_PATH . '/debug_sql.log' : __DIR__ . '/../../data/debug_sql.log';
                    $logEntry = [
                        'time' => date('c'),
                        'action' => 'update_ficha_socioeconomico',
                        'sql' => $sql,
                        'params' => $values
                    ];
                    @file_put_contents($debugFile, json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);

                    $this->query($sql, $values);
                    @file_put_contents($debugFile, json_encode(['time'=>date('c'),'result'=>'ok','id_atendido'=>$id]) . PHP_EOL, FILE_APPEND);
                } catch (Exception $e) {
                    error_log('ERRO ao atualizar Ficha_Socioeconomico: ' . $e->getMessage());
                    @file_put_contents($debugFile, json_encode(['time'=>date('c'),'error'=>$e->getMessage()]) . PHP_EOL, FILE_APPEND);
                    throw $e;
                }
            } else {
                error_log('Nenhuma coluna válida para atualizar em Ficha_Socioeconomico (schema possivelmente incompleto)');
            }
            
            // 3. Atualizar Família e Despesas (deletar existentes e recriar)
            // Buscar fichaId primeiro (PK é idficha)
            $fichaIdStmt = $this->query("SELECT idficha FROM Ficha_Socioeconomico WHERE id_atendido = ?", [$id]);
            $fichaExistente = $fichaIdStmt->fetch();
            $fichaId = $fichaExistente['idficha'] ?? null;
            
            if ($fichaId) {
                error_log('Atualizando família e despesas para ficha idficha: ' . $fichaId);
                
                // Deletar família e despesas existentes
                $this->query("DELETE FROM Familia WHERE id_ficha = ?", [$fichaId]);
                $this->query("DELETE FROM Despesas WHERE id_ficha = ?", [$fichaId]);
                
                // Salvar nova família (se houver)
                error_log('=== UPDATE: INICIANDO SALVAMENTO DE FAMÍLIA ===');
                error_log('familia_json presente: ' . (isset($data['familia_json']) ? 'SIM' : 'NÃO'));
                error_log('familia array presente: ' . (isset($data['familia']) && is_array($data['familia']) ? 'SIM (' . count($data['familia']) . ' itens)' : 'NÃO'));
                
                $familia = [];
                if (!empty($data['familia_json'])) {
                    error_log('Decodificando familia_json no update...');
                    $familia = json_decode($data['familia_json'], true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($familia)) {
                        error_log('ERRO ao decodificar familia_json no update: ' . json_last_error_msg());
                        error_log('Conteúdo familia_json: ' . substr($data['familia_json'], 0, 200));
                        $familia = [];
                    } else {
                        error_log('familia_json decodificado com sucesso no update: ' . count($familia) . ' membros');
                    }
                } elseif (!empty($data['familia']) && is_array($data['familia'])) {
                    error_log('Usando array familia diretamente no update: ' . count($data['familia']) . ' membros');
                    $familia = $data['familia'];
                } else {
                    error_log('NENHUM dado de família encontrado no update');
                }
                
                if (!empty($familia) && is_array($familia)) {
                    error_log('Inserindo ' . count($familia) . ' membros da família no update com id_ficha = ' . $fichaId);
                    $familiaInseridos = 0;
                    foreach ($familia as $idx => $membro) {
                        // Validar membro antes de inserir
                        if (empty($membro['nome']) || empty($membro['parentesco'])) {
                            error_log("Update - Membro da família #{$idx} ignorado - falta nome ou parentesco");
                            continue;
                        }
                        
                        $renda = 0;
                        if (!empty($membro['renda'])) {
                            $renda = is_numeric($membro['renda']) ? floatval($membro['renda']) : 0;
                        }
                        
                        try {
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
                        } catch (Exception $e) {
                            error_log("ERRO ao inserir membro #{$idx} no update: " . $e->getMessage());
                            throw $e;
                        }
                    }
                    error_log("✅ Update - Família: {$familiaInseridos} membros inseridos com sucesso");
                } else {
                    error_log('⚠️ Update - Nenhum membro da família para inserir');
                }
                
                // Salvar novas despesas (se houver)
                error_log('=== UPDATE: INICIANDO SALVAMENTO DE DESPESAS ===');
                error_log('despesas_json presente: ' . (isset($data['despesas_json']) ? 'SIM' : 'NÃO'));
                error_log('despesas array presente: ' . (isset($data['despesas']) && is_array($data['despesas']) ? 'SIM (' . count($data['despesas']) . ' itens)' : 'NÃO'));
                
                $despesas = [];
                if (!empty($data['despesas_json'])) {
                    error_log('Decodificando despesas_json no update...');
                    $despesas = json_decode($data['despesas_json'], true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($despesas)) {
                        error_log('ERRO ao decodificar despesas_json no update: ' . json_last_error_msg());
                        error_log('Conteúdo despesas_json: ' . substr($data['despesas_json'], 0, 200));
                        $despesas = [];
                    } else {
                        error_log('despesas_json decodificado com sucesso no update: ' . count($despesas) . ' itens');
                    }
                } elseif (!empty($data['despesas']) && is_array($data['despesas'])) {
                    error_log('Usando array despesas diretamente no update: ' . count($data['despesas']) . ' itens');
                    $despesas = $data['despesas'];
                } else {
                    error_log('NENHUM dado de despesas encontrado no update');
                }
                
                if (!empty($despesas) && is_array($despesas)) {
                    error_log('Inserindo ' . count($despesas) . ' despesas no update com id_ficha = ' . $fichaId);
                    $despesasInseridas = 0;
                    foreach ($despesas as $idx => $despesa) {
                        // Normalizar valor
                        $valor = 0;
                        if (!empty($despesa['valor'])) {
                            $valorStr = is_string($despesa['valor']) ? str_replace(',', '.', $despesa['valor']) : $despesa['valor'];
                            $valor = floatval($valorStr);
                        } elseif (!empty($despesa['valor_despesa'])) {
                            $valorStr = is_string($despesa['valor_despesa']) ? str_replace(',', '.', $despesa['valor_despesa']) : $despesa['valor_despesa'];
                            $valor = floatval($valorStr);
                        }
                        
                        // Normalizar tipo/nome
                        $tipo = trim($despesa['tipo'] ?? $despesa['tipo_renda'] ?? $despesa['nome'] ?? '');
                        
                        // Normalizar renda
                        $renda = 0;
                        if (!empty($despesa['renda'])) {
                            $rendaStr = is_string($despesa['renda']) ? str_replace(',', '.', $despesa['renda']) : $despesa['renda'];
                            $renda = floatval($rendaStr);
                        } elseif (!empty($despesa['valor_renda'])) {
                            $rendaStr = is_string($despesa['valor_renda']) ? str_replace(',', '.', $despesa['valor_renda']) : $despesa['valor_renda'];
                            $renda = floatval($rendaStr);
                        }
                        
                        // Inserir se tiver pelo menos valor ou tipo
                        if ($valor > 0 || !empty($tipo)) {
                            try {
                                $this->query(
                                    "INSERT INTO Despesas (id_ficha, valor_despesa, tipo_renda, valor_renda) VALUES (?, ?, ?, ?)",
                                    [$fichaId, $valor, $tipo, $renda] // id_ficha (FK) recebe idficha (PK)
                                );
                                $despesasInseridas++;
                            } catch (Exception $e) {
                                error_log("ERRO ao inserir despesa #{$idx} no update: " . $e->getMessage());
                                throw $e;
                            }
                        }
                    }
                    error_log("✅ Update - Despesas: {$despesasInseridas} itens inseridos com sucesso");
                } else {
                    error_log('⚠️ Update - Nenhuma despesa para inserir');
                }
            } else {
                error_log('ATENÇÃO: Ficha não encontrada para id_atendido: ' . $id);
            }
            
            Database::commit();
            
            return $this->getFicha($id);
            
        } catch (Exception $e) {
            Database::rollback();
            error_log('Erro ao atualizar ficha: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Deletar ficha
     */
    public function deleteFicha($id) {
        try {
            Database::beginTransaction();
            
            // Deletar (CASCADE deletará automaticamente)
            $this->delete($id);
            
            Database::commit();
            
            return true;
            
        } catch (Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
    
    /**
     * Busca por nome (compatibilidade)
     */
    public function searchByName($nome) {
        return $this->searchAdvanced($nome);
    }

    /**
     * Busca avançada
     */
    public function searchAdvanced($query) {
        $stmt = $this->query("
            SELECT 
                a.idatendido as id,
                a.idatendido,
                a.nome,
                a.nome as nome_entrevistado,
                a.nome as nome_completo,
                a.cpf,
                a.rg,
                a.data_nascimento,
                a.status,
                f.renda_familiar,
                f.qtd_pessoas as numero_membros
            FROM Atendido a
            INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
            WHERE 
                a.nome LIKE ? OR
                a.cpf LIKE ? OR
                a.rg LIKE ?
            ORDER BY a.data_cadastro DESC
            LIMIT 100
        ", ["%$query%", "%$query%", "%$query%"]);
        
        $results = $stmt->fetchAll();
        
        // Formatar datas e adicionar dados calculados
        foreach ($results as &$result) {
            $result['data_nascimento'] = $this->formatDate($result['data_nascimento']);
            $result['idade'] = $this->calculateAge($result['data_nascimento']);
            $result['categoria'] = $this->categorizeByAge($result['idade']);
        }
        
        return $results;
    }
    
    /**
     * Obter estatísticas
     */
    public function getStatistics() {
        try {
            // Total de fichas
            $stmt = $this->query("
                SELECT COUNT(*) as total 
                FROM Atendido a
                INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
            ");
            $total = $stmt->fetch()['total'];
            
            // Por categoria
            $stmt = $this->query("
                SELECT 
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) < 12 THEN 'Criança'
                        WHEN TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) < 18 THEN 'Adolescente'
                        ELSE 'Adulto'
                    END as categoria,
                    COUNT(*) as total
                FROM Atendido a
                INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
                GROUP BY categoria
            ");
            $porCategoria = $stmt->fetchAll();
            
            // Por status
            $stmt = $this->query("
                SELECT 
                    a.status,
                    COUNT(*) as total
                FROM Atendido a
                INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
                GROUP BY a.status
            ");
            $porStatus = $stmt->fetchAll();
            
            return [
                'total' => $total,
                'porCategoria' => $porCategoria,
                'porStatus' => $porStatus
            ];
            
        } catch (Exception $e) {
            error_log('Erro ao obter estatísticas: ' . $e->getMessage());
            return [
                'total' => 0,
                'porCategoria' => [],
                'porStatus' => []
            ];
        }
    }
}
