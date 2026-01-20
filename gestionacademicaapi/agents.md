# Agents Plan

## Estado actual
- Auth JWT listo (firewall stateless + JwtAuthenticator).
- Rutas unificadas a atributos en español; config/routes/api.php vacío.

## Roles objetivo
- ROLE_ADMIN: CRUD global.
- ROLE_DOCENTE: CRUD cursos/curso-asignaturas/calificaciones/reportes de sus cursos; chats donde participa.
- ROLE_ESTUDIANTE: Lectura de sus matrículas/calificaciones/curso-asignaturas/chats propios.
- ROLE_PADRE: Lectura de matrículas/calificaciones de hijos (requiere vínculo); chats donde participa.

## Próximos pasos
1) Arreglar UserRoleController: usar roles desde TokenInterface (`$this->getUser()->getRoles()`) y validar ROLE_ADMIN.
2) Definir access_control inicial en security.yaml por prefijos (ADMIN escritura global; DOCENTE escritura académica; lectura a revisar por filtros).
3) Filtros de lectura en servicios: calificaciones/matrículas/chats/mensajes por usuario (estudiante/padre/docente) y evitar findAll abierto.
4) Normalizar campos de request (id_usuario → id_user, nombre → titulo donde aplique).
5) Seeds de roles base (admin/docente/estudiante/padre) y admin inicial.

## Notas
- User implementa UserInterface; roles se cargan desde JWT en JwtAuthenticator.
- Sin filtros actuales: los GET exponen toda la data.