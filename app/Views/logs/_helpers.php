<?php

if (!function_exists('cfLogEsc')) {
    function cfLogEsc($value) {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('cfLogActionMeta')) {
    function cfLogActionMeta($action) {
        $action = strtoupper((string) $action);
        $map = [
            'INSERT' => ['class' => 'insert', 'label' => 'Criar', 'icon' => 'fa-plus'],
            'UPDATE' => ['class' => 'update', 'label' => 'Editar', 'icon' => 'fa-pen'],
            'DELETE' => ['class' => 'delete', 'label' => 'Deletar', 'icon' => 'fa-trash']
        ];

        return $map[$action] ?? ['class' => 'neutral', 'label' => $action ?: 'Sistema', 'icon' => 'fa-circle-info'];
    }
}

if (!function_exists('cfLogActionBadge')) {
    function cfLogActionBadge($action) {
        $meta = cfLogActionMeta($action);
        return '<span class="log-action-badge ' . cfLogEsc($meta['class']) . '">' .
            '<i class="fas ' . cfLogEsc($meta['icon']) . '"></i>' .
            cfLogEsc($meta['label']) .
            '</span>';
    }
}

if (!function_exists('cfLogTableLabel')) {
    function cfLogTableLabel($table) {
        $map = [
            'atendido' => 'Atendido',
            'ficha_acolhimento' => 'Ficha Acolhimento',
            'ficha_socioeconomico' => 'Ficha Socioeconômica',
            'anotacao_psicologica' => 'Anotação Psicológica',
            'frequencia_dia' => 'Frequência',
            'desligamento' => 'Desligamento',
            'usuario' => 'Usuário'
        ];

        return $map[$table] ?? $table;
    }
}

if (!function_exists('cfLogMasked')) {
    function cfLogMasked($value, $field = '') {
        return LogHelper::maskSensitiveValue($value, $field);
    }
}

if (!function_exists('cfLogExcerpt')) {
    function cfLogExcerpt($value, $field = '', $length = 90) {
        $text = trim((string) cfLogMasked($value, $field));

        if ($text === '') {
            return '-';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($text, 'UTF-8') > $length
                ? mb_substr($text, 0, $length, 'UTF-8') . '...'
                : $text;
        }

        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }
}

if (!function_exists('cfLogUsersById')) {
    function cfLogUsersById($usuarios) {
        $indexed = [];

        foreach (($usuarios ?? []) as $user) {
            if (isset($user['idusuario'])) {
                $indexed[$user['idusuario']] = $user;
            }
        }

        return $indexed;
    }
}

if (!function_exists('cfLogUserName')) {
    function cfLogUserName($log, $usersById) {
        $userId = $log['id_usuario'] ?? null;

        if (!$userId) {
            return 'Sistema';
        }

        return $usersById[$userId]['nome'] ?? 'Sistema';
    }
}

if (!function_exists('cfLogDate')) {
    function cfLogDate($value) {
        if (empty($value)) {
            return '-';
        }

        return date('d/m/Y H:i:s', strtotime($value));
    }
}

if (!function_exists('cfLogUrl')) {
    function cfLogUrl(array $params) {
        return 'logs.php' . ($params ? '?' . http_build_query($params) : '');
    }
}

if (!function_exists('cfLogFilterParams')) {
    function cfLogFilterParams($filters) {
        $params = ['action' => 'search'];

        foreach (['tabela', 'acao', 'usuario_id', 'data_inicio', 'data_fim', 'busca'] as $key) {
            if (!empty($filters[$key])) {
                $params[$key] = $filters[$key];
            }
        }

        return $params;
    }
}

if (!function_exists('cfLogPaginationWindow')) {
    function cfLogPaginationWindow($currentPage, $lastPage) {
        $currentPage = max(1, (int) $currentPage);
        $lastPage = max(1, (int) $lastPage);

        return [
            'start' => max(1, $currentPage - 2),
            'end' => min($lastPage, $currentPage + 2)
        ];
    }
}
