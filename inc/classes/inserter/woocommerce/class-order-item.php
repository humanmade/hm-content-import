<?php

namespace HMCI\Inserter\Woocommerce;

use HMCI\Inserter\WP\Base;

/**
 * Woocommerce Order Item inserter - manages inserting order items into the woocommerce_order_items table.
 *
 * @package HMCI\Inserter\WP
 */
class Order_Item extends Base {

	static function insert( $order_item, $canonical_id = false, $order_item_meta = [] ) {

		if ( empty( $order_item['order_item_id'] ) && $canonical_id ) {
			$current_id = static::get_id_from_canonical_id( $canonical_id, 'order_item_id' );

			if ( $current_id ) {
				$order_item['order_item_id'] = $current_id;
			}
		}


	}

	static function get_id_from_canonical_id( $canonical_id, $id_field = 'order_item_id' ) {
		return parent::get_id_from_canonical_id( $canonical_id, $id_field );
	}
	static function get_core_object_type() {
		return 'woocommerce_order_item';
	}
}

