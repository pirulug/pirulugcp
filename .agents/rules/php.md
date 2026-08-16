---
trigger: always_on
---

# Reglas de PHP

- **Etiquetas**: Siempre comenzar los archivos con `<?php`.
- **Tipado**: No usar type hints en parametros ni en retornos de funciones/metodos.
- **Documentacion**: Usar bloques DocBlock para documentar funciones y logica compleja.
- **Limpieza**: Eliminar codigo comentado innecesario y mantener la logica legible sin minificar.
- **Estructura**: Seguir la convencion de nombres camelCase para funciones y variables.

## Formateo y Nomenclatura
- **Sangría:** Usa ESTRICTAMENTE **2 espacios** por nivel de tabulación.
- **Variables:** Siempre en `snake_case`.
- **Funciones:** Siempre en `snake_case`.
- **Clases:** Siempre en `PascalCase`.
- **Constantes:** Siempre en `UPPER_SNAKE_CASE`.
- **Apertura de Llaves:** La llave de apertura `{` va en la misma línea que la declaración.

## Uso de exit
- Para detener la ejecución de un script y devolver un mensaje, se debe usar la funcion exit() con todo su parentesis y punto y coma al final, por ejemplo: exit() no solo exit;

## Para el redirect 
- **Redirecciones:** Usa siempre `header("Location: ...")` seguido OBLIGATORIAMENTE de `exit();`. No uses funciones envolventes como `redirect()`.
- **Ejemplo:**
  ```php
  header("Location: " . admin_route("users"));
  exit();

## Reglas de Comentarios

El sistema de comentarios existe para **reducir ambigüedad y preservar contexto**.  
Debe documentar **intención, contrato y decisiones**, nunca duplicar el código.

### Principio General

> **El código explica el "cómo".  
> El comentario explica el "por qué".**

Si un comentario no aporta contexto que no se puede inferir directamente del código, debe eliminarse.

### Comentarios de Funciones

Toda función debe definirse con un bloque `/** ... */` que establezca su **contrato explícito**.

**Objetivo:** permitir entender y usar la función sin leer su implementación.

**Estructura obligatoria:**
- Descripción clara de la intención (no trivial)
- Parámetros (`@param`: tipo + propósito)
- Retorno (`@return`: tipo + significado)
- Excepciones (`@throws`, si aplica)
- Efectos secundarios (si existen: I/O, DB, estado global)

**Reglas:**
- Usar tipado real de PHP + anotación consistente
- No describir paso a paso la implementación
- Ser específico, evitar términos genéricos como “procesa datos”

**Ejemplo:**
```php
/**
 * Registra un nuevo usuario en el sistema persistente.
 *
 * @param array $data Datos validados (login, password hash, email).
 * @return int ID del usuario creado.
 *
 * @throws DomainException Si el usuario ya existe.
 * @throws RuntimeException Si falla la persistencia.
 */
function create_user(array $data): int {
    // ...
}
```

### Separación de Bloques (Segmentación Semántica)

Los bloques de lógica deben delimitarse visualmente para mejorar navegación y mantenimiento.

**Formato obligatorio:**
```php
// -----------------------------------------------------------------------------
// SECCIÓN: NOMBRE DESCRIPTIVO
// -----------------------------------------------------------------------------
```

**Reglas:**
- Nombrar por intención, no por acción genérica
- Usar MAYÚSCULAS consistentes
- No abusar: solo en bloques relevantes (validación, reglas, persistencia, etc.)

**Ejemplo:**
```php
// -----------------------------------------------------------------------------
// SECCIÓN: VALIDACIÓN DE ENTRADA
// -----------------------------------------------------------------------------
if (empty($data['email'])) {
    throw new InvalidArgumentException('Email requerido');
}

// -----------------------------------------------------------------------------
// SECCIÓN: PERSISTENCIA
// -----------------------------------------------------------------------------
$user_id = insert_user($data);
```

### Comentarios Inline (Notas Operativas)

Se usan `//` para explicar **decisiones no obvias**, no el flujo evidente.

**Casos válidos:**
- Workarounds o hacks controlados
- Limitaciones técnicas o deuda conocida
- Dependencias externas frágiles
- Supuestos críticos

**Ejemplo correcto:**
```php
// Evitar doble hash: el password ya viene hasheado desde capa previa
$password = $data['password'];
```

**Ejemplo inválido:**
```php
// Asigna el password
$password = $data['password'];
```

### Prohibiciones (Ruido)

Está prohibido comentar:

- Código autoexplicativo
- Operaciones triviales (`if`, asignaciones simples)
- Repetición de nombres de variables
- Comentarios desactualizados o ambiguos

**Ejemplo incorrecto:**
```php
// Incrementa contador
$count++;
```

**Ejemplo correcto:**
```php
// Ajuste manual por inconsistencias en migración legacy
$count++;
```

### Consistencia y Mantenimiento

- Todo comentario debe mantenerse sincronizado con el código
- Si el código cambia y el comentario no, el comentario es inválido
- Priorizar eliminar comentarios antes que mantenerlos incorrectos

### Regla de Oro

> **Menos comentarios, pero de mayor valor semántico.**

## Estilo de Codificación (PHP Dinámico)
- **Sin Type Hints:** Las funciones NO deben tener declaraciones de tipo (`string`, `array`, `void`, etc.) ni en parámetros ni en el retorno (`: string`). Se prefiere un estilo dinámico y limpio.
- **Lógica No Minificada:** Mantener las validaciones y bloques de código legibles.
  - **Correcto:**
    ```php
    if (strpos($name, '/') === false){
      return '';
    }
    ```
  - **Incorrecto:** `if (strpos($name, '/') === false) return '';` (en una sola línea).
- **Separador de Módulos:** Usar siempre la barra diagonal (`/`) para invocar módulos y archivos en los helpers.

## Estructura Obligatoria de Consultas PDO

Todas las consultas a la base de datos con PDO deben seguir estrictamente la siguiente estructura:

```php
$query = "SELECT * FROM users WHERE user_id = :id LIMIT 1";
$stmt  = $connect->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_OBJ);
```

**Reglas:**
- La variable de la consulta SQL debe llamarse `$query`.
- La conexión a la base de datos debe almacenarse en la variable `$connect` (ej. `$connect = connect();`).
- La sentencia preparada debe almacenarse en la variable `$stmt` mediante `$connect->prepare($query)`.
- Los parámetros deben vincularse usando `$stmt->bindParam(':parametro', $variable)`.
- Se debe ejecutar `$stmt->execute()`.
- La obtención de un registro individual debe hacerse con `PDO::FETCH_OBJ` (ej. `$stmt->fetch(PDO::FETCH_OBJ)`).
- La obtención de múltiples registros debe hacerse con `PDO::FETCH_OBJ` (ej. `$stmt->fetchAll(PDO::FETCH_OBJ)`).

## Prohibición de Valores por Defecto o Descarte (`?:` / `??`)
- **Prohibido usar fallbacks implícitos:** Está estrictamente prohibido usar valores por defecto de descarte con el operador Elvis `?:` o el coalescente `??` para enmascarar propiedades o valores vacíos/nulos (ej: `$currency ?: 'PEN'`, `$unit ?: 'NIU'`, `$igv_type ?: 10`).
- **Justificación:** Los valores deben provenir directamente de la base de datos o de las estructuras previamente validadas. Asignar fallbacks improvisados enmascara inconsistencias en el origen de los datos o en los modelos.
- **Ejemplo Incorrecto:**
  ```php
  'tipo_moneda' => $sale->sale_currency ?: "PEN",
  'unidad'      => $detail->saledetail_product_unit ?: "NIU",
  ```
- **Ejemplo Correcto:**
  ```php
  'tipo_moneda' => $sale->sale_currency,
  'unidad'      => $detail->saledetail_product_unit,
  ```

## Prohibición de Uso de `htmlspecialchars()`
- **Prohibido usar `htmlspecialchars()`:** Está estrictamente prohibido usar la función `htmlspecialchars()` para escapar o imprimir valores de variables o cadenas en las plantillas y vistas PHP.
- **Justificación:** Los valores deben imprimirse directamente utilizando las variables o expresiones validadas limpias.
- **Ejemplo Incorrecto:**
  ```php
  value="<?= htmlspecialchars($default_client->doc_number) ?>"
  ```
- **Ejemplo Correcto:**
  ```php
  value="<?= $default_client->doc_number ?>"
  ```