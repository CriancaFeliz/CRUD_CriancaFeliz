<div class="actions flex-between mb-4">
    <div></div>
    <a href="users.php?action=create" class="btn success">
        <i class="fas fa-user-plus"></i> Novo Usuário
    </a>
</div>

<div class="users-grid">
    <?php if (empty($users)): ?>
        <div class="empty-state card-glass text-center p-4">
            <div style="font-size: 48px; margin-bottom: 16px;"><i class="fas fa-users text-muted"></i></div>
            <div style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">Nenhum usuário encontrado</div>
            <div class="text-muted">Clique em "Novo Usuário" para adicionar o primeiro usuário</div>
        </div>
    <?php else: ?>
        <div class="table-responsive card-glass p-0">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Nível de Acesso</th>
                        <th>Status</th>
                        <th>Criado em</th>
                        <th class="actions-cell">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php $userId = (int)($user['id'] ?? $user['idusuario'] ?? 0); ?>
                        <tr id="user-<?php echo $userId; ?>">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar avatar-placeholder" style="width: 38px; height: 38px; font-size: 14px;">
                                        <?php echo e(strtoupper(substr($user['name'] ?? $user['nome'] ?? 'U', 0, 1))); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($user['name'] ?? $user['nome'] ?? 'Sem nome'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td>
                                <?php 
                                $role = $user['role'] ?? $user['nivel'] ?? 'user';
                                $roleBadgeClass = 'badge-info';
                                if ($role === 'admin') $roleBadgeClass = 'badge-danger';
                                elseif ($role === 'funcionario') $roleBadgeClass = 'badge-success';
                                elseif ($role === 'psicologo') $roleBadgeClass = 'badge-info';

                                $roleNames = [
                                    'admin' => 'Administrador',
                                    'psicologo' => 'Psicólogo',
                                    'funcionario' => 'Funcionário'
                                ];
                                $roleName = $roleNames[$role] ?? ($user['nivel'] ?? 'Desconhecido');
                                ?>
                                <span class="badge <?php echo $roleBadgeClass; ?>">
                                    <?php echo e($roleName); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $status = $user['status'] ?? 'Ativo';
                                $isActive = (strtolower($status) === 'ativo' || strtolower($status) === 'active');
                                ?>
                                <span class="status <?php echo $isActive ? 'ativo' : 'inativo'; ?>">
                                    <?php echo $isActive ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </td>
                            <td class="text-muted" style="font-size: 13px;">
                                <?php echo !empty($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A'; ?>
                            </td>
                            <td class="actions-cell">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="users.php?action=edit&amp;id=<?php echo $userId; ?>"
                                       class="btn-icon edit-btn" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <button type="button" data-action="toggle-status" data-user-id="<?php echo $userId; ?>"
                                            data-user-name="<?php echo e($user['name'] ?? $user['nome'] ?? 'Usuário'); ?>"
                                            data-user-status="<?php echo $isActive ? 'ativo' : 'inativo'; ?>"
                                            class="btn-icon toggle-btn <?php echo $isActive ? 'active' : ''; ?>"
                                            title="<?php echo $isActive ? 'Desativar usuário' : 'Ativar usuário'; ?>">
                                        <i class="fas <?php echo $isActive ? 'fa-pause' : 'fa-play'; ?>"></i>
                                    </button>
                                    
                                    <?php if ((int)($currentUser['id'] ?? $currentUser['idusuario'] ?? 0) !== $userId): ?>
                                        <button type="button" data-action="delete-user" data-user-id="<?php echo $userId; ?>" data-user-name="<?php echo e($user['name'] ?? $user['nome'] ?? 'Usuário'); ?>"
                                                class="btn-icon delete-btn"
                                                title="Excluir usuário">
                                            <i class="fas fa-trash"></i>
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

<!-- Modal Moderno de Confirmação Padronizado Criança Feliz -->
<div id="userConfirmModal" class="modal-confirm-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="userModalTitle">
    <div class="modal-confirm-dialog">
        <img src="img/logo.png" class="modal-confirm-logo" alt="Criança Feliz">
        <h3 id="userModalTitle" class="modal-confirm-title">Confirmar Exclusão</h3>
        <p id="userModalDesc" class="modal-confirm-desc">Tem certeza de que deseja excluir este usuário?</p>
        <p id="userModalSubtext" class="modal-confirm-subtext">Esta ação não poderá ser desfeita.</p>
        <div class="modal-confirm-btn-group">
            <button type="button" class="btn-confirm-cancel" onclick="closeUserModal()">Cancelar</button>
            <button type="button" id="userModalConfirmBtn" class="btn-confirm-delete">
                <i id="userModalBtnIcon" class="fas fa-trash-alt"></i> <span id="userModalBtnText">Sim, Excluir</span>
            </button>
        </div>
    </div>
</div>

<script>
const csrfToken = <?php echo json_encode($csrf_token ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
let currentUserConfirmCallback = null;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const bgColors = {
        success: isDark ? '#14532d' : '#10b981',
        danger: isDark ? '#7f1d1d' : '#ef4444',
        error: isDark ? '#7f1d1d' : '#ef4444',
        warning: isDark ? '#78350f' : '#f59e0b',
        info: isDark ? '#1e3a8a' : '#0284c7'
    };
    
    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: ${bgColors[type] || bgColors.info};
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 12px;
        font-weight: 500;
        font-size: 14px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        z-index: 999999;
        display: flex;
        align-items: center;
        gap: 10px;
        opacity: 0;
        transform: translateY(12px);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    `;
    
    const iconClass = type === 'success' ? 'fa-check-circle' : (type === 'danger' || type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    toast.innerHTML = `<i class="fas ${iconClass}"></i> <span>${escapeHtml(message)}</span>`;
    document.body.appendChild(toast);
    
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(12px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

function openUserModal(options) {
    const modal = document.getElementById('userConfirmModal');
    const titleEl = document.getElementById('userModalTitle');
    const descEl = document.getElementById('userModalDesc');
    const subtextEl = document.getElementById('userModalSubtext');
    const confirmBtn = document.getElementById('userModalConfirmBtn');
    const btnIcon = document.getElementById('userModalBtnIcon');
    const btnText = document.getElementById('userModalBtnText');

    titleEl.textContent = options.title || 'Confirmar';
    descEl.textContent = options.desc || 'Tem certeza de que deseja continuar?';
    subtextEl.textContent = options.subtext || '';
    subtextEl.style.display = options.subtext ? 'block' : 'none';

    btnIcon.className = 'fas ' + (options.btnIcon || 'fa-check');
    btnText.textContent = options.btnText || 'Confirmar';

    if (options.btnClass) {
        confirmBtn.className = options.btnClass;
    } else {
        confirmBtn.className = 'btn-confirm-delete';
    }

    currentUserConfirmCallback = options.onConfirm || null;

    modal.style.display = 'flex';
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
}

function closeUserModal() {
    const modal = document.getElementById('userConfirmModal');
    if (!modal) return;
    modal.classList.remove('active');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    currentUserConfirmCallback = null;
}

// Fechar ao clicar fora do card
document.getElementById('userConfirmModal').addEventListener('click', (e) => {
    if (e.target.id === 'userConfirmModal') {
        closeUserModal();
    }
});

// Fechar com tecla Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeUserModal();
    }
});

// Executar confirmação ao clicar no botão
document.getElementById('userModalConfirmBtn').addEventListener('click', () => {
    if (typeof currentUserConfirmCallback === 'function') {
        const callback = currentUserConfirmCallback;
        closeUserModal();
        callback();
    }
});

document.querySelectorAll('[data-action="toggle-status"]').forEach(button => {
    button.addEventListener('click', () => {
        const userId = button.dataset.userId;
        const isActive = button.dataset.userStatus === 'ativo';

        if (isActive) {
            openUserModal({
                title: 'Confirmar Desativação',
                desc: 'Tem certeza de que deseja alterar o status deste usuário?',
                subtext: 'O usuário não poderá acessar o sistema enquanto estiver inativo.',
                btnClass: 'btn-confirm-delete',
                btnIcon: 'fa-pause',
                btnText: 'Sim, Desativar',
                onConfirm: () => executeToggleStatus(userId)
            });
        } else {
            openUserModal({
                title: 'Confirmar Ativação',
                desc: 'Tem certeza de que deseja reativar o acesso deste usuário?',
                subtext: 'O usuário voltará a ter acesso normal ao sistema.',
                btnClass: 'btn-confirm-success',
                btnIcon: 'fa-play',
                btnText: 'Sim, Ativar',
                onConfirm: () => executeToggleStatus(userId)
            });
        }
    });
});

document.querySelectorAll('[data-action="delete-user"]').forEach(button => {
    button.addEventListener('click', () => {
        const userId = button.dataset.userId;

        openUserModal({
            title: 'Confirmar Exclusão',
            desc: 'Tem certeza de que deseja excluir este usuário?',
            subtext: 'Esta ação não poderá ser desfeita.',
            btnClass: 'btn-confirm-delete',
            btnIcon: 'fa-trash-alt',
            btnText: 'Sim, Excluir',
            onConfirm: () => executeDeleteUser(userId)
        });
    });
});

function executeToggleStatus(userId) {
    fetch('users.php?action=toggle_status&id=' + encodeURIComponent(userId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Status do usuário alterado com sucesso!', 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.error || 'Erro ao alterar status', 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro de comunicação ao alterar status', 'danger');
    });
}

function executeDeleteUser(userId) {
    fetch('users.php?action=delete&id=' + encodeURIComponent(userId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Usuário excluído com sucesso!', 'success');
            const row = document.getElementById('user-' + userId);
            if (row) {
                row.style.transition = 'opacity 0.3s, transform 0.3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    row.remove();
                    const tbody = document.querySelector('tbody');
                    if (!tbody || tbody.children.length === 0) {
                        location.reload();
                    }
                }, 300);
            } else {
                location.reload();
            }
        } else {
            showToast(data.error || 'Erro ao excluir usuário', 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro de comunicação ao excluir usuário', 'danger');
    });
}
</script>
