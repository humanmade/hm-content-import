<?php

namespace HMCI\Utils;

/**
 * Clear the local object cache to avoid memory leakage issues
 */
function clear_local_object_cache() {

	global $wpdb, $wp_object_cache;

	$wpdb->queries = []; // or define( 'WP_IMPORTING', true );

	if ( ! is_object( $wp_object_cache ) ) {
		return;
	}

	wp_cache_flush_runtime();
}
