-- To Quran app DB patch: add launch family workspace permissions for admin operations.
-- Target: u504065335_to_quran (intentional app/LMS target for accelerated local launch path)
-- Backup evidence required before execution:
--   database/manual/backups/2026-06-04-u504065335_to_quran-before-family-workspace-permissions.sql

SET @expected_database := 'u504065335_to_quran';
SET @operator_confirm_tq_app_target := 'u504065335_to_quran';
SET @guard_ok := (
    SELECT DATABASE() = @expected_database
       AND @operator_confirm_tq_app_target = @expected_database
       AND EXISTS (
           SELECT 1
           FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'permissions'
       )
       AND EXISTS (
           SELECT 1
           FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'roles'
       )
       AND EXISTS (
           SELECT 1
           FROM information_schema.TABLES
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'role_has_permissions'
       )
);

SELECT IF(
    @guard_ok,
    'Applying family workspace permissions patch to To Quran app DB.',
    CONCAT('REFUSING family workspace permissions patch. Connected database is ', COALESCE(DATABASE(), '<none>'), '.')
) AS guard_result;

INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT permission_name, 'web', NOW(), NOW()
FROM (
    SELECT 'families.view_workspace' AS permission_name UNION ALL
    SELECT 'families.activate' UNION ALL
    SELECT 'families.suspend' UNION ALL
    SELECT 'families.reactivate' UNION ALL
    SELECT 'families.archive' UNION ALL
    SELECT 'families.restore' UNION ALL
    SELECT 'families.children.activate' UNION ALL
    SELECT 'families.children.suspend' UNION ALL
    SELECT 'families.children.reactivate' UNION ALL
    SELECT 'families.children.archive' UNION ALL
    SELECT 'families.children.restore' UNION ALL
    SELECT 'families.history.view' UNION ALL
    SELECT 'families.credentials.reveal' UNION ALL
    SELECT 'families.credentials.send_reset_link' UNION ALL
    SELECT 'families.credentials.generate_password' UNION ALL
    SELECT 'families.credentials.resend_activation'
) AS launch_permissions
WHERE @guard_ok
  AND NOT EXISTS (
      SELECT 1
      FROM permissions existing_permissions
      WHERE existing_permissions.name = launch_permissions.permission_name
        AND existing_permissions.guard_name = 'web'
  );

INSERT INTO role_has_permissions (permission_id, role_id)
SELECT permissions.id, roles.id
FROM permissions
JOIN roles ON roles.name = 'admin' AND roles.guard_name = 'web'
LEFT JOIN role_has_permissions existing_role_permission
    ON existing_role_permission.permission_id = permissions.id
   AND existing_role_permission.role_id = roles.id
WHERE @guard_ok
  AND permissions.guard_name = 'web'
  AND permissions.name IN (
      'families.view_workspace',
      'families.activate',
      'families.suspend',
      'families.reactivate',
      'families.archive',
      'families.restore',
      'families.children.activate',
      'families.children.suspend',
      'families.children.reactivate',
      'families.children.archive',
      'families.children.restore',
      'families.history.view',
      'families.credentials.reveal',
      'families.credentials.send_reset_link',
      'families.credentials.generate_password',
      'families.credentials.resend_activation'
  )
  AND existing_role_permission.permission_id IS NULL;

SELECT permissions.name
FROM permissions
JOIN role_has_permissions ON role_has_permissions.permission_id = permissions.id
JOIN roles ON roles.id = role_has_permissions.role_id
WHERE roles.name = 'admin'
  AND roles.guard_name = 'web'
  AND permissions.name LIKE 'families.%'
ORDER BY permissions.name;
