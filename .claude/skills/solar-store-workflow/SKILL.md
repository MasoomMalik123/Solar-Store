---
name: solar-store-workflow
description: Use when maintaining this PHP storefront, its PDO catalog, authentication, shared UI, or solar shopping flows.
---

# Solar Store Workflow

- The storefront is PHP with shared UI in `components/user_header.php`, `components/footer.php`, and `css/style.css`.
- Database access uses PDO through `components/connect.php`; use prepared statements for values.
- Product and user records live in MySQL. Preserve live cart, wishlist, and order references when changing catalog data.
- User passwords currently use legacy SHA-1 hashes. Do not change the hash format without migrating registration, login, and existing hashes together.
- Validate PHP edits with `php -l <file>` and verify shared UI at desktop and mobile widths, including both theme states.