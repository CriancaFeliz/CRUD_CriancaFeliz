<div class="actions" style="display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px;">
    <a href="socioeconomico_list.php" class="btn secondary">Voltar</a>
    <a href="socioeconomico_list.php?action=export" class="btn">Exportar CSV</a>
</div>

<div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,.08);">
    <h2 style="margin-top:0;">Relatorio Socioeconomico</h2>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin:20px 0;">
        <div style="background:#f8f9fa; border-radius:8px; padding:16px;">
            <div style="font-size:12px; color:#6c757d; font-weight:600;">Total de fichas</div>
            <div style="font-size:28px; font-weight:700;"><?php echo intval($report['total_fichas'] ?? 0); ?></div>
        </div>
        <div style="background:#f8f9fa; border-radius:8px; padding:16px;">
            <div style="font-size:12px; color:#6c757d; font-weight:600;">Renda media</div>
            <div style="font-size:28px; font-weight:700;">R$ <?php echo number_format(floatval($report['renda_media'] ?? 0), 2, ',', '.'); ?></div>
        </div>
        <div style="background:#f8f9fa; border-radius:8px; padding:16px;">
            <div style="font-size:12px; color:#6c757d; font-weight:600;">Membros por familia</div>
            <div style="font-size:28px; font-weight:700;"><?php echo number_format(floatval($report['membros_media'] ?? 0), 1, ',', '.'); ?></div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:20px;">
        <section>
            <h3>Situacao economica</h3>
            <table style="width:100%; border-collapse:collapse;">
                <tbody>
                    <?php foreach (($report['situacoes'] ?? []) as $label => $total): ?>
                        <tr>
                            <td style="padding:10px; border-bottom:1px solid #dee2e6;"><?php echo htmlspecialchars($label); ?></td>
                            <td style="padding:10px; border-bottom:1px solid #dee2e6; text-align:right; font-weight:700;"><?php echo intval($total); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section>
            <h3>Faixa etaria</h3>
            <table style="width:100%; border-collapse:collapse;">
                <tbody>
                    <?php foreach (($report['faixa_etaria'] ?? []) as $label => $total): ?>
                        <tr>
                            <td style="padding:10px; border-bottom:1px solid #dee2e6;"><?php echo htmlspecialchars($label); ?></td>
                            <td style="padding:10px; border-bottom:1px solid #dee2e6; text-align:right; font-weight:700;"><?php echo intval($total); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</div>
