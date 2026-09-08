<?php
function safeDate($date) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') return '-';
    $ts = strtotime(str_replace('/', '-', (string)$date));
    return ($ts && $ts > 0) ? date('d/m/Y', $ts) : '-';
}

$patients = $patients ?? [];
?>

<div class="actions flex-between flex-wrap gap-2 mb-4">
    <div>
        <a href="psychology.php" class="btn secondary">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>
    </div>
    
    <div class="d-flex gap-2">
        <div class="search-box" style="position:relative;">
            <input type="text" 
                   id="patientSearch" 
                   class="form-control"
                   placeholder="Buscar paciente..." 
                   style="padding-left: 40px !important; min-width: 250px;">
            <i class="fas fa-search text-muted" style="position:absolute; left:14px; top:50%; transform:translateY(-50%);"></i>
        </div>
        
        <select id="ageFilter" class="form-select">
            <option value="">Todas as idades</option>
            <option value="crianca">Crianças (0-11)</option>
            <option value="adolescente">Adolescentes (12-17)</option>
            <option value="adulto">Adultos (18+)</option>
        </select>
    </div>
</div>

<div class="patients-grid">
<?php if (empty($patients)): ?>

    <div class="empty-state text-center p-5 card-glass text-muted">
        <div style="font-size: 48px; margin-bottom: 16px;"><i class="fas fa-users"></i></div>
        <div style="font-size: 20px; font-weight: 600; margin-bottom: 8px;">Nenhum paciente encontrado</div>
        <div style="font-size: 15px; line-height: 1.5;">
            Os pacientes aparecerão aqui automaticamente quando<br>
            fichas de acolhimento forem cadastradas no sistema.
        </div>
    </div>

<?php else: ?>

    <div class="patients-table table-responsive card-glass p-0">
        <table style="width:100%; border-collapse:collapse; margin:0;" id="patientsTable">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); background: rgba(0,0,0,0.02);">
                    <th style="padding:16px; text-align:left; font-weight:600; color:var(--text-muted) !important; font-size:13px; text-transform:uppercase;">Paciente</th>
                    <th style="padding:16px; text-align:left; font-weight:600; color:var(--text-muted) !important; font-size:13px; text-transform:uppercase;">Idade</th>
                    <th style="padding:16px; text-align:left; font-weight:600; color:var(--text-muted) !important; font-size:13px; text-transform:uppercase;">Responsável</th>
                    <th style="padding:16px; text-align:left; font-weight:600; color:var(--text-muted) !important; font-size:13px; text-transform:uppercase;">Acolhimento</th>
                    <th style="padding:16px; text-align:left; font-weight:600; color:var(--text-muted) !important; font-size:13px; text-transform:uppercase;">Última Anotação</th>
                    <th style="padding:16px; text-align:center; font-weight:600; color:var(--text-muted) !important; font-size:13px; text-transform:uppercase; width:120px;">Ações</th>
                </tr>
            </thead>
            
            <tbody>
                <?php foreach ($patients as $patient):

                    $nome = (string)($patient['nome_completo'] ?? 'Não informado');
                    $cpf = (string)($patient['cpf'] ?? 'Não informado');
                    $idade = isset($patient['idade']) ? (int)$patient['idade'] : '-';

                    $dataNasc = safeDate($patient['data_nascimento'] ?? null);
                    $dataAcolh = safeDate($patient['data_acolhimento'] ?? null);
                    $lastNote = safeDate($patient['last_note'] ?? null);
                    $responsavel = (string)($patient['responsavel'] ?? 'Não informado');

                    $cpfLimpo = preg_replace('/\D/', '', $patient['cpf'] ?? '');
                    $cpfFormatado = (strlen($cpfLimpo) === 11) ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfLimpo) : ($patient['cpf'] ?? '');

                    $avatar = strtoupper(mb_substr($nome, 0, 1, 'UTF-8'));
                ?>

                <tr class="patient-row"
                    style="border-bottom:1px solid var(--border-color); transition: background 0.2s;"
                    data-name="<?= e(mb_strtolower($nome, 'UTF-8')) ?>"
                    data-cpf="<?= e($cpf) ?>"
                    data-age-group="<?php 
                        if ($idade !== '-' && $idade < 12) echo 'crianca';
                        elseif ($idade !== '-' && $idade < 18) echo 'adolescente';
                        else echo 'adulto';
                    ?>">

                    <!-- NOME + CPF -->
                    <td style="padding:16px;">
                        <div style="display:flex; align-items:center; gap:14px;">
                            
                            <?php if (!empty($patient['foto'])): ?>
                                <img src="acolhimento_view.php?action=photo&id=<?= $patient['id'] ?>" alt="<?= e($nome) ?>" style="width:42px; height:42px; border-radius:50%; object-fit:cover; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <?php else: ?>
                                <div class="avatar-placeholder" 
                                    style="width:42px; height:42px; border-radius:50%; background: linear-gradient(135deg, #0ea5e9, #20c997); display:flex; align-items:center; justify-content:center; font-weight:600; color:white; font-size:16px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                    <?= e($avatar) ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <div style="font-weight:600; color:var(--text-primary); margin-bottom:4px;">
                                    <?= e($nome) ?>
                                </div>
                                <div style="font-size:13px; color:var(--text-muted); font-family:monospace;">
                                    <i class="fas fa-id-card" style="opacity: 0.7;"></i> <?= e($cpfFormatado) ?>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- IDADE + NASCIMENTO -->
                    <td style="padding:16px;">
                        <div style="display:flex; align-items:baseline; gap:4px; margin-bottom: 4px;">
                            <span style="font-weight:600; font-size:16px; color:var(--text-primary);">
                                <?= $idade ?>
                            </span>
                            <span style="font-size:13px; color:var(--text-muted);">anos</span>
                        </div>

                        <div style="font-size:12px; color:var(--text-muted);">
                            <i class="fas fa-birthday-cake" style="opacity: 0.6; margin-right: 4px;"></i><?= e($dataNasc) ?>
                        </div>
                    </td>

                    <!-- RESPONSAVEL -->
                    <td style="padding:16px;">
                        <div style="font-size:14px; <?= $responsavel === 'Não informado' ? 'color:var(--text-muted); opacity: 0.6; font-style: italic;' : 'color:var(--text-primary);' ?>">
                            <?= e($responsavel) ?>
                        </div>
                    </td>

                    <!-- ACOLHIMENTO -->
                    <td style="padding:16px;">
                        <div style="font-size:14px; <?= $dataAcolh === '-' ? 'color:var(--text-muted); opacity: 0.6; font-style: italic;' : 'color:var(--text-muted);' ?>">
                            <?= e($dataAcolh) ?>
                        </div>
                    </td>

                    <!-- ÚLTIMA ANOTAÇÃO -->
                    <td style="padding:16px;">
                        <?php if ($lastNote !== '-'): ?>
                            <div style="font-size:14px; color:var(--primary-blue); font-weight:500;">
                                <i class="fas fa-clock" style="opacity: 0.7; margin-right: 4px;"></i> <?= e($lastNote) ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:13px; font-style: italic; opacity: 0.6;">Sem anotações</span>
                        <?php endif; ?>
                    </td>

                    <!-- AÇÕES -->
                    <td style="padding:16px; text-align:center;">
                        <a href="psychology.php?action=patient&cpf=<?= urlencode($cpf) ?>" class="btn primary" style="padding: 8px 16px; border-radius: 6px;">
                            Abrir <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </td>
                </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('patientSearch');
    const ageFilter = document.getElementById('ageFilter');
    const rows = document.querySelectorAll('.patient-row');

    function filterPatients() {
        const query = (searchInput.value || '').toLowerCase().trim();
        const selectedAge = ageFilter.value;

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const cpf = row.getAttribute('data-cpf');
            const ageGroup = row.getAttribute('data-age-group');

            const matchSearch = !query || name.includes(query) || cpf.includes(query);
            const matchAge = !selectedAge || ageGroup === selectedAge;

            if (matchSearch && matchAge) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterPatients);
    }
    if (ageFilter) {
        ageFilter.addEventListener('change', filterPatients);
    }
});
</script>
