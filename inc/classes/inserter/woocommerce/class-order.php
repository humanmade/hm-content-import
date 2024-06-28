<?php

namespace HMCI\Inserter\Woocommerce;

use HMCI\Inserter\WP\Base;
use WC_Order;

/**
 * Woocommerce Order inserter - manages inserting orders into the wc_orders table.
 *
 * @package HMCI\Inserter\WP
 */
class Order extends Base {

	/**
	 * Insert an order record into the database.
	 *
	 * @param [] $order_data All the WooCommerce order fields.
	 * @param string $canonical_id Canonical ID to use for this order.
	 * @param [] $order_meta Order meta fields.
	 * @param [] $products Products to include in the order (see Order_Item class for args).
	 * @return int|WP_Error ID of successfully imported order.
	 */
	static function insert( $order_data, $canonical_id, $order_meta = [], $products = [] ) {

		if ( empty( $order_item['id'] ) && $canonical_id ) {
			$current_id = static::get_id_from_canonical_id( $canonical_id, 'order_id' );

			if ( $current_id ) {
				$order = wc_get_order( $current_id );
			}
		}

		if ( ! $order || is_wp_error( $order ) ) {
			$order = new WC_Order();
		}

		$order->set_props( $order_meta );

		foreach ( $products as $product ) {
			$order_item = Order_Item::insert( ...$product );

			$order->add_item( $order_item );
		}

		$order->set_created_by( 'import' );

		return $order->save();
	}

	static function get_core_object_type() {
		return 'wc_order';
	}
}

