<div class="actions" style="display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px;">
    <a href="attendance.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">
        ← Voltar
    </a>
</div>

<div class="card" style="background:#fff; border-radius:12px; padding:24px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 20px 0; color:#495057; border-bottom:2px solid #6fb64f; padding-bottom:8px;">
        Marcar Presença/Falta em Lote
    </h3>

    <form method="get" action="attendance.php" style="display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; align-items:end;">
        <input type="hidden" name="action" value="batch">
        <div>
            <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">Data</label>
            <input type="date" name="data" value="<?php echo htmlspecialchars($data); ?>" style="padding:10px; border:2px solid #f0a36b; border-radius:8px; width:100%;">
        </div>
        <div>
            <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">Grupo</label>
            <select name="grupo" style="padding:10px; border:2px solid #f0a36b; border-radius:8px; width:100%;">
                <option value="Todos" <?php echo ($grupo==='Todos'?'selected':''); ?>>Todos</option>
                <option value="Crianca" <?php echo ($grupo==='Crianca'?'selected':''); ?>>Crianças</option>
                <option value="Adolescente" <?php echo ($grupo==='Adolescente'?'selected':''); ?>>Adolescentes</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn" style="background:#17a2b8; color:#fff; border:none; padding:12px 16px; border-radius:8px; cursor:pointer; width:100%;">
                Filtrar
            </button>
        </div>
    </form>
</div>

<div class="card" style="background:#fff; border-radius:12px; padding:24px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <form method="post" action="attendance.php?action=apply_batch">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="data" value="<?php echo htmlspecialchars($data); ?>">
        <input type="hidden" name="grupo" value="<?php echo htmlspecialchars($grupo); ?>">

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; align-items:end; margin-bottom:16px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:8px;">Ação em Lote</label>
                <select name="acao" style="padding:10px; border:2px solid #f0a36b; border-radius:8px; width:100%;">
                    <option value="Presenca">Marcar Presença</option>
                    <option value="Falta">Marcar Falta</option>
                </select>
            </div>
            <div>
                <div style="font-size:14px; color:#6c757d;">Data: <strong><?php echo htmlspecialchars($data); ?></strong></div>
                <div style="font-size:14px; color:#6c757d;">Grupo: <strong><?php echo htmlspecialchars($grupo); ?></strong></div>
                <div style="font-size:14px; color:#6c757d;">Selecionados: <strong id="sel-count">0</strong> de <strong><?php echo count($atendidos); ?></strong></div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="submit" class="btn" style="background:#6fb64f; color:#fff; border:none; padding:12px 16px; border-radius:8px; cursor:pointer;">
                    Aplicar em Lote
                </button>
            </div>
        </div>

        <div class="table-wrapper" style="overflow:auto; max-height:460px; border:1px solid #eee; border-radius:8px;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8f9fa;">
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #eee; width:36px;">
                            <input type="checkbox" id="check-all">
                        </th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">ID</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">Nome</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">Idade</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">Status</th>
                        <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($atendidos)) : ?>
                    <tr>
                        <td colspan="5" style="padding:14px; color:#6c757d;">Nenhum atendido encontrado para o filtro selecionado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($atendidos as $a): ?>
                        <tr>
                            <td style="padding:10px; border-bottom:1px solid #f1f3f5;">
                                <?php $id = $a['id'] ?? $a['idatendido'] ?? null; ?>
                                <?php if ($id): ?>
                                    <input type="checkbox" class="row-check" name="ids[]" value="<?php echo htmlspecialchars($id); ?>" checked>
                                <?php endif; ?>
                            </td>
                            <td style="padding:10px; border-bottom:1px solid #f1f3f5;">&nbsp;<?php echo htmlspecialchars($a['id'] ?? $a['idatendido'] ?? ''); ?></td>
                            <td style="padding:10px; border-bottom:1px solid #f1f3f5;">&nbsp;<?php echo htmlspecialchars($a['nome'] ?? $a['nome_completo'] ?? ''); ?></td>
                            <td style="padding:10px; border-bottom:1px solid #f1f3f5;">
                                <?php 
                                $idade = null; 
                                if (!empty($a['data_nascimento'])) { 
                                    try { 
                                        $birth = new DateTime($a['data_nascimento']); 
                                        $now = new DateTime($data); 
                                        $idade = $birth->diff($now)->y; 
                                    } catch (Exception $e) { $idade = null; }
                                }
                                echo $idade !== null ? intval($idade) : '-';
                                ?>
                            </td>
                            <td style="padding:10px; border-bottom:1px solid #f1f3f5;">Ativo</td>
                            <td style="padding:10px; border-bottom:1px solid #f1f3f5;">
                                <?php if (!empty($id)): ?>
                                <form method="post" action="attendance.php?action=register_presence" style="display:inline; margin-right:6px;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="atendido_id" value="<?php echo htmlspecialchars($id); ?>">
                                    <input type="hidden" name="data" value="<?php echo htmlspecialchars($data); ?>">
                                    <button class="btn" style="background:#28a745; color:#fff; padding:6px 10px; border-radius:6px; border:none; cursor:pointer;">Presença</button>
                                </form>
                                <form method="post" action="attendance.php?action=register_absence" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="atendido_id" value="<?php echo htmlspecialchars($id); ?>">
                                    <input type="hidden" name="data" value="<?php echo htmlspecialchars($data); ?>">
                                    <button class="btn" style="background:#dc3545; color:#fff; padding:6px 10px; border-radius:6px; border:none; cursor:pointer;">Falta</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const checkAll = document.getElementById('check-all');
  const rowChecks = Array.from(document.querySelectorAll('.row-check'));
  const selCount = document.getElementById('sel-count');

  function updateCount() {
    const count = rowChecks.filter(c => c.checked).length;
    if (selCount) selCount.textContent = count;
  }

  if (checkAll) {
    checkAll.addEventListener('change', function() {
      rowChecks.forEach(c => c.checked = checkAll.checked);
      updateCount();
    });
  }

  rowChecks.forEach(c => c.addEventListener('change', updateCount));
  updateCount();
});
</script>
