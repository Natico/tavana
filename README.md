# ART WordPress Project

ART is a classic WordPress project prepared for local development, Git-based source control, and future deployment to shared hosting with cPanel.

This repository tracks only custom project source code and documentation. It does not track WordPress core, local configuration, uploads, cache, secrets, or production content.

## Local Environment

- Local URL: `http://art-site.local`
- Local WordPress path: `/var/www/art-site`
- PHP requirement: PHP 8.3
- Database: MySQL 8.0
- Web server: Apache

WordPress is already installed locally. Do not reinstall WordPress, delete the existing installation, or modify WordPress core files as part of normal development.

## Project Structure

Custom content and business logic currently lives in:

```text
wp-content/plugins/art-cms/
```

The future custom presentation theme is planned for:

```text
wp-content/themes/art-theme/
```

Project planning and prompt documentation lives in:

```text
docs/
```

## Git Scope

Git should contain only source-controlled project files, such as:

- `wp-content/plugins/art-cms/`
- future `wp-content/themes/art-theme/`
- `docs/`
- `.gitignore`
- `README.md`
- safe deployment documentation or templates that contain no secrets

Git must not contain:

- WordPress core files
- `wp-config.php`
- database dumps
- passwords, API keys, or other secrets
- `wp-content/uploads/`
- cache, logs, generated backups, or temporary files
- third-party plugins and default WordPress themes

Always check `git status` before committing and confirm that only intended custom project files are staged.

## Development Workflow

Use `main` as the stable branch.

For each feature:

1. Start from a clean `main`.
2. Create a feature branch, for example `feature/project-readme`.
3. Make a focused change.
4. Run relevant checks, such as PHP syntax checks for PHP files.
5. Commit and push the feature branch.
6. Merge back to `main` after review or approval.
7. Push `main`.

Keep each branch small enough to review and reason about independently.

## Deployment Model

The future production target is shared hosting with cPanel Git Version Control.

Normal deployments should move only custom source code from Git to production, for example:

```text
wp-content/plugins/art-cms/
wp-content/themes/art-theme/
```

Production database content must not be overwritten during normal code deployments.

Production uploads must not be overwritten during normal code deployments.

Initial launch may require a one-time migration of database content and uploads, but that is separate from the normal development and deployment workflow.

## Current Status

- Repository and ignore foundation: complete.
- `art-cms` plugin foundation: complete.
- Project prompt documentation: complete.
- Custom `art-theme` foundation: not started.
- Safe cPanel deployment template: not started.
- Admin menu organization: not started.
