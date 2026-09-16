# Safe cPanel Deployment Notes

This project is intended to deploy custom WordPress code to shared hosting with cPanel Git Version Control.

Normal deployments should copy only source-controlled custom code:

- `wp-content/plugins/art-cms/`
- `wp-content/themes/art-theme/`

Normal deployments must not copy or overwrite:

- WordPress core
- `wp-config.php`
- production database content
- `wp-content/uploads/`
- cache directories
- logs
- backups
- secrets or API keys

## Deployment Template

The file `deployment/cpanel.yml.example` is a safe example, not an active deployment file.

Before using it on production:

1. Confirm the real cPanel account path.
2. Copy the example to `.cpanel.yml` in the production deployment repository.
3. Replace `CPANEL_USER` with the real cPanel username.
4. Review every copy target carefully.
5. Confirm that production uploads and database content are not part of the deployment tasks.

The example intentionally avoids delete operations. Removing files from production should be handled as an explicit deployment decision, not as a default template behavior.

Do not hardcode real production paths or secrets in this public repository.

## Initial Launch

Initial launch may later require a one-time migration of database content and uploads.

That migration is separate from normal Git deployment and should be planned, backed up, and executed explicitly.
