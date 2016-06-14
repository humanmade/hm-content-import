<?php

namespace HMCI\Destination;

/**
 *
 * Base destination class
 *
 * Standard class instance to be used for inserting an object somewhere
 *
 * @package HMCI\Destination
 */
abstract class Base implements Base_Interface {

	/**
	 * @return mixed|void
	 */
	static function get_canonical_id_key() {

		return apply_filters( 'hmci_import_type_canonical_id_key', 'hmci_canonical_id', get_called_class() );
	}

	/**
	 * @param $canonical_id
	 * @return bool
	 */
	static function exists( $canonical_id ) {

		return (bool) static::get_id_from_canonical_id( $canonical_id );
	}

}