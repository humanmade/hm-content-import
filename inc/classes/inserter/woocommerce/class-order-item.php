<?php

namespace HMCI\Inserter\Woocommerce;

use HMCI\Inserter\WP\Base;
use WC_Order_Factory;
use WC_Order_Item;
use WC_Order_Item_Product;

/**
 * Woocommerce Order Item inserter - manages inserting order items into the woocommerce_order_items table.
 *
 * @package HMCI\Inserter\WP
 */
class Order_Item extends Base {

	/**
	 * "Insert" an order item.
	 *
	 * Note: This is different than most of the HMCI inserter classes in that
	 * it doesn't actually save anything to the database. This is because the
	 * WC_Order_Item class doesn't expose any save methods, expecting that the
	 * actual updates will be saved from the WC_Order class.
	 *
	 * As a result, this, method just returns a WC_Order_Item object which can
	 * be added to a WC_Order. See the Order inserter for an example of its
	 * use.
	 *
	 * @param int $order_id Order ID being created.
	 * @param [] $order_item Order item data.
	 * @param string $canonical_id Canonical ID to look up this order with.
	 * @param [] $order_item_meta Order item meta to insert.
	 * @return WC_Order_Item
	 */
	static function insert( $order_item, $canonical_id = false, $order_item_meta = [] ) {

		if ( $canonical_id ) {
			$current_id = static::get_id_from_canonical_id( $canonical_id, 'order_item_id' );
		}

		if ( $current_id ) {
			$order_item_record = WC_Order_Factory::get_order_item( $current_id );
		} else {
			$order_item_record = new WC_Order_Item_Product();
		}

		$order_item_record->set_props( $order_item );

		if ( $canonical_id ) {
			$order_item_meta[ static::get_canonical_id_key( $canonical_id ) ] = 1;
		}

		foreach ( $order_item_meta as $key => $value ) {
			$order_item_record->update_meta_data( $key, $value, null );
		}

		$order_item_record->apply_changes();

		return $order_item_record;
	}

	static function get_core_object_type() {
		return 'order_item';
	}
}

