# Auditoría: paridad Flutter (`autopsia_ti_app`) vs Laravel (matriz)

Mapa de cada pantalla real de Laravel contra su equivalente en Flutter, con el estado de fidelidad verificado hasta el momento. Se actualiza a medida que se audita cada pantalla contra el código fuente real (controlador + vista Blade), nunca contra suposiciones.

**Cómo leer el estado:**
- ✅ **Verificado** — se leyó el controlador/vista real, se corrigió lo necesario en Flutter, y se probó (curl contra el servidor y/o build nativo).
- ⚠️ **Parcial** — existe en Flutter pero con alcance reducido a propósito (documentado el motivo), o quedó pendiente un detalle menor.
- ❌ **No implementado** — no existe todavía en Flutter.
- 🚫 **Excluido a propósito** — decisión consciente de no replicarlo (workflow de oficina/admin, requiere sesión web, o depende de conexión en vivo). No es un olvido.
- ❓ **No revisado** — todavía no se leyó el código real de Laravel para esta pantalla. Puede haber discrepancias sin descubrir.

---

## Plataforma / navegación

| Pantalla real (ruta Laravel) | Flutter | Estado | Notas |
|---|---|---|---|
| Login (`/autopsia_ti/login`) | `login_screen.dart` | ✅ | Sanctum, con fallback offline por sesión previamente verificada. |
| Redirección por rol (`/`) | `main_campo_screen.dart` (menú fijo) | ⚠️ | Laravel redirige a pantallas distintas según rol (admin→dashboard, operador→monitoreo, visor_cronograma→cronograma). Flutter muestra siempre el mismo menú lateral completo sin ocultar ítems por rol (salvo el caso puntual de filtros del listado de actas, que sí respeta `operador`). |
| Mi Perfil (`usuario.perfil` / `.perfil.update`) | `tabs/perfil_tab.dart` | ✅ | Formulario real (nombres/apellidos/correo/contraseña) + endpoint `PUT /v1/perfil` propio. |

## Dashboard

| Pantalla real | Flutter | Estado | Notas |
|---|---|---|---|
| Mapa de Diagnóstico Situacional (`usuario.dashboard.general`, admin) | `tabs/dashboard_tab.dart` | ✅ | Filtros en cascada, "Ver solo mapa" en pantalla completa real (Overlay), auto-encuadre, KPIs. |
| Dashboard de Equipos (`usuario.dashboard.equipos` + 6 rutas AJAX de filtros) | — | ❓ | **No revisado todavía.** Es una pantalla separada del mapa (probablemente estadísticas/gráficos de inventario de equipos de cómputo). No existe nada en Flutter hoy. |

## Actas de Diagnóstico Situacional (monitoreo)

| Pantalla real | Flutter | Estado | Notas |
|---|---|---|---|
| Listado (`usuario.monitoreo.index`) | `tabs/actas_monitoreo_listado_tab.dart` | ✅ | 9 columnas reales, filtros, KPIs, cálculo de "módulos firmados" portado línea por línea del `@php` del Blade. |
| Crear acta (`usuario.monitoreo.create` / `.store`) | `nueva_acta_form_screen.dart` | ✅ | Un solo formulario con buscador de establecimiento embebido (igual que el autocomplete real), equipo mínimo 1, implementador/responsable/categoría/fecha/pozo/panel/fotos. |
| Editar acta (`usuario.monitoreo.edit` / `.update`, `EditMonitoreoController`) | — | ❓ | **No revisado.** Es una pantalla de edición de la CABECERA del acta (fecha/responsable/etc.) distinta de "gestionar módulos". Hoy en Flutter no hay forma de editar estos campos después de creada el acta (solo los módulos). |
| Gestionar módulos (`usuario.monitoreo.modulos`, activar/desactivar módulos) | `acta_detalle_screen.dart` | ⚠️ | Flutter muestra RR.HH. + consultorios dinámicos + croquis, pero **no replica el toggle de qué módulos están "activos"** para el acta (`config_modulos`, ruta `.toggle`) — en Flutter no hay concepto de módulos habilitados/deshabilitados, todos los consultorios dinámicos que se crean quedan implícitamente activos. |
| Salud Mental (`monitoreo.salud_mental_group.index`) | — | ❓ | **No revisado.** Grupo especial de 7 submódulos, ya apareció en el cálculo de "módulos firmados" del listado pero nunca se auditó la pantalla en sí. |
| Cambiar autor (`monitoreo.cambiar-autor`) | — | 🚫 | Lista fija de 5 DNIs hardcodeados en el propio Laravel, solo admin. Bajo prioridad. |
| Subir PDF firmado por módulo (`FirmasMonitoreoController@subir/ver`) | — | 🚫 | Workflow de oficina (subir PDF escaneado), requiere conexión. |
| PDF consolidado / envío por correo (`generarPDF`, `enviarCorreo`, `getEquipoEmails`) | — | 🚫 | Requiere sesión web autenticada para el PDF; envío de correo es workflow de oficina. |
| Consultorios dinámicos (`consultorio.crear/show/store/renombrar/destroy/pdf`) | `modulos/consultorio_dinamico_screen.dart` | ✅ | Crear/editar/eliminar consultorios con nombre libre, verificado en sesiones anteriores. **Falta**: `consultorio.renombrar` (cambiar el nombre de un consultorio ya creado) — hoy en Flutter parece no tener esa acción. |
| RR.HH. (`modulo/rrhh`) | `modulos/rrhh_screen.dart` | ✅ | Verificado en sesión anterior. |
| Infraestructura 2D / Croquis (`modulo/infraestructura-2d`) | `modulos/croquis_editor_screen.dart` | ✅ | Editor completo (5 fases). Colaboración en tiempo real excluida a propósito (no viable offline). |
| Descargar datos offline / sincronizar lote (`monitoreo.offline.*`) | `services/sync_service.dart`, `OfflineSyncController` | ✅ | Corregido bug de pérdida de datos (categoría/pozo/panel/fotos) esta sesión. |

## Establecimientos

| Pantalla real | Flutter | Estado | Notas |
|---|---|---|---|
| Listado + edición (`usuario.establecimientos.*`) | `tabs/establecimientos_tab.dart`, `establecimiento_edit_screen.dart` | ✅ | 6 secciones reales, Consultar RENIPRESS. |
| Actualizar coordenadas por mapa (`establecimientos.coordenadas`, drag del pin) | — | ❓ | La edición de lat/long existe como campos de texto en el formulario, pero no el mapa interactivo (Leaflet) de la web real para arrastrar el pin. |

## Actas de Reunión

| Pantalla real | Flutter | Estado | Notas |
|---|---|---|---|
| Listado/crear/editar (`usuario.reuniones.*`) | `tabs/reuniones_tab.dart`, `reunion_form_screen.dart` | ✅ | Formulario completo, verificado. |
| QR de asistencia (`activar_asistencia`, `asistencia.*` público) | — | 🚫 | Depende de sondeo en vivo al servidor, no viable offline. |
| Firma visual / subir PDF firmado (`visual-signature`, `subirPDF`) | — | 🚫 | La "firma visual" ni siquiera tiene controlador real en Laravel (ruta rota). Subir PDF es workflow de oficina. |

## Gestión de Usuarios (admin)

| Pantalla real | Flutter | Estado | Notas |
|---|---|---|---|
| CRUD (`admin.users.*`) | `tabs/gestionar_usuarios_tab.dart` | ✅ | Crear/editar/activar-bloquear, verificado end-to-end. |
| Eliminar usuario (`admin.users.destroy`) | — | 🚫 | Ruta muerta en el propio Laravel (sin controlador ni botón real). No debe implementarse. |
| Banco de Firmas (`admin.firmas.*`) | — | ❓ | **No revisado.** Pantalla separada de administración de firmas digitales de profesionales. No existe nada en Flutter. |
| Dashboard Admin (`admin.dashboard`, distinto del dashboard de `usuario`) | — | ❓ | **No revisado.** Hay un `AdminController::index` con su propia vista (`admin.dashboard.dashboard`) que no se ha comparado contra el Dashboard que ya tiene Flutter — podrían ser la misma información o dos cosas distintas. |

## Reportes (admin / visor_cronograma)

| Pantalla real | Flutter | Estado | Notas |
|---|---|---|---|
| Equipos, consultorios-medicina, actas-monitoreo, DNIe, cronograma, auditoría (consistencia/equipos/duplicidad) — 8 pantallas bajo `usuario.reportes.*` | — | 🚫 | Decisión ya tomada con el usuario: se elimina la pestaña "Reportes" completa de Flutter — son generación de Excel/PDF admin-only, no tienen sentido offline. |

## Firma móvil (independiente de Actas de Reunión)

| Pantalla real | Flutter | Estado | Notas |
|---|---|---|---|
| `FirmaMovilController` (`/firmar/{token}`, QR, status) | — | 🚫 | Mecanismo de firma a mano vía celular con caché temporal (TTL 600s), no conectado a ningún módulo ya auditado. Fuera de alcance salvo que se identifique dónde se usa realmente. |

---

## Resumen de prioridad sugerida para lo que falta

1. **Dashboard de Equipos** (❓) — pantalla completa sin revisar, podría ser grande.
2. **Editar cabecera de acta** (`EditMonitoreoController`) (❓) — gap funcional real: hoy no se puede corregir fecha/responsable/etc. de una acta ya creada.
3. **Toggle de módulos activos por acta** (`config_modulos`) (⚠️) — afecta directamente el cálculo de "módulos firmados" que ya se replicó; sin esto, el conteo puede no coincidir con la web en actas donde el operador desactivó módulos a mano.
4. **Renombrar consultorio dinámico** (⚠️) — falta menor, ya identificado.
5. **Dashboard Admin** y **Banco de Firmas** (❓) — confirmar primero si son relevantes para el flujo de campo antes de invertir tiempo.

Todo lo marcado 🚫 es una decisión ya conversada con el usuario o una consecuencia directa de que la funcionalidad real depende de conexión en vivo / sesión web — no requiere retrabajo salvo que cambien los requisitos.
