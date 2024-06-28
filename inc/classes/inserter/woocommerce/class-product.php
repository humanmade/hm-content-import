<?php

namespace HMCI\Inserter\Woocommerce;

use HMCI\Inserter\WP\Post;
use WC_Product_Factory;

/**
 * Woocommerce Product inserter - manages inserting products from post_data and meta_data
 *
 * @package HMCI\Inserter\WP
 */
class Product extends Post {

	/**
	 * Add product post object to the database, and set meta for it.
	 *
	 * @param array $post_data     Post data formatted as it will be saved to the posts table. Should match WP_Post data.
	 * @param bool  $canonical_id  Use an existing canonical ID.
	 * @param array $post_meta     Metadata to assign to the post.
	 * @param array $product_meta  Product meta props - defined WooCommerce values that get saved to wc_product_meta_lookup table.
	 * @return int|\WP_Error
	 */
	static function insert( $post_data = [], $canonical_id = false, $post_meta = [], $product_meta = [] ) {

		if ( empty( $post_data['post_type'] ) ) {
			$post_data['post_type'] = 'product';
		}

		$product_id = parent::insert( $post_data, $canonical_id, $post_meta );

		if ( $product_id && ! is_wp_error( $product_id ) ) {

			$product = ( new WC_Product_Factory() )->get_product( $product_id );

			$product->set_props( $product_meta );
			$product->save();
		}

		return $product_id;
	}
}
