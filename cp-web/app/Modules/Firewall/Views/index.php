<?php
$f2bActive = ($f2bStatus["status"] ?? "not-found") === "active";
$iptOk     = (bool)($iptStatus["available"] ?? false);
?>

<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h4 mb-0">Firewall del Servidor</h1>
    </div>
    <div>
        <a href="/firewall" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
        </a>
    </div>
</div>

<!-- Resumen de estado -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="bg-body p-3 rounded d-flex align-items-center gap-3">
            <div class="fs-2 <?= $f2bActive ? "text-success" : "text-danger" ?>">
                <i class="bi bi-shield-lock"></i>
            </div>
            <div>
                <div class="fw-bold">Fail2Ban</div>
                <span class="badge <?= $f2bActive ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
                    <?= $f2bActive ? "ACTIVO" : strtoupper($f2bStatus["status"] ?? "NO ENCONTRADO") ?>
                </span>
                <?php if ($f2bActive): ?>
                    <span class="ms-2 text-muted small"><?= (int)($f2bStatus["banned_count"] ?? 0) ?> IP(s) baneadas</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="bg-body p-3 rounded d-flex align-items-center gap-3">
            <div class="fs-2 <?= $iptOk ? "text-success" : "text-secondary" ?>">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div>
                <div class="fw-bold">IPTables</div>
                <span class="badge <?= $iptOk ? "bg-success-subtle text-success border border-success-subtle" : "bg-secondary-subtle text-secondary border border-secondary-subtle" ?>">
                    <?= $iptOk ? "DISPONIBLE" : "NO DISPONIBLE" ?>
                </span>
                <?php if ($iptOk): ?>
                    <span class="ms-2 text-muted small"><?= (int)($iptStatus["drop_rules"] ?? 0) ?> regla(s) DROP activas</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Formulario banear IP -->
<div class="bg-body p-3 rounded mb-3">
    <h6 class="fw-bold mb-3"><i class="bi bi-slash-circle me-1"></i> Banear una IP</h6>
    <form action="/firewall/ban" method="POST" class="row g-2 align-items-end">
        <div class="col-md-5">
            <label for="ip_ban" class="form-label">Direccion IP <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="ip_ban" name="ip" placeholder="Ej: 192.168.1.100" required>
        </div>
        <div class="col-md-4">
            <label for="method_ban" class="form-label">Metodo</label>
            <select class="form-select" id="method_ban" name="method">
                <option value="f2b" <?= !$f2bActive ? "disabled" : "" ?>>Fail2Ban (jail sshd)</option>
                <option value="ipt" <?= !$iptOk ? "disabled" : "" ?>>IPTables (DROP)</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-danger w-100 text-uppercase fw-bold">
                <i class="bi bi-slash-circle me-1"></i> Banear IP
            </button>
        </div>
    </form>
</div>

<!-- Fail2Ban: Jails -->
<div class="bg-body p-3 rounded mb-3">
    <h6 class="fw-bold mb-3"><i class="bi bi-shield-exclamation me-1"></i> Fail2Ban - Jails Activos</h6>

    <?php if (!$f2bActive): ?>
        <div class="text-center text-muted py-4">
            <i class="bi bi-shield-x fs-3 d-block mb-2"></i>
            Fail2Ban no esta activo o no esta instalado en este servidor.
        </div>
    <?php elseif (empty($jails)): ?>
        <div class="text-center text-muted py-4">
            <i class="bi bi-shield-check fs-3 d-block mb-2"></i>
            No hay jails activos con IPs baneadas.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle table-sm m-0">
                <thead>
                    <tr>
                        <th class="ps-3">Jail</th>
                        <th>Actualmente Baneadas</th>
                        <th>Total Baneadas</th>
                        <th>IPs Baneadas</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jails as $jail): ?>
                    <tr>
                        <td class="ps-3 fw-bold">
                            <i class="bi bi-shield me-1 text-warning"></i>
                            <code><?= $jail["jail"] ?></code>
                        </td>
                        <td>
                            <span class="badge <?= (int)$jail["currently_banned"] > 0 ? "bg-danger-subtle text-danger border border-danger-subtle" : "bg-secondary-subtle text-secondary border border-secondary-subtle" ?>">
                                <?= (int)$jail["currently_banned"] ?>
                            </span>
                        </td>
                        <td class="text-muted"><?= (int)$jail["total_banned"] ?></td>
                        <td>
                            <?php if (!empty($jail["banned_ips"])): ?>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($jail["banned_ips"] as $ip): ?>
                                        <code class="small bg-danger-subtle text-danger px-1 rounded"><?= $ip ?></code>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small">Ninguna</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-3 text-nowrap">
                            <?php foreach ($jail["banned_ips"] as $ip): ?>
                            <form action="/firewall/unban" method="POST" class="d-inline m-0">
                                <input type="hidden" name="ip" value="<?= $ip ?>">
                                <input type="hidden" name="method" value="f2b">
                                <button type="submit" class="btn btn-sm btn-outline-success text-uppercase fw-bold text-nowrap">
                                    <i class="bi bi-shield-check me-1"></i> Desbanear <?= $ip ?>
                                </button>
                            </form>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- IPTables: Reglas DROP -->
<div class="bg-body p-3 rounded mb-3">
    <h6 class="fw-bold mb-3"><i class="bi bi-diagram-3 me-1"></i> IPTables - Reglas DROP Activas (INPUT)</h6>

    <?php if (!$iptOk): ?>
        <div class="text-center text-muted py-4">
            <i class="bi bi-x-circle fs-3 d-block mb-2"></i>
            IPTables no esta disponible en este servidor.
        </div>
    <?php elseif (empty($iptRules)): ?>
        <div class="text-center text-muted py-4">
            <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
            No hay reglas DROP activas en la cadena INPUT.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle table-sm m-0">
                <thead>
                    <tr>
                        <th class="ps-3">IP Bloqueada</th>
                        <th>Tipo de Regla</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($iptRules as $ip): ?>
                    <tr>
                        <td class="ps-3 fw-bold">
                            <code class="text-danger"><?= $ip ?></code>
                        </td>
                        <td>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">INPUT DROP</span>
                        </td>
                        <td class="text-end pe-3 text-nowrap">
                            <form action="/firewall/unban" method="POST" class="d-inline m-0">
                                <input type="hidden" name="ip" value="<?= $ip ?>">
                                <input type="hidden" name="method" value="ipt">
                                <button type="submit" class="btn btn-sm btn-outline-success text-uppercase fw-bold text-nowrap">
                                    <i class="bi bi-check-circle me-1"></i> Desbloquear
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
