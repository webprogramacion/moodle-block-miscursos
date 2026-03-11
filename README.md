# block_miscursosdashboard

Bloque de Moodle para mostrar, dentro del Dashboard del usuario, sus cursos matriculados con opciones de ordenacion, visualizacion de fechas de matricula y dos modos de disposicion.

## Compatibilidad

- Moodle 5.0.x
- Moodle 5.1.x

## Instalacion

1. Copia la carpeta `block_miscursosdashboard` dentro de `moodle/blocks/`.
2. Ve a **Administracion del sitio > Notificaciones** para completar la instalacion.
3. En el Dashboard, activa edicion y agrega el bloque **Mis cursos**.

## Uso

- El bloque lista los cursos del usuario autenticado.
- Cada curso incluye nombre completo y enlace.
- Puedes ordenar por:
  - Nombre del curso
  - Fecha de matricula (ascendente o descendente)
- En la configuracion de cada instancia puedes:
  - Mostrar/ocultar fecha de inicio de matricula
  - Mostrar/ocultar fecha de fin de matricula
  - Elegir modo de disposicion:
    - Listado (sin foto)
    - Filas y columnas (con foto)

## Decision tecnica para multiples matriculas

Cuando un usuario tiene varias matriculas en el mismo curso:

- Se usa la **fecha de inicio mas temprana** (`MIN(timestart)`) como inicio de matricula.
- Se usa la **fecha de fin mas tardia** (`MAX(timeend)`) como fin de matricula.

Las fechas vacias o no definidas se muestran como "No disponible" y no producen errores.

## Nota de empaquetado

- No generar archivo `.zip` automaticamente para este plugin.
- El empaquetado `.zip` lo realizo manualmente para evitar archivos corruptos en Moodle.
