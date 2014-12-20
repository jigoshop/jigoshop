<?php

namespace Jigoshop\Core\Installer\Product;

use Jigoshop\Core\Installer\Initializer;
use WPAL\Wordpress;

class Variable implements Initializer
{
	public function initialize(Wordpress $wp)
	{
		$wpdb = $wp->getWPDB();
		$wpdb->hide_errors();

		$collate = '';
		if ($wpdb->has_cap('collation')) {
			if (!empty($wpdb->charset)) {
				$collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}
			if (!empty($wpdb->collate)) {
				$collate .= " COLLATE {$wpdb->collate}";
			}
		}

		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_product_variation_attribute (
				variation_id BIGINT UNSIGNED NOT NULL,
				attribute_id INT(9) NOT NULL,
				value VARCHAR(255),
				PRIMARY KEY id (variation_id, attribute_id),
				FOREIGN KEY variation (variation_id) REFERENCES {$wpdb->posts} (ID) ON DELETE CASCADE,
				FOREIGN KEY attribute (attribute_id) REFERENCES {$wpdb->prefix}jigoshop_attribute (id) ON DELETE CASCADE
			) {$collate};
		";
		$wpdb->query($query);
		$wpdb->show_errors();
	}
}
