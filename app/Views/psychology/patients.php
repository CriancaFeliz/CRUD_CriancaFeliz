<?php
function safeDate($date) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') return '-';
    $ts = strtotime(str_replace('/', '-', (string)$date));
    return ($ts && $ts > 0) ? date('d/m/Y', $ts) : '-';
}

$patients = $patients ?? [];
?>

<div class="actions" style="display:flex; gap:12px; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <a href="psychology.php" class="btn secondary" style="background:#6c757d; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">
            ← Voltar ao Dashboard
        </a>
    </div>
    
    <div style="display:flex; gap:12px; align-items:center;">
        <div class="search-box" style="position:relative;">
            <input type="text" 
                   id="patientSearch" 
                   placeholder="Buscar paciente..." 
                   style="padding:10px 40px 10px 12px; border:2px solid #17a2b8; border-radius:8px; width:250px; font-family:Poppins;">
            <div style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#17a2b8; font-size:16px;">🔍</div>
        </div>
        
        <select id="ageFilter" style="padding:10px 12px; border:2px solid #17a2b8; border-radius:8px; font-family:Poppins;">
            <option value="">Todas as idades</option>
            <option value="crianca">Crianças (0-11)</option>
            <option value="adolescente">Adolescentes (12-17)</option>
            <option value="adulto">Adultos (18+)</option>
        </select>
    </div>
</div>

<div class="patients-grid" style="display:grid; gap:20px;">
<?php if (empty($patients)): ?>

    <div class="empty-state" style="text-align:center; padding:60px; background:#fff; border-radius:12px; color:#6c757d;">
        <div style="font-size:64px; margin-bottom:20px;">👥</div>
        <div style="font-size:24px; font-weight:600; margin-bottom:12px;">Nenhum paciente encontrado</div>
        <div style="font-size:16px; line-height:1.5;">
            Os pacientes aparecerão aqui automaticamente quando<br>
            fichas de acolhimento forem cadastradas no sistema.
        </div>
    </div>

<?php else: ?>

    <div class="patients-table" style="background:#fff; border-radius:12px; overflow:hidden; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <table style="width:100%; border-collapse:collapse;" id="patientsTable">
            <thead>
                <tr style="background:#f8f9fa;">
                    <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Paciente</th>
                    <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Idade</th>
                    <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Responsável</th>
                    <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Acolhimento</th>
                    <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Última Anotação</th>
                    <th style="padding:16px; text-align:center; font-weight:600; border-bottom:1px solid #dee2e6; width:120px;">Ações</th>
                </tr>
            </thead>
            
            <tbody>
                <?php foreach ($patients as $patient):

                    $nome = htmlspecialchars((string)($patient['nome_completo'] ?? 'Não informado'));
                    $cpf = htmlspecialchars((string)($patient['cpf'] ?? 'Não informado'));
                    $idade = isset($patient['idade']) ? (int)$patient['idade'] : '-';

                    $dataNasc = safeDate($patient['data_nascimento'] ?? null);
                    $dataAcolh = safeDate($patient['data_acolhimento'] ?? null);

                    $lastNote = safeDate($patient['last_note'] ?? null);

                    $responsavel = htmlspecialchars((string)($patient['responsavel'] ?? 'Não informado'));

                    $avatar = strtoupper(mb_substr($nome, 0, 1, 'UTF-8'));
                ?>

                <tr class="patient-row"
                    style="border-bottom:1px solid #f0f0f0;"
                    data-name="<?= strtolower($nome) ?>"
                    data-cpf="<?= $cpf ?>"
                    data-age-group="<?php 
                        if ($idade !== '-' && $idade < 12) echo 'crianca';
                        elseif ($idade !== '-' && $idade < 18) echo 'adolescente';
                        else echo 'adulto';
                    ?>">

                    <!-- NOME + CPF -->
                    <td style="padding:16px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            
                            <div class="avatar" 
                                style="width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg, #17a2b8, #20c997); display:flex; align-items:center; justify-content:center; font-weight:600; color:white; font-size:18px;">
                                <?= $avatar ?>
                            </div>

                            <div>
                                <div style="font-weight:600; color:#212529; margin-bottom:2px;">
                                    <?= $nome ?>
                                </div>
                                <div style="font-size:12px; color:#6c757d; font-family:monospace;">
                                    CPF: <?= $cpf ?>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- IDADE + NASCIMENTO -->
                    <td style="padding:16px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-weight:600; font-size:18px; color:#17a2b8;">
                                <?= $idade ?>
                            </span>
                            <span style="font-size:12px; color:#6c757d;">anos</span>
                        </div>

                        <div style="font-size:11px; color:#6c757d; margin-top:2px;">
                            <?= $dataNasc ?>
                        </div>
                    </td>

                    <!-- RESPONSAVEL -->
                    <td style="padding:16px;">
                        <div style="font-size:14px; color:#495057;">
                            <?= $responsavel ?>
                        </div>
                    </td>

                    <!-- ACOLHIMENTO -->
                    <td style="padding:16px;">
                        <div style="font-size:14px; color:#6c757d;">
                            <?= $dataAcolh ?>
                        </div>
                    </td>

                    <!-- ÚLTIMA ANOTAÇÃO -->
                    <td style="padding:16px;">
                        <?php if ($lastNote !== '-'): ?>
                            <div style="font-size:14px; color:#28a745; font-weight:500;">
                                <?= $lastNote ?>
                            </div>
                        <?php else: ?>
                            <span style="color:#dc3545; font-size:14px; font-weight:500;">Sem anotações</span>
                        <?php endif; ?>
                    </td>

                    <!-- AÇÕES -->
                    <td style="padding:16px; text-align:center; white-space:nowrap;">
                        <div style="display:flex; gap:8px; justify-content:center;">
                        
                            <a href="psychology.php?action=patient&cpf=<?= urlencode($cpf) ?>" 
                                class="btn-action"
                                style="background:#6c757d; color:#fff; padding:8px 12px; border-radius:6px; text-decoration:none; font-size:12px;">
                                👤 Ver
                            </a>

                            <a href="psychology.php?action=patient&cpf=<?= urlencode($cpf) ?>#new-note"
                                class="btn-action"
                                style="background:#17a2b8; color:white; padding:8px 12px; border-radius:6px; text-decoration:none; font-size:12px;">
                                🧠 Atender
                            </a>

                        </div>
                    </td>
                </tr>

                <?php endforeach; ?>

            </tbody>
        </table>
    </div>

    <div class="patients-summary" style="background:#f8f9fa; border-radius:12px; padding:20px; margin-top:20px; text-align:center;">
        <div style="font-size:16px; color:#495057;">
            <strong id="visibleCount"><?= count($patients) ?></strong> de <strong><?= count($patients) ?></strong> pacientes
        </div>
    </div>

<?php endif; ?>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("patientSearch");
    const ageFilter = document.getElementById("ageFilter");
    const rows = document.querySelectorAll(".patient-row");
    const counter = document.getElementById("visibleCount");

    function applyFilters() {
        const search = searchInput.value.trim().toLowerCase();
        const age = ageFilter.value;

        let visible = 0;

        rows.forEach(row => {
            const name = row.dataset.name.toLowerCase();
            const cpf = row.dataset.cpf.toLowerCase();
            const ageGroup = row.dataset.ageGroup;

            let matchesSearch =
                name.includes(search) ||
                cpf.includes(search);

            let matchesAge =
                age === "" || ageGroup === age;

            if (matchesSearch && matchesAge) {
                row.style.display = "";
                visible++;
            } else {
                row.style.display = "none";
            }
        });

        counter.textContent = visible;
    }

    searchInput.addEventListener("input", applyFilters);
    ageFilter.addEventListener("change", applyFilters);

});
</script>