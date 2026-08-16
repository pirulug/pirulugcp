---
trigger: always_on
---

# Reglas Generales

- **Indentacion**: Usar siempre 2 espacios de tabulacion para todos los lenguajes (excepto Python).
- **Idioma**: Responder exclusivamente en español.
- **Formato**: Responder siempre en texto plano.
- **Emojis**: No usar emojis en ninguna parte de la respuesta.
- **Comunicacion**: Mantener respuestas concisas y directas.
- **Comillas**: Usar preferentemente comillas dobles (`"`) en primer lugar para las cadenas de texto. El uso de comillas simples (`'`) se reserva para cuando están anidadas dentro de comillas dobles.

## Prohibición de Valores por Defecto o Descarte (`||` / `??` / `?:`)
- **Prohibido usar fallbacks implícitos:** Está estrictamente prohibido usar valores por defecto de descarte con los operadores `||`, `??` o `?:` para enmascarar propiedades o valores vacíos/nulos (ej. en JS: `data.emisor_ruc || "20131312955"`, `data.description || "-"`, en PHP: `$currency ?: "PEN"`).
- **Justificación:** Los valores deben provenir directamente del modelo de datos o de las estructuras validadas. Asignar fallbacks improvisados enmascara inconsistencias en el origen de los datos.
- **Ejemplo Incorrecto (JS):**
  ```js
  emisorEl.textContent = data.emisor_ruc || "20131312955";
  ```
- **Ejemplo Correcto (JS):**
  ```js
  emisorEl.textContent = data.emisor_ruc;
  ```

## Prohibición de Relleno de Ceros a Correlativos (`str_pad`)
- **Prohibido rellenar correlativos con ceros a la izquierda:** Está estrictamente prohibido usar `str_pad()` o formateos para rellenar ceros a la izquierda en los números correlativos de comprobantes (ej. `F001-00000001` o `B001-00000009`).
- **Formato Obligatorio:** Los correlativos deben mostrarse directamente sin ceros agregados artificialmente (ej: `$series . "-" . $correlativo` -> `F001-1`, `B001-9`, `NV01-1`).
- **Ejemplo Incorrecto:**
  ```php
  $doc_series_num = $item->factura_series . "-" . str_pad($item->factura_correlativo, 8, "0", STR_PAD_LEFT);
  ```
- **Ejemplo Correcto:**
  ```php
  $doc_series_num = $item->factura_series . "-" . $item->factura_correlativo;
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

### Iconografía Obligatoria (Bootstrap Icons)
Es **estrictamente obligatorio** utilizar únicamente **Bootstrap Icons** (`bi bi-...`) en toda la interfaz de usuario (botones, tablas, tarjetas, menús y modales).

- Si en el código existen o se encuentran otros tipos de iconos (tales como FontAwesome `fa-solid`, `fa-regular` o etiquetas `<svg>` manuales), se deben reemplazar **obligatoriamente** por su equivalente en Bootstrap Icons.