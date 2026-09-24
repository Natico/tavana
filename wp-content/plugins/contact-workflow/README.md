# Contact Workflow

Contact Workflow is a standalone WordPress plugin for admin-managed contact submissions, contact departments, and basic contact workflow status tracking.

## Current Features

- Admin-only Contact Submissions.
- Admin-only Contact Departments.
- Department recipient email configuration.
- Department Active / Inactive state.
- Numeric Department order.
- Read-only submitted customer data in the admin detail screen.
- Explicit Contact Status workflow.
- Department deletion protection when submissions reference a department.
- Standalone Contact admin menu.
- Contact-specific capabilities.

## Custom Post Types

- `cw_submission`: admin-only contact submission records.
- `cw_department`: admin-only contact departments.

## Submission Meta

- `_cw_contact_name`
- `_cw_contact_email`
- `_cw_contact_phone`
- `_cw_contact_department_id`
- `_cw_contact_department_name`
- `_cw_contact_subject`
- `_cw_contact_message`
- `_cw_contact_status`

## Department Meta

- `_cw_department_emails`
- `_cw_department_active`
- `_cw_department_order`

## Capabilities

- `cw_view_submissions`
- `cw_edit_submission_status`
- `cw_delete_submissions`
- `cw_manage_departments`

Administrators receive these capabilities safely on activation and during admin initialization for already-active plugin updates. The plugin does not create a Writer role and does not modify non-Administrator roles.

## Admin Navigation

Contact
- Submissions
- Departments

## Data Preservation

Deactivation does not delete submissions, departments, meta, or user data. No uninstall deletion policy is implemented in this phase.

## Not Implemented Yet

- Frontend contact form.
- Email delivery or routing execution.
- SMTP/provider integrations.
- CAPTCHA or anti-spam.
- REST or AJAX public endpoints.
- Writer role creation.
- Migration from older development-only ART Contact identifiers.
- Import/export.
- Licensing, updater, or marketplace packaging.
