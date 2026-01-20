# Gestion Academica API

## Datos de prueba (seed demo)

Comando:

```bash
php bin/console app:seed-demo-data
```

Qué crea:
- Roles base (admin, docente, estudiante, padre) si faltan.
- Usuarios demo con password `Demo12345`:
  - docente@demo.com
  - estudiante@demo.com
  - padre@demo.com
- Periodo lectivo 2025-2026.
- Curso "Curso Demo A" (docente titular: docente@demo.com).
- Asignatura "Matemáticas" y su CursoAsignatura.
- Matrícula del estudiante en el curso/periodo.
- Calificación de ejemplo para esa matrícula/asignatura.
- Datos familiares con representante vinculado al usuario padre.
- Chat del curso con los tres usuarios y un mensaje inicial.

Notas de uso por rol (API protegida con JWT):
- Admin: acceso completo; puede gestionar chats y asignar chat-usuarios.
- Docente: puede crear/editar calificaciones y matrículas de su curso; ve chats/mensajes donde participa.
- Estudiante: solo lectura de sus matrículas/calificaciones; ve chats/mensajes donde participa.
- Padre: lectura de matrículas/calificaciones del estudiante vinculado en DatosFamiliares; ve chats/mensajes donde participa.

Siguiente paso cuando haya frontend: usar las credenciales de arriba para obtener JWT y validar la visibilidad por rol en los endpoints `/api/calificaciones`, `/api/matriculas`, `/api/chats`, `/api/mensajes`.
