<div class="actions" style="display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px;">
    <a href="users.php?action=create" class="btn" style="background:#6fb64f; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">
        👤 Novo Usuário
    </a>
</div>

<div class="users-grid" style="display:grid; gap:16px;">
    <?php if (empty($users)): ?>
        <div class="empty-state" style="text-align:center; padding:40px; background:#fff; border-radius:12px; color:#666;">
            <div style="font-size:48px; margin-bottom:16px;">👥</div>
            <div style="font-size:18px; font-weight:600; margin-bottom:8px;">Nenhum usuário encontrado</div>
            <div>Clique em "Novo Usuário" para adicionar o primeiro usuário</div>
        </div>
    <?php else: ?>
        <div class="users-table" style="background:#fff; border-radius:12px; overflow:hidden; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8f9fa;">
                        <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Nome</th>
                        <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Email</th>
                        <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Nível de Acesso</th>
                        <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Status</th>
                        <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Criado em</th>
                        <th style="padding:16px; text-align:center; font-weight:600; border-bottom:1px solid #dee2e6; width:160px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom:1px solid #f0f0f0;" id="user-<?php echo $user['id'] ?? $user['idusuario']; ?>">
                            <td style="padding:16px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="avatar" style="width:40px; height:40px; border-radius:50%; background:#e9ecef; display:flex; align-items:center; justify-content:center; font-weight:600; color:#495057;">
                                        <?php echo strtoupper(substr($user['name'] ?? $user['nome'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:600; color:#212529;"><?php echo htmlspecialchars($user['name'] ?? $user['nome'] ?? 'Sem nome'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:16px; color:#6c757d;">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td style="padding:16px;">
                                <?php 
                                $roleColors = [
                                    'admin' => '#dc3545',
                                    'psicologo' => '#17a2b8', 
                                    'funcionario' => '#28a745'
                                ];
                                $roleNames = [
                                    'admin' => 'Administrador',
                                    'psicologo' => 'Psicólogo',
                                    'funcionario' => 'Funcionário'
                                ];
                                $role = $user['role'] ?? $user['nivel'] ?? 'user';
                                $roleColor = $roleColors[$role] ?? '#6c757d';
                                $roleName = $roleNames[$role] ?? ($user['nivel'] ?? 'Desconhecido');
                                ?>
                                <span style="background:<?php echo $roleColor; ?>; color:white; padding:4px 8px; border-radius:12px; font-size:12px; font-weight:600;">
                                    <?php echo $roleName; ?>
                                </span>
                            </td>
                            <td style="padding:16px;">
                                <?php 
                                $status = $user['status'] ?? 'Ativo';
                                $isActive = (strtolower($status) === 'ativo' || strtolower($status) === 'active');
                                ?>
                                <span class="status-badge" style="background:<?php echo $isActive ? '#28a745' : '#6c757d'; ?>; color:white; padding:4px 8px; border-radius:12px; font-size:12px; font-weight:600;">
                                    <?php echo $isActive ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </td>
                            <td style="padding:16px; color:#6c757d; font-size:14px;">
                                <?php echo isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A'; ?>
                            </td>
                            <td style="padding:16px; text-align:center;">
                                <div style="display:flex; gap:8px; justify-content:center;">
                                    <?php $userId = $user['id'] ?? $user['idusuario'] ?? ''; ?>
                                    <a href="users.php?action=edit&id=<?php echo urlencode($userId); ?>" 
                                       class="btn-action" 
                                       style="background:#f0a36b; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; text-decoration:none; font-size:12px;"
                                       title="Editar">
                                        ✏️
                                    </a>
                                    
                                    <button onclick="toggleUserStatus('<?php echo $userId; ?>')" 
                                            class="btn-action" 
                                            style="background:<?php echo $isActive ? '#ffc107' : '#28a745'; ?>; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; font-size:12px;"
                                            title="<?php echo $isActive ? 'Desativar' : 'Ativar'; ?>">
                                        <?php echo $isActive ? '⏸️' : '▶️'; ?>
                                    </button>
                                    
                                    <?php if (($currentUser['id'] ?? $currentUser['idusuario']) !== $userId): ?>
                                        <button onclick="deleteUser('<?php echo $userId; ?>', '<?php echo htmlspecialchars($user['name'] ?? $user['nome']); ?>')" 
                                                class="btn-action" 
                                                style="background:#dc3545; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; font-size:12px;"
                                                title="Excluir">
                                            🗑️
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleUserStatus(userId) {
    if (!confirm('Tem certeza que deseja alterar o status deste usuário?')) {
        return;
    }
    
    fetch('users.php?action=toggle_status&id=' + userId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao alterar status do usuário');
    });
}

function deleteUser(userId, userName) {
    if (!confirm(`Tem certeza que deseja excluir o usuário "${userName}"?\n\nEsta ação não pode ser desfeita.`)) {
        return;
    }
    
    fetch('users.php?action=delete&id=' + userId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('user-' + userId).remove();
            
            // Verificar se não há mais usuários
            const tbody = document.querySelector('tbody');
            if (tbody.children.length === 0) {
                location.reload();
            }
        } else {
            alert('Erro: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao excluir usuário');
    });
}
</script>

<style>
.btn-action:hover {
    opacity: 0.8;
    transform: translateY(-1px);
}

.users-table tr:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .users-table {
        overflow-x: auto;
    }
    
    .users-table table {
        min-width: 800px;
    }
}
</style>
