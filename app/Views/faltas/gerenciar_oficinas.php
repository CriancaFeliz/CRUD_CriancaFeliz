<!-- Gerenciar Oficinas -->
<style>
    .actions-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .oficinas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .oficina-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 5px solid #3E6475;
    }
    .oficina-card.inativa {
        opacity: 0.6;
        border-left-color: #ccc;
    }
    .oficina-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }
    .oficina-nome {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }
    .oficina-status {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-ativa {
        background: #d4edda;
        color: #155724;
    }
    .status-inativa {
        background: #f8d7da;
        color: #721c24;
    }
    .oficina-info {
        margin: 10px 0;
        color: #666;
        font-size: 14px;
    }
    .oficina-info i {
        width: 20px;
        color: #348cb4;
    }
    .oficina-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    .btn-small {
        padding: 8px 14px;
        font-size: 13px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .btn-edit {
        background: #348cb4;
        color: #fff;
    }
    .btn-toggle {
        background: #6c757d;
        color: #fff;
    }
    .btn-small:hover {
        opacity: 0.9;
    }
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .modal.active {
        display: flex;
    }
    .modal-content {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .modal-header h3 {
        margin: 0;
    }
    .btn-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
    }
</style>

<!-- Header -->
<div class="actions-top">
    <h3 style="margin: 0;"><i class="fas fa-chalkboard-teacher"></i> Gerenciar Oficinas</h3>
    <button onclick="abrirModalNova()" class="btn">
        <i class="fas fa-plus"></i> Nova Oficina
    </button>
</div>

<!-- Grid de Oficinas -->
<div class="oficinas-grid">
    <?php if (empty($oficinas)): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #fff; border-radius: 12px;">
            <p style="color: #999;">Nenhuma oficina cadastrada</p>
        </div>
    <?php else: ?>
        <?php foreach ($oficinas as $oficina): ?>
            <div class="oficina-card <?php echo !$oficina['ativo'] ? 'inativa' : ''; ?>">
                <div class="oficina-header">
                    <div class="oficina-nome"><?php echo htmlspecialchars($oficina['nome']); ?></div>
                    <span class="oficina-status <?php echo $oficina['ativo'] ? 'status-ativa' : 'status-inativa'; ?>">
                        <?php echo $oficina['ativo'] ? 'Ativa' : 'Inativa'; ?>
                    </span>
                </div>
                
                <?php if ($oficina['descricao']): ?>
                    <div class="oficina-info">
                        <i class="fas fa-align-left"></i> <?php echo htmlspecialchars($oficina['descricao']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($oficina['dia_semana']): ?>
                    <div class="oficina-info">
                        <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($oficina['dia_semana']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($oficina['horario_inicio']): ?>
                    <div class="oficina-info">
                        <i class="fas fa-clock"></i> 
                        <?php echo substr($oficina['horario_inicio'], 0, 5); ?> - 
                        <?php echo substr($oficina['horario_fim'], 0, 5); ?>
                    </div>
                <?php endif; ?>
                
                <div class="oficina-actions">
                    <button onclick='abrirModalEditar(<?php echo json_encode($oficina); ?>)' class="btn-small btn-edit">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button onclick="toggleOficina(<?php echo $oficina['id_oficina']; ?>)" class="btn-small btn-toggle">
                        <i class="fas fa-power-off"></i> <?php echo $oficina['ativo'] ? 'Desativar' : 'Ativar'; ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="modalOficina" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">Nova Oficina</h3>
            <button class="btn-close" onclick="fecharModal()">&times;</button>
        </div>
        
        <form id="formOficina" method="POST" action="faltas.php?action=salvarOficinaConfig">
            <input type="hidden" name="_csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id_oficina" id="id_oficina" value="">
            
            <div class="form-group">
                <label>Nome da Oficina *</label>
                <input type="text" name="nome" id="nome" required>
            </div>
            
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" id="descricao" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label>Dia da Semana</label>
                <select name="dia_semana" id="dia_semana">
                    <option value="">Selecione...</option>
                    <option value="Segunda">Segunda-feira</option>
                    <option value="Terça">Terça-feira</option>
                    <option value="Quarta">Quarta-feira</option>
                    <option value="Quinta">Quinta-feira</option>
                    <option value="Sexta">Sexta-feira</option>
                    <option value="Sábado">Sábado</option>
                    <option value="Domingo">Domingo</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Horário Início</label>
                <input type="time" name="horario_inicio" id="horario_inicio">
            </div>
            
            <div class="form-group">
                <label>Horário Fim</label>
                <input type="time" name="horario_fim" id="horario_fim">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Salvar
                </button>
                <button type="button" onclick="fecharModal()" class="btn secondary">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalNova() {
    document.getElementById('modalTitulo').textContent = 'Nova Oficina';
    document.getElementById('formOficina').reset();
    document.getElementById('id_oficina').value = '';
    document.getElementById('modalOficina').classList.add('active');
}

function abrirModalEditar(oficina) {
    document.getElementById('modalTitulo').textContent = 'Editar Oficina';
    document.getElementById('id_oficina').value = oficina.id_oficina;
    document.getElementById('nome').value = oficina.nome;
    document.getElementById('descricao').value = oficina.descricao || '';
    document.getElementById('dia_semana').value = oficina.dia_semana || '';
    document.getElementById('horario_inicio').value = oficina.horario_inicio || '';
    document.getElementById('horario_fim').value = oficina.horario_fim || '';
    document.getElementById('modalOficina').classList.add('active');
}

function fecharModal() {
    document.getElementById('modalOficina').classList.remove('active');
}

function toggleOficina(id) {
    if (!confirm('Deseja alterar o status desta oficina?')) {
        return;
    }
    
    // Fazer requisição AJAX
    fetch('faltas.php?action=toggleOficina', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id_oficina=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recarregar a página para atualizar o status
            window.location.reload();
        } else {
            alert(data.error || 'Erro ao alterar status da oficina');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar requisição');
    });
}

// Fechar modal ao clicar fora
document.getElementById('modalOficina').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModal();
    }
});
</script>
