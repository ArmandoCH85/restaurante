CREATE TABLE `ventas_sistema_anterior` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`fecha_venta` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`caja` VARCHAR(100) NOT NULL COMMENT 'Nombre de la caja' COLLATE 'utf8mb4_unicode_ci',
	`cliente` VARCHAR(300) NULL DEFAULT NULL COMMENT 'Información del cliente' COLLATE 'utf8mb4_unicode_ci',
	`documento` VARCHAR(100) NOT NULL COMMENT 'Número del documento de venta' COLLATE 'utf8mb4_unicode_ci',
	`canal_venta` VARCHAR(150) NULL DEFAULT NULL COMMENT 'Canal o ubicación de la venta' COLLATE 'utf8mb4_unicode_ci',
	`tipo_pago` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Método de pago' COLLATE 'utf8mb4_unicode_ci',
	`total` DECIMAL(12,2) NOT NULL COMMENT 'Monto total de la venta',
	`estado` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Estado de la transacción' COLLATE 'utf8mb4_unicode_ci',
	`created_at` TIMESTAMP NULL DEFAULT (CURRENT_TIMESTAMP) COMMENT 'Fecha de importación',
	`updated_at` TIMESTAMP NULL DEFAULT (CURRENT_TIMESTAMP) ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `idx_fecha_venta` (`fecha_venta`) USING BTREE,
	INDEX `idx_documento` (`documento`) USING BTREE,
	INDEX `idx_caja` (`caja`) USING BTREE,
	INDEX `idx_tipo_pago` (`tipo_pago`) USING BTREE,
	INDEX `idx_estado` (`estado`) USING BTREE,
	INDEX `idx_canal_venta` (`canal_venta`) USING BTREE,
	INDEX `idx_total` (`total`) USING BTREE
)
COMMENT='Ventas importadas del sistema anterior para consultas históricas'
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1991
;
