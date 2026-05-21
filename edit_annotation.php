<?php
/**
 * API para buscar dados de uma anotação (usada no modal de edição)
 * Retorna JSON com os dados da anotação para edição
 */

// Carregar bootstrap MVC
require_once 'bootstrap.php';

// Verificar autenticação
try {
    $authService = new AuthService();
    $authService->requireAuth();
    $authService->requirePermission('view_psychological_area');
} catch (Exception $e) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit;
}

// Obter ID da anotação
$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID da anotação é obrigatório']);
    exit;
}

try {
    // Obter dados da anotação
    $psychologyService = new PsychologyService();
    $note = $psychologyService->getAnnotationById($id);

    if (!$note) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Anotação não encontrada']);
        exit;
    }

    // Retornar dados formatados
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'note' => [
            'id' => $note['id'] ?? $note['id_anotacao'],
            'title' => $note['title'] ?? $note['titulo'] ?? '',
            'content' => $note['content'] ?? $note['conteudo'] ?? '',
            'note_type' => $note['note_type'] ?? strtolower($note['tipo'] ?? 'consulta'),
            'mood_assessment' => $note['mood_assessment'] ?? $note['humor'] ?? null,
            'behavior_notes' => $note['behavior_notes'] ?? $note['observacoes_comportamentais'] ?? '',
            'recommendations' => $note['recommendations'] ?? $note['recomendacoes'] ?? '',
            'next_session' => $note['next_session'] ?? $note['proxima_sessao'] ?? null
        ]
    ]);
    exit;

} catch (Exception $e) {
    error_log("Erro em edit_annotation.php: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
