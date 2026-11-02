# Solar Store

## Run locally

Start Apache and MySQL from the XAMPP Control Panel. In VS Code, run `Tasks: Run Task` and choose `Solar Store: Open Home in Browser` or `Solar Store: Open Calculator in Browser`. The PHP Server task runs XAMPP PHP on port 8012 and opens a rendered PHP page.

The Live Server `Go Live` command serves static files and cannot execute PHP; that is why it may show a folder listing or source instead of the site. Use the `PHP Server: Serve project` command/editor button or the workspace tasks. The PHP Server extension is configured to use `C:\xampp\php\php.exe`.

## Database

The current local settings connect to `localhost:3306`, database `shop_db`, and XAMPP's default `root` account with an empty password. The existing database was verified and preserved; do not reimport `shop1_db.sql` over it because the dump is for fresh setup and would replace current catalog data.

For a fresh database, import `shop1_db.sql`, then run `scripts/add_product_taxonomy.sql` and `scripts/seed_solar_catalog.sql`. The solar seed script is idempotent. Preserve cart, wishlist, and order references when upgrading a live catalog.

The calculator also reads `data/solar-products.json`, a verified recommendation catalog for GoodWe, Growatt, Solis and their storage products. It includes official product links and manufacturer-hosted images; product availability, regional model numbers, compatibility and prices must be confirmed with the local distributor before a sale or installation. The calculator's battery suggestions use the estimated usable-energy target and prefer a catalogued battery that meets or exceeds it.

## Production settings

Set `APP_ENV=production`, `SHOP_DB_HOST`, `SHOP_DB_PORT`, `SHOP_DB_NAME`, `SHOP_DB_USER`, and `SHOP_DB_PASS` in the web server process environment. Use a dedicated database account rather than the local XAMPP `root` account. Production responses hide raw database errors; details are written to the PHP error log.

SMTP is configured separately in `components/smtp_config.php`. `.env.example` documents values but is not automatically loaded by PHP.

Do not commit real database or SMTP credentials to this repository.
