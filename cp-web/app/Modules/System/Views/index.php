<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h4 mb-0">Servicios y Estado del Sistema</h1>
    </div>
    <div>
        <a href="/system" class="btn btn-sm btn-outline-secondary text-uppercase fw-bold text-nowrap">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar Estado
        </a>
    </div>
</div>

<div class="bg-body p-3 rounded mb-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle table-sm m-0">
            <thead>
                <tr>
                    <th class="ps-3">Servicio</th>
                    <th>Tipo / Descripcion</th>
                    <th>Estado Actual</th>
                    <th class="text-end pe-3 text-nowrap">Acciones de Control</th>
                </tr>
            </thead>
            <tbody>
                <!-- Nginx Proxy -->
                <tr>
                    <td class="ps-3 fw-bold">
                        <span class="d-inline-flex align-items-center">
                            <i class="bi bi-arrow-left-right me-2 text-primary"></i>
                            <code>nginx</code>
                        </span>
                    </td>
                    <td>Frontend Proxy Reverso y Terminacion SSL (Puerto 80 / 443)</td>
                    <td>
                        <?php $nginxStatus = $services["nginx"] ?? "inactive"; ?>
                        <span class="badge <?= ($nginxStatus === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
                            <?= strtoupper($nginxStatus) ?>
                        </span>
                    </td>
                    <td class="text-end pe-3 text-nowrap">
                        <div class="d-flex justify-content-end gap-1">
                            <form action="/system/action" method="POST" class="d-inline m-0">
                                <input type="hidden" name="service" value="nginx">
                                <button type="submit" name="action" value="restart" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reiniciar
                                </button>
                                <button type="submit" name="action" value="reload" class="btn btn-sm btn-outline-info text-uppercase fw-bold text-nowrap">
                                    <i class="bi bi-arrow-repeat me-1"></i> Recargar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Apache Backend -->
                <tr>
                    <td class="ps-3 fw-bold">
                        <span class="d-inline-flex align-items-center">
                            <i class="bi bi-server me-2 text-info"></i>
                            <code>apache2</code>
                        </span>
                    </td>
                    <td>Backend Web Server con mod_rewrite y .htaccess (Puerto 8080)</td>
                    <td>
                        <?php $apStatus = $services["apache"] ?? "inactive"; ?>
                        <span class="badge <?= ($apStatus === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
                            <?= strtoupper($apStatus) ?>
                        </span>
                    </td>
                    <td class="text-end pe-3 text-nowrap">
                        <div class="d-flex justify-content-end gap-1">
                            <form action="/system/action" method="POST" class="d-inline m-0">
                                <input type="hidden" name="service" value="apache2">
                                <button type="submit" name="action" value="restart" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reiniciar
                                </button>
                                <button type="submit" name="action" value="reload" class="btn btn-sm btn-outline-info text-uppercase fw-bold text-nowrap">
                                    <i class="bi bi-arrow-repeat me-1"></i> Recargar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- MariaDB -->
                <tr>
                    <td class="ps-3 fw-bold">
                        <span class="d-inline-flex align-items-center">
                            <i class="bi bi-database me-2 text-success"></i>
                            <code>mariadb</code>
                        </span>
                    </td>
                    <td>Servidor de Base de Datos Relacional</td>
                    <td>
                        <?php $dbStatus = $services["mariadb"] ?? "inactive"; ?>
                        <span class="badge <?= ($dbStatus === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
                            <?= strtoupper($dbStatus) ?>
                        </span>
                    </td>
                    <td class="text-end pe-3 text-nowrap">
                        <div class="d-flex justify-content-end gap-1">
                            <form action="/system/action" method="POST" class="d-inline m-0">
                                <input type="hidden" name="service" value="mariadb">
                                <button type="submit" name="action" value="restart" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reiniciar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Fail2Ban -->
                <?php $f2bStatus = $services["fail2ban"] ?? "not-found"; ?>
                <?php if ($f2bStatus !== "not-found"): ?>
                <tr>
                    <td class="ps-3 fw-bold">
                        <span class="d-inline-flex align-items-center">
                            <i class="bi bi-shield-lock me-2 text-danger"></i>
                            <code>fail2ban</code>
                        </span>
                    </td>
                    <td>Proteccion contra Ataques de Fuerza Bruta</td>
                    <td>
                        <span class="badge <?= ($f2bStatus === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-danger-subtle text-danger border border-danger-subtle" ?>">
                            <?= strtoupper($f2bStatus) ?>
                        </span>
                    </td>
                    <td class="text-end pe-3 text-nowrap">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="/firewall" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap">
                                <i class="bi bi-shield-exclamation me-1"></i> Gestionar
                            </a>
                            <form action="/system/action" method="POST" class="d-inline m-0">
                                <input type="hidden" name="service" value="fail2ban">
                                <button type="submit" name="action" value="restart" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reiniciar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <!-- IPTables -->
                <tr>
                    <td class="ps-3 fw-bold">
                        <span class="d-inline-flex align-items-center">
                            <i class="bi bi-diagram-3 me-2 text-secondary"></i>
                            <code>iptables</code>
                        </span>
                    </td>
                    <td>Firewall de Red a Nivel de Kernel</td>
                    <td>
                        <?php
                        $iptCmd = shell_exec("which iptables 2>/dev/null");
                        $iptOk  = !empty(trim($iptCmd ?? ""));
                        ?>
                        <span class="badge <?= $iptOk ? "bg-success-subtle text-success border border-success-subtle" : "bg-secondary-subtle text-secondary border border-secondary-subtle" ?>">
                            <?= $iptOk ? "DISPONIBLE" : "NO DISPONIBLE" ?>
                        </span>
                    </td>
                    <td class="text-end pe-3 text-nowrap">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="/firewall" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap">
                                <i class="bi bi-diagram-3 me-1"></i> Gestionar
                            </a>
                        </div>
                    </td>
                </tr>

                <!-- PHP-FPM Services -->
                <?php if (!empty($services["php_fpm"])): ?>
                    <?php foreach ($services["php_fpm"] as $phpSvc): ?>
                        <?php if ($phpSvc["status"] !== "not-found"): ?>
                        <tr>
                            <td class="ps-3 fw-bold">
                                <span class="d-inline-flex align-items-center">
                                    <i class="bi bi-code-slash me-2 text-warning"></i>
                                    <code><?= htmlspecialchars($phpSvc["service"]) ?></code>
                                </span>
                            </td>
                            <td>Manejador PHP FastCGI (Version <?= htmlspecialchars($phpSvc["version"]) ?>)</td>
                            <td>
                                <span class="badge <?= ($phpSvc["status"] === "active") ? "bg-success-subtle text-success border border-success-subtle" : "bg-secondary-subtle text-secondary border border-secondary-subtle" ?>">
                                    <?= strtoupper($phpSvc["status"]) ?>
                                </span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <div class="d-flex justify-content-end gap-1">
                                    <form action="/system/action" method="POST" class="d-inline m-0">
                                        <input type="hidden" name="service" value="<?= htmlspecialchars($phpSvc["service"]) ?>">
                                        <button type="submit" name="action" value="restart" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap">
                                            <i class="bi bi-arrow-clockwise me-1"></i> Reiniciar
                                        </button>
                                        <button type="submit" name="action" value="reload" class="btn btn-sm btn-outline-info text-uppercase fw-bold text-nowrap">
                                            <i class="bi bi-arrow-repeat me-1"></i> Recargar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
