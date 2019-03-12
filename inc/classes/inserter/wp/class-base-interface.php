<?php

namespace HMCI\Inserter\WP;

/**
 * Base WP Inserter class interface
 *
 * @package HMCI\Inserter\WP
 */
interface Base_Interface {

	/**
	 * Get the core object type used by the inserter.
	 *
	 * @return string
	 */
	static function get_core_object_type();
}
