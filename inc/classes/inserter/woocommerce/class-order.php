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

		if ( empty( $order ) || is_wp_error( $order ) ) {
			$order = new WC_Order();
		}

		$order->set_props( $order_data );
		$order->save();

		$order_id = $order->get_id();

		// Remove any current items from the order if this order is being updated.
		$order->remove_order_items();

		foreach ( $products as $product ) {
			$order_item = Order_Item::insert( ...$product );

			$order->add_item( $order_item );
		}

		$order->set_created_via( 'import' );
		$order->save();

		// Assign order meta to order, if any.
		foreach ( $order_meta as $meta_key => $meta_value ) {
			$order->update_meta_data( $meta_key, $meta_value );
		}
		$order->save_meta_data();

		static::set_canonical_id( $order_id, $canonical_id );
		return $order_id;
	}

	static function set_canonical_id( $order_id, $canonical_id ) {
		global $wpdb;

		if ( $order_id === static::get_id_from_canonical_id( $canonical_id, 'order_id' ) ) {
			return;
		}

		/*
		 * Because the order meta table doesn't follow the core structure for
		 * meta tables (the meta id column is called "id" rather than
		 * "meta_id"), calling `update_metadata()` throws a PHP warning. This code
		 * is copied from the internals of update_metadata, but simplified to
		 * return faster and not throw warnings.
		 */
		$meta_key = static::get_canonical_id_key( $canonical_id );

		$meta_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->ordermeta} WHERE order_id = %d AND meta_key = %s",
				$order_id,
				$meta_key,
			)
		);

		if ( empty( $meta_ids ) ) {
			add_metadata( 'order', $order_id, $meta_key, $canonical_id );
		}
	}

	static function get_core_object_type() {
		global $wpdb;

		// WooCommerce doesn't register orders with $wpdb.
		if ( ! property_exists( $wpdb, 'ordermeta' ) ) {
			$wpdb->ordermeta = "{$wpdb->prefix}wc_orders_meta";
		}

		return 'order';
	}
}

