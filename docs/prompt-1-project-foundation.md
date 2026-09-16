# Prompt 1: Project Development Foundation

```text
You are the coding agent responsible for preparing the development foundation of an existing WordPress project called ART.

Important: do not start implementing the full website yet. First prepare a clean, maintainable development/codebase structure and Git workflow.

CURRENT LOCAL ENVIRONMENT
-------------------------
Operating system: Ubuntu 24.04
Web server: Apache
PHP: 8.3
MySQL: 8.0
WordPress is already installed and working.

Local WordPress path:

/var/www/art-site

Local URL:

http://art-site.local

Do NOT reinstall WordPress.
Do NOT delete the existing WordPress installation.
Do NOT modify WordPress Core.

PROJECT TYPE
------------
This is a classic WordPress project.

We are NOT using:

- Bedrock
- Headless WordPress
- Next.js
- page builders
- Elementor
- Gutenberg as a page-builder architecture
- external CMS frameworks

We will create:

1. A custom WordPress theme
2. A custom project plugin for application/content-model logic if necessary

The theme should be responsible primarily for presentation.

Business/content logic such as:

- custom post types
- custom taxonomies
- custom admin behavior
- project-specific fields
- reusable content-model logic

should preferably live in a custom plugin rather than being tightly coupled to the theme.

Use this tentative naming:

Theme:
art-theme

Plugin:
art-core

GIT STRATEGY
------------
This project will eventually be deployed to shared hosting with cPanel.

The production server is NOT a VPS.

cPanel has:

- Git Version Control
- File Manager
- phpMyAdmin
- Backup
- standard WordPress hosting tools

Therefore the project must NOT depend on:

- root access
- Docker in production
- long-running Node services
- system services
- server-level configuration
- Bedrock-specific server configuration

The Git repository may be PUBLIC.

Git must contain ONLY our source-controlled project code and related development/deployment files.

DO NOT COMMIT:

- WordPress Core
- wp-config.php
- database dumps
- passwords
- secrets
- API keys
- wp-content/uploads
- cache
- logs
- generated backup files
- temporary files
- IDE-specific junk

Production content and production database must remain independent from local development after the initial launch.

DEPLOYMENT MODEL
----------------

The long-term workflow should be:

Local development / Coding Agent
        |
        v
      Git
        |
        v
GitHub or GitLab
        |
        v
cPanel Git Version Control
        |
        v
Deploy custom code
        |
        v
Production WordPress

Only custom source code should normally move from local to production.

Production database must NOT be overwritten during normal deployments.

Production uploads must NOT be overwritten during normal deployments.

Initial launch is different and may later include a one-time migration of:

- database
- uploads

but that is NOT part of normal code deployments.

TASK
----

Inspect the existing WordPress installation at:

/var/www/art-site

Then prepare the project development foundation.

Do the following carefully.

1. Verify the existing WordPress installation and current wp-content structure.

2. Create the custom theme:

/var/www/art-site/wp-content/themes/art-theme

Create only a clean minimal starter theme for now.

It should include an appropriate base such as:

style.css
functions.php
index.php
header.php
footer.php

and a sensible scalable directory structure for future development.

For example you may introduce directories such as:

assets/
inc/
templates/
template-parts/

but do not over-engineer it.

3. Create the custom plugin:

/var/www/art-site/wp-content/plugins/art-core

Create a minimal valid WordPress plugin bootstrap.

For now it may contain only the foundation.

We will later use it for project-specific functionality such as custom post types, taxonomies and admin/content-model logic.

Do NOT implement the entire application model yet unless required for a clean bootstrap.

4. Prepare Git version control.

We want ONE repository for this project code.

It is acceptable for the Git repository root to remain at:

/var/www/art-site

BUT Git must track only our custom project files.

Create a robust .gitignore / whitelist strategy so that WordPress Core and runtime content never enter Git.

At minimum Git should be able to track:

wp-content/themes/art-theme/
wp-content/plugins/art-core/
.gitignore
README.md
deployment-related configuration that contains no secrets

while ignoring everything else that should not be source controlled.

Before committing anything, explicitly check:

git status

and ensure WordPress Core, uploads and wp-config.php are NOT staged.

5. Create README.md.

Document:

- project name
- local URL
- local WordPress path
- PHP requirement
- where theme code lives
- where plugin code lives
- what is intentionally excluded from Git
- development workflow
- basic deployment concept
- warning that production DB/uploads must not be overwritten during normal deployment

6. Prepare for cPanel deployment.

cPanel Git Version Control is available.

Do NOT perform an actual production deployment.

Prepare the repository so that later we can deploy:

wp-content/themes/art-theme

to:

public_html/wp-content/themes/art-theme

and:

wp-content/plugins/art-core

to:

public_html/wp-content/plugins/art-core

If a .cpanel.yml deployment file is appropriate, create a SAFE template/example.

Do not hardcode an unknown production path.
```
