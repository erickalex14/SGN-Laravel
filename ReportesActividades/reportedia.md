# Informe Diario de Actividades: Migración de Sistema Legacy a Laravel

**Fecha:** 15 de mayo de 2026
**Área:** Desarrollo de Software / Arquitectura Backend
**Objetivo del Día:** Análisis estructural de la base de datos de producción (Legacy) y generación estricta de Modelos Eloquent en Laravel para garantizar la compatibilidad retroactiva durante la fase de migración.
**Responsable:** Erick Alexander Chavarrea Macias

---

## 1. Resumen Ejecutivo

Durante la jornada, se procesó el esquema estructural completo de la base de datos MySQL de producción (`novitecdb_pruebas`). El trabajo consistió en la abstracción de las tablas legacy hacia el ORM de Laravel (Eloquent), respetando al 100% la nomenclatura existente para asegurar que el nuevo sistema pueda operar en paralelo con la base de datos actual sin romper consultas SQL, reportes ni flujos de datos.

Se categorizó la arquitectura del software en cuatro dominios principales:
1. **Directory:** Gestión de clientes, empresas y sucursales.
2. **Identity:** Autenticación, roles, permisos y estructura de notificaciones.
3. **Inventory:** Catálogo de productos, repuestos y marcas.
4. **Operations:** Core del negocio (equipos, órdenes, preórdenes e informes técnicos).

## 2. Métricas de Desarrollo

* **Tablas analizadas:** 31 tablas (excluyendo vistas temporales).
* **Modelos Eloquent generados:** 30 modelos principales y pivotes.
* **Líneas de código SQL procesadas (Estructura base):** ~540 LOC.
* **Líneas de código PHP (Modelos) escritas:** ~950 LOC.
* **Tiempo de reestructuración del esquema:** Completado dentro de la jornada.

## 3. Metodología Aplicada

Para mantener la integridad de los datos, se establecieron las siguientes directrices técnicas en la creación de los modelos:
* Desactivación de las convenciones de nomenclatura estándar de Laravel (`snake_case` plural automático) mediante la definición explícita de `$table` y `$primaryKey`.
* Configuración explícita de `$timestamps` (asignación de variables `public const CREATED_AT` y `UPDATED_AT` mapeadas a nombres en español como `creado_en`, o deshabilitación total en tablas que carecen de ellas).
* Mapeo estricto de relaciones relacionales (`belongsTo`, `hasMany`, `belongsToMany`) basándose en las restricciones de claves foráneas (FK) presentes en el motor InnoDB.

## 4. Evidencia de Trabajo (Muestra de Conversión)

A continuación, se detalla un ejemplo representativo del análisis y la conversión realizada.

### Estructura SQL Original (Legacy):
```sql
CREATE TABLE `cas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `marca` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

```
### Modelo Eloquent Generado (Laravel):
```php
<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Operations\Orden;

class Cas extends Model
{
    protected $table = 'cas';
    protected $primaryKey = 'id';
    
    // Mapeo de timestamps en español
    public const CREATED_AT = 'creado_en';
    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre', 'marca', 'telefono', 'correo', 'direccion', 
        'ciudad', 'contacto', 'notas', 'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'cas_id', 'id');
    }
}
```
## 5. Observaciones Arquitectónicas y Deuda Técnica Detectada
Durante el análisis del esquema, se identificaron ciertas inconsistencias en el diseño legacy que requerirán atención en fases posteriores de la migración (identificadas en el código como RELATION_REQUIRES_CONFIRMATION):

Tipos de Datos Divergentes: En la tabla repuestos, las columnas marca_id y tipo_dispositivo_id están definidas como varchar(36), mientras que las claves primarias de las tablas destino (marcas y tiposdispositivo) son int unsigned AUTO_INCREMENT.

Ausencia de Claves Foráneas Estrictas: Tablas como solicitudesrepuesto infieren relaciones a través del nombre de la columna (ej. orden_id), pero carecen del CONSTRAINT explícito en la base de datos a nivel de motor.

### Siguientes Pasos:
A partir de la base de los modelos generados, el siguiente paso será iniciar el desarrollo de la capa de controladores y el enrutamiento de la nueva API REST, asegurando la conexión exitosa y la lectura correcta de los registros actuales.

## 6. Repositorio evidencia de lineas de codigo escritas hoy

* 69 Archivos modificados, 3209 Lineas de codigo hoy dia, 241 lineas borradas.
* [Repositorio GitHub - Modelos Eloquent Generados](https://github.com/erickalex14/SGN-Laravel/tree/dev)
