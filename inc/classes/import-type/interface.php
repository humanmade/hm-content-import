<?php

namespace HMCI\Import_Type;

interface Base_Interface {

	static function exists( $canonical_id );

	static function insert( $item, $canonical_id );

	static function set_meta( $wordpress_id, $meta_data );

	static function set_canonical_id( $wordpress_id, $canonical_id );

	static function get_id_from_canonical_id( $canonical_id );

}