<div class="bg-body p-3 rounded mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h4 mb-0">Bases de Datos (MariaDB)</h1>
    </div>
    <div>
        <a href="/database/create" class="btn btn-sm btn-primary text-uppercase fw-bold text-nowrap">
            <i class="bi bi-plus-lg me-1"></i> Nueva Base de Datos
        </a>
    </div>
</div>

<div class="bg-body p-3 rounded mb-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle table-sm m-0">
            <thead>
                <tr>
                    <th class="ps-3">Base de Datos</th>
                    <th>Usuario Asignado</th>
                    <th>Host Permitido</th>
                    <th>Fecha Creacion</th>
                    <th class="text-end pe-3 text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($databases)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No hay bases de datos creadas aun. <a href="/database/create" class="text-primary fw-bold text-uppercase">Crear la primera</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($databases as $db): ?>
                        <tr>
                            <td class="ps-3 fw-bold">
                                <span class="d-inline-flex align-items-center">
                                    <i class="bi bi-database me-2 text-primary"></i>
                                    <?= htmlspecialchars($db["db_name"]) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">
                                    <?= htmlspecialchars($db["db_user"]) ?>
                                </span>
                            </td>
                            <td><code>localhost</code></td>
                            <td class="text-muted small"><?= htmlspecialchars($db["created_at"] ?? "N/A") ?></td>
                            <td class="text-end pe-3 text-nowrap">
                                <div class="d-flex justify-content-end gap-1">
                                    <!-- Boton Auto-Login phpMyAdmin (Signon SSO) -->
                                    <a href="/database/autologin/<?= (int)$db["id"] ?>" target="_blank" class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap" title="Abrir phpMyAdmin">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> phpMyAdmin
                                    </a>
                                    <a href="/database/edit/<?= (int)$db["id"] ?>" class="btn btn-sm btn-outline-warning text-uppercase fw-bold text-nowrap" title="Editar Base de Datos">
                                        <i class="bi bi-pencil me-1"></i> Editar
                                    </a>
                                    <a href="/database/delete/<?= (int)$db["id"] ?>" class="btn btn-sm btn-outline-danger text-uppercase fw-bold text-nowrap" onclick="return confirm('Estas seguro de eliminar la base de datos <?= htmlspecialchars($db["db_name"]) ?>? Esta accion borrara todas las tablas y datos.')" title="Eliminar Base de Datos">
                                        <i class="bi bi-trash me-1"></i> Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
