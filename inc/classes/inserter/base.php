<?php

namespace HMCI\Inserter;

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
	static function get_canonical_id_key() {

		return apply_filters( 'hmci_import_type_canonical_id_key', 'hmci_canonical_id', get_called_class() );
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