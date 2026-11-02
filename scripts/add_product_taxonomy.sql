SET @products_brand_exists = (
   SELECT COUNT(*)
   FROM `information_schema`.`columns`
   WHERE `table_schema` = DATABASE()
     AND `table_name` = 'products'
     AND `column_name` = 'brand'
);
SET @products_brand_sql = IF(
   @products_brand_exists = 0,
   'ALTER TABLE `products` ADD COLUMN `brand` varchar(80) NOT NULL DEFAULT '''' AFTER `name`',
   'SELECT 1'
);
PREPARE products_brand_statement FROM @products_brand_sql;
EXECUTE products_brand_statement;
DEALLOCATE PREPARE products_brand_statement;

SET @products_category_exists = (
   SELECT COUNT(*)
   FROM `information_schema`.`columns`
   WHERE `table_schema` = DATABASE()
     AND `table_name` = 'products'
     AND `column_name` = 'category'
);
SET @products_category_sql = IF(
   @products_category_exists = 0,
   'ALTER TABLE `products` ADD COLUMN `category` varchar(40) NOT NULL DEFAULT '''' AFTER `brand`',
   'SELECT 1'
);
PREPARE products_category_statement FROM @products_category_sql;
EXECUTE products_category_statement;
DEALLOCATE PREPARE products_category_statement;

SET @products_primary_key_exists = (
   SELECT COUNT(*)
   FROM `information_schema`.`table_constraints`
   WHERE `table_schema` = DATABASE()
     AND `table_name` = 'products'
     AND `constraint_type` = 'PRIMARY KEY'
);
SET @products_primary_key_sql = IF(
   @products_primary_key_exists = 0,
   'ALTER TABLE `products` ADD PRIMARY KEY (`id`)',
   'SELECT 1'
);
PREPARE products_primary_key_statement FROM @products_primary_key_sql;
EXECUTE products_primary_key_statement;
DEALLOCATE PREPARE products_primary_key_statement;

ALTER TABLE `products` MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;