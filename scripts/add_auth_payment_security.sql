-- PHP password_hash() needs up to 255 characters. Existing installs may still
-- have the original varchar(50) column, which silently truncates new hashes.
ALTER TABLE `users` MODIFY `password` varchar(255) NOT NULL;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(100) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_password_reset_token_hash` (`token_hash`),
  KEY `idx_password_reset_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `provider` varchar(40) NOT NULL,
  `order_id` int(100) DEFAULT NULL,
  `merchant_order_ref` varchar(120) NOT NULL,
  `provider_transaction_id` varchar(160) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0,
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `status` varchar(30) NOT NULL DEFAULT 'received',
  `signature_valid` tinyint(1) NOT NULL DEFAULT 0,
  `raw_payload` longtext NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_provider_ref` (`provider`,`merchant_order_ref`),
  KEY `idx_payment_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
