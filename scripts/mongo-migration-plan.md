# SQL to local MongoDB migration

The application currently uses PDO/MySQL in components/connect.php. Do not switch only the connection string: the pages use SQL joins, prepared statements, sessions and transactional order updates.

## Target collections

- users
- admins
- products
- solar_catalog
- solar_accessories
- wishlist
- cart
- orders
- messages
- password_reset_tokens
- payment_transactions

## Required local setup

1. Install MongoDB Community Server and start the local service.
2. Install the PHP MongoDB extension matching the XAMPP PHP version.
3. Add the official mongodb/mongodb Composer package.
4. Add MONGO_URI=mongodb://127.0.0.1:27017 and MONGO_DB_NAME=solar_store to .env.
5. Export SQL data to BSON/JSON, validate counts and totals, then switch repositories one feature at a time.
6. Keep the SQL database read-only until login, catalog, cart, orders, password reset and payment webhook tests pass on Mongo.

## Migration acceptance checks

- Every user email remains unique and password hashes remain valid.
- Cart and wishlist ownership is preserved.
- Each order keeps its original total, payment status and line-item snapshot.
- Duplicate payment webhooks remain idempotent.
- Product images and official source URLs remain unchanged.
- A rollback environment variable can still point the application to the SQL database.

The PHP MongoDB extension is not currently enabled in this XAMPP installation, so the live application is intentionally still using SQL until the dependency and target database are installed.
