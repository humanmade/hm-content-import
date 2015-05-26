<?php

namespace HMCI\Import_Type;

abstract class Base implements Base_Interface {

	static function get_canonical_id_key() {

		return apply_filters( 'hmci_import_type_canonical_id_key', 'hmci_canonical_id', get_called_class() );
	}

	static function exists( $canonical_id ) {

		return (bool) static::get_id_from_canonical_id( $canonical_id );
	}

}