---
name: database-integrity
description: Audita la integridad estructural del dominio en base de datos MySQL 8+ sin modificar el esquema ni ejecutar migraciones.
---

# Database Integrity Skill

Audita la integridad estructural del dominio en base de datos (MySQL 8+) sin modificar el esquema ni ejecutar migraciones.

## Instrucciones

1. Evaluar que el modelo relacional este alineado con las reglas del dominio.
2. Validar Foreign Keys (ON DELETE / ON UPDATE).
3. Verificar columnas NOT NULL y ausencia de columnas legacy.
4. Analizar indices y performance de queries frecuentes.
5. Asegurar coherencia entre Eloquent y MySQL.
