# Walkthrough - Alertas de Antigüedad de Órdenes y Cierre de Garantías por Nota de Crédito

Hemos completado e implementado en producción dos grandes flujos del sistema:
1. **Notificaciones y Alertas Automáticas para Técnicos y Clientes** por antigüedad de órdenes.
2. **Cierre Condicional por Transferencia de Notas de Crédito** para órdenes de garantía personal, incluyendo soporte de subestados en reportes y exportaciones.

---

## 🛠️ Cambios Realizados

### 1. Notificaciones y Alertas Automáticas (Antigüedad de Órdenes)
- **Comando Artisan (`app/Console/Commands/VerificarAntiguedadOrdenes.php`)**:
  - Corre diariamente para verificar las órdenes personales asignadas hace exactamente 3 y 5 días.
  - **A los 3 días**: Envía una notificación del sistema (pop-up) al técnico asignado.
  - **A los 5 días**: Envía una notificación del sistema al técnico y envía un correo electrónico formateado al cliente indicando que su orden demorará más de lo previsto.
- **Base de Datos (`database/migrations/2026_06_29_171818_alter_notificaciones_tipo_column.php`)**:
  - Modifica la columna `tipo` de la tabla `notificaciones` a `VARCHAR(50)` para soportar los nuevos identificadores de notificaciones (`orden_asignada`, `orden_antiguedad_3d`, `orden_antiguedad_5d`).
- **Controlador de Órdenes (`app/Http/Controllers/Operations/OrdenController.php`)**:
  - Dispara notificaciones pop-up de asignación en tiempo real al técnico cuando se le asigna o reasigna una orden personal.

### 2. Cierre Condicional por Transferencia (Garantías en Nota de Crédito)
- **Base de Datos (`database/migrations/2026_06_29_180714_add_transferencia_to_ordenes_table.php`)**:
  - Agrega las columnas `transferencia_plataforma` (string, 50, nullable) y `transferencia_numero` (string, 100, nullable) a la tabla `ordenes`.
  - Actualiza la vista consolidada `vista_ordenes` para proyectar estas nuevas columnas.
- **Lógica de Transiciones (`app/Services/Operations/GestionOrdenService.php` y `app/Services/Operations/ActualizarOrdenService.php`)**:
  - Evita que las órdenes personales de tipo `Validacion de Garantia` se cierren automáticamente (manteniendo `fecha_finalizacion` en `null`) al pasar a `Nota de Credito` si no tienen número de transferencia.
- **Modelo Orden (`app/Models/Operations/Orden.php`)**:
  - Define campos fillable y expone mediante `$appends` el estado actual de la Nota de Crédito (`nc_estado`, `nc_motivo_rechazo`) desde su relación `solicitudesNc`.
- **Flujo del Técnico (Mis Órdenes)**:
  - **Popup SweetAlert2**: Mapea en el login/carga del listado si existe alguna orden de garantía en Nota de Crédito aprobada que requiera número de transferencia, desplegando un modal flotante interactivo con opción de elegir plataforma (`MBA3`, `Milenium`, `Otros`) e ingresar el número de transferencia.
  - **Panel lateral de detalles**: Agrega la sección de registro y visualización de la transferencia.
  - **Endpoint de Registro (`MisOrdenesController@registrarTransferencia`)**: Recibe y valida los datos, finaliza la orden (seteando `fecha_finalizacion` con hora de Guayaquil) y registra la actividad diaria del técnico.
- **Flujo del Administrador (Editar Orden)**:
  - Agrega el bloque de campos de plataforma y número de transferencia en `editar.blade.php`. Se visualiza y edita si la orden es de garantía en estado Nota de Crédito.
  - Modifica `EdicionOrdenController@update` y `ActualizarOrdenDTO` para pasar estos valores al servicio `ActualizarOrdenService` y poder cerrar la orden desde el panel administrativo.
- **Módulo de Reportes e Imprimibles (`app/Repositories/Operations/OrdenRepository.php` y `resources/views/operations/reportes/index.blade.php`)**:
  - Mapea dinámicamente el estado para reportes:
    - **`NC Aprobada-Abierta`**: NC aprobada pero sin número de transferencia.
    - **`NC Aprobada-Cerrada`**: NC aprobada con número de transferencia registrado.
  - Incluye la columna visual **Transferencia** en la tabla de reportes HTML.
  - Exporta los campos de plataforma y número de transferencia en las utilidades de descarga **CSV** y **XLSX** (ExcelJS).

---

## 🧪 Pruebas Automatizadas

Hemos escrito y corrido satisfactoriamente las siguientes suites de pruebas:
1. **`tests/Feature/NotificacionesFlujoTest.php`**: Verifica la generación de notificaciones por asignación, alertas automáticas de 3 y 5 días, y envío de correos.
2. **`tests/Feature/NotaCreditoCierreTest.php`**: Valida que las garantías en Nota de Crédito sigan abiertas, el técnico registre la transferencia, se asigne la fecha de finalización y se reporten los subestados correctos.

### Resultados de Tests:
Toda la suite del proyecto (55 pruebas) pasa de manera exitosa:
```bash
   PASS  Tests\Feature\NotaCreditoCierreTest
  ✓ las ordenes de tipo garantia no se cierran automaticamente al pasar a Nota de Credito                        1.01s  
  ✓ las ordenes de tipo servicio comun si se cierran automaticamente al pasar a Nota de Credito                  0.32s  
  ✓ el tecnico puede registrar la transferencia para cerrar la orden de garantia                                 0.39s  
  ✓ los reportes muestran subestados correctos segun el numero de transferencia en garantias                    11.32s  

  Tests:    55 passed (204 assertions)
  Duration: 46.73s
```

---

## 🚀 Despliegue a Producción

El despliegue se ha ejecutado y validado en el servidor de producción:
- **Sincronización de Archivos**: Los archivos modificados y nuevos fueron subidos exitosamente vía SFTP al servidor remoto `/home/novitecadmin/novitec-stack/novitec-sgn`.
- **Recompilación de Docker**: Se ejecutó la reconstrucción y recreación del contenedor `novitec-sgn` en producción.
- **Migraciones**: Se aplicó con éxito la migración `2026_06_29_180714_add_transferencia_to_ordenes_table` en la base de datos de producción.
- **Limpieza de Caché**: Se limpió la caché de configuración, rutas, vistas y eventos del framework Laravel en producción.
