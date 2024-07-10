<?php

namespace HMCI\Inserter;

use const HMCI\CANONICAL_ID_LOOKUP_KEY;

/**
 *
 * Base inserter class
 *
 * Standard class instance to be used for inserting an object somewhere
 *
 * @package HMCI\Inserter
 */
abstract class Base implements Base_Interface {

	/**
	 * Get canonical ID meta key
	 *
	 * @return mixed|void
	 */
	static function get_canonical_id_key( string $canonical_id ) {
		return apply_filters( 'hmci_canonical_id_key', sprintf( '%s_%s', CANONICAL_ID_LOOKUP_KEY, $canonical_id ), static::class );
	}

	/**
	 * Check if object exists for provided canonical ID
	 *
	 * @param $canonical_id
	 * @return bool
	 */
	static function exists( $canonical_id ) {

		return (bool) static::get_id_from_canonical_id( $canonical_id );
	}
}
