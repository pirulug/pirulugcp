---
trigger: always_on
description: Reglas para el estilo ui.
---

## Clases de Bootstrap PROHIBIDAS

Para que el framework gestione correctamente el cambio de tema (Light/Dark), tienes estrictamente prohibido usar las siguientes clases nativas:

- Sombras: .shadow, .shadow-sm, .shadow-lg. 
  - Alternativa: Usa los bordes nativos del framework o clases CSS personalizadas que se adapten al modo oscuro.
- Fondos: .bg-white, .bg-light, .bg-dark.
  - Alternativa: Deja que el contenedor (.card, .modal, etc.) use su color de fondo automático.
- Texto: .text-white, .text-light, .text-dark.
  - Alternativa: Usa .text-body o deja que el color se herede para asegurar legibilidad.
- Bordes: .border-0 en elementos .card. Se requiere el borde sutil para delimitar secciones en Dark Mode.
- Redondeado: .rounded en elementos .card. Las tarjetas ya tienen su radio de borde predefinido.
- Grupos: .btn-group en columnas de acción de tablas de datos. Usa botones individuales separados para evitar colapsos visuales.
- Etiquetas: Los elementos label solo deben tener la clase .form-label. Está estrictamente prohibido usar .fw-bold, .small, .text-uppercase o .text-secondary.

- Usar: `.mb-3 .p-3 .g-3 .mt-3 .pt-3`
- Prohibido: `.mb-4 .p-4 .g-4 .mt-4 .pt-4`

### Cards (no duplicar estilos base)
- `.card`: no usar `.bg-body .border .p-3`
- `.card-header`: no `.bg-body .border-bottom .py-3`
- `.card-body`: no `.p-3 .p-0`
- `.card-footer`: no `.bg-body .border-top .py-3`

## Estética

- **Iconos:** Uso **estrictamente obligatorio** de Bootstrap Icons (`bi bi-...`). Si se encuentran otros iconos (FontAwesome `fa-solid`/`fa-regular`, etiquetas `<svg>`, etc.), se deben reemplazar obligatoriamente por su equivalente de Bootstrap Icons.
- **Botones:** `text-uppercase fw-bold + icono`
- **Tablas:** Siempre dentro de `.table-responsive`

## para la seccion guardar cancelar

La sección de botones de acción (Guardar/Cancelar) debe ir siempre **fuera** de los elementos `.card`, generalmente al final del formulario y utilizando la clase `.sticky-bottom` para asegurar que siempre sea accesible.

Si estás dentro de un sistema de rejilla (grid), **no lo envuelvas en un `col-12`**. Colócalo dentro de la columna que tiene el contenido principal (la más larga, ej. `col-md-8`), justo debajo del card:

```html
<div class="row">
  <div class="col-md-4">
    <div class="card">...</div>
  </div>
  <div class="col-md-8">
    <div class="card">...</div>
    
    <!-- Botonera dentro del col-8, fuera del card -->
    <div class="bg-body p-3 rounded d-flex justify-content-end gap-2 sticky-bottom mt-3">
      <a href="<?= admin_route("modulo") ?>" class="btn btn-outline-secondary px-4 text-uppercase fw-bold">
        <i class="fa-solid fa-arrow-left me-2"></i>
        Cancelar
      </a>
      <button type="submit" class="btn btn-primary px-5 text-uppercase fw-bold">
        <i class="fa-solid fa-floppy-disk me-2"></i>
        Guardar Cambios
      </button>
    </div>
  </div>
</div>
```

O simplemente después del card si no hay grid:

```html
<div class="card">
  <!-- Contenido -->
</div>

<div class="bg-body p-3 rounded d-flex justify-content-end gap-2 sticky-bottom mt-3">
  <a href="<?= admin_route('modulo') ?>" class="btn btn-outline-secondary px-4 text-uppercase small fw-bold">
    <i class="fa-solid fa-arrow-left me-2"></i>
    Volver
  </a>
  <button type="submit" class="btn btn-primary px-5 text-uppercase small fw-bold">
    <i class="fa-solid fa-floppy-disk me-2"></i>
    Guardar Cambios
  </button>
</div>
```

- Site el .card que le antecede tiene .mb-3 no poner .mt-3 a la botonera pero si no tiene si 
- Usa .sticky-bottom para los botones de guardar y cancelar.
- Nunca coloques este contenedor dentro de un .card-body o .card-footer.
- No uses .border si se va a usar .rounded
- No uses la clase .btn-group en columnas de acción de tablas de datos. Usa botones individuales separados para evitar colapsos visuales.

## para los input type password

Para campos de contraseña, usa estrictamente la siguiente estructura. El atributo `data-pr-toggle-password` se coloca directamente como atributo booleano en el elemento `input`. No requiere de un `input-group` ni de un `button` externo para el toggle.

```html
<input type="password" class="form-control" id="user_password" name="user_password" placeholder="Ingresar contraseña" data-pr-toggle-password>
```

## Prohibición de Iconos en Inputs

Está estrictamente prohibido el uso de `input-group` con `input-group-text` para mostrar iconos decorativos al inicio de los inputs (prepend). Los inputs deben ser limpios y directos.

**Incorrecto:**
```html
<div class="input-group">
  <span class="input-group-text bg-transparent"><i class="fa-solid fa-user"></i></span>
  <input type="text" class="form-control">
</div>
```

**Correcto:**
```html
<input type="text" class="form-control">
```

La única excepción de `input-group` es para el campo `password` con su botón de toggle al final (append), o cuando se requiere un botón de acción adjunto (ej. copiar API key).

## Inputs

Los formularios deben ser **consistentes, accesibles y predecibles**.  
Cada input define un contrato claro entre interfaz, validación y backend.

### Base obligatoria

```html
<div class="mb-3">
  <label for="user_email" class="form-label">Correo</label>
  <input type="email" class="form-control" id="user_email" name="user_email" required>
  <div class="invalid-feedback">Correo inválido</div>
</div>
```

**Reglas:**
- `label` obligatorio
- `for = id`
- `name` obligatorio
- `invalid-feedback` si hay validación
- `placeholder` es opcional, no reemplaza label

### Naming

Los nombres deben ser **predecibles y alineados a base de datos**.

**Formato:**
```
<entidad>_<campo>
```

Ej: `user_email`, `user_password`

Prohibido: `email`, `input1`, `txtEmail`

### Tipos correctos

`text | email | password | number | date | tel`

**Ejemplo:**
```html
<input type="tel" class="form-control" name="user_phone">
```

### Validación Visual

Bootstrap maneja estados con clases:

- `is-valid`
- `is-invalid`

**Ejemplo:**
```html
<input type="email" class="form-control is-invalid" name="user_email">
<div class="invalid-feedback">Correo inválido</div>
```

**Reglas:**
- Nunca mostrar error sin `is-invalid`
- Nunca usar alertas globales para errores de input
- Validación debe ser **campo por campo**

### Inputs Requeridos

Todo campo obligatorio debe declararse explícitamente:

```html
<input type="text" class="form-control" required>
```

**Adicional:**
- Marcar visualmente en label:
```html
<label class="form-label">Nombre <span class="text-danger">*</span></label>
```

### Agrupación de Inputs

Para inputs relacionados usar:

```html
<div class="row">
  <div class="col-md-6">
    <!-- input -->
  </div>
  <div class="col-md-6">
    <!-- input -->
  </div>
</div>
```

**Reglas:**
- Máximo 2–3 inputs por fila
- Evitar formularios verticales excesivamente largos

### Selects y Textareas

**Select:**
```html
<select class="form-select">
  <option value="">-- Seleccionar --</option>
</select>
```

**Textarea:**
```html
<textarea class="form-control" name=""></textarea>
```

**Reglas:**
- `select` siempre inicia con opción -- Seleccionar --

### Inputs Deshabilitados y Solo Lectura

```html
<input type="text" class="form-control" value="123" readonly>
<input type="text" class="form-control" disabled>
```

**Diferencia:**
- `readonly`: se envía en el formulario
- `disabled`: no se envía

### Consistencia Visual

**Reglas estrictas:**
- Usar siempre `form-control`, `form-select`
- No mezclar estilos personalizados innecesarios
- Mantener espaciado con `mb-3`
- No usar `<br>` para separar inputs

### Plugin Dropimg (Subida de Imágenes)
Para campos de imagen con el plugin `dropimg`, en vistas de edición se debe configurar la imagen previa mediante el atributo `data-default`:

```html
<input type="file" id="user_image" name="user_image" data-dropimg data-width="150" data-height="150" data-aspect="circle" data-default="<?= storage('uploads/user/' . ($user->user_image ?: 'default.webp')) ?>" accept="image/*">
```
Está prohibido incluir elementos `<img>` adicionales encima del input `dropimg`, ya que la librería gestiona automáticamente la previsualización con `data-default`.

### Navegación Interna de Páginas (Tabs / Submódulos)
Para menús de navegación interna entre pestañas o submódulos de un mismo apartado (ej. Perfil / Cambiar Contraseña), es **obligatorio** usar la estructura con contenedor `bg-body p-3 rounded my-3` y `nav nav-pills nav-justified`:

```html
<div class="bg-body p-3 rounded my-3">
  <ul class="nav nav-pills nav-justified">
    <li class="nav-item">
      <a class="nav-link active" href="/panel/account/profile.php">
        <i class="bi bi-person me-1"></i>
        Información de Perfil
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/panel/account/password.php">
        <i class="bi bi-key me-1"></i>
        Cambiar Contraseña
      </a>
    </li>
  </ul>
</div>
```

### Estructura de Vistas de Listado (Filtros, Tablas y Botones de Acción)
Para las vistas de listado de módulos (ej. Usuarios, Clientes, Productos):
1. **Separación Estricta de Contenedores**: No usar `.card`. Usar contenedores `bg-body p-3 rounded mb-3` independientes para:
   - **Filtros / Acciones Superiores**: `<div class="bg-body p-3 rounded mb-3 text-end">` con los botones de acción principal, separador `<hr class="my-2">` y formulario de filtros.
   - **Tabla de Datos**: `<div class="bg-body p-3 rounded mb-3">` con tabla responsiva `table table-hover align-middle table-sm m-0`.
   - **Paginación**: `<div class="bg-body p-3 rounded mb-3">` al final fuera de la tabla.
2. **Botones de Acción en Tablas**:
   - **Icono + Texto en Mayúsculas**: Deben incluir obligatoriamente su **Bootstrap Icon** (`bi bi-...`), texto explicativo y las clases `text-uppercase fw-bold text-nowrap` (ej: `<a class="btn btn-sm btn-outline-primary text-uppercase fw-bold text-nowrap"><i class="bi bi-pencil me-1"></i> Editar</a>`).
   - **Sin Saltos de Línea**: La celda de acciones debe ser estrictamente `<td class="text-end pe-3 text-nowrap">` y los botones deben estar envueltos en `<div class="d-flex justify-content-end gap-1">` para evitar colapsos o saltos de línea visuales.