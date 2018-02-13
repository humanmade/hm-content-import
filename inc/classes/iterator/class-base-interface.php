<?php

namespace HMCI\Iterator;

/**
 * Base interface for iterators
 *
 * Interface is used to define abstract static methods
 *
 * Interface Base_Interface
 * @package HMCI\Iterator
 */
interface Base_Interface {

	/**
	 * Get iterator description
	 *
	 * @return mixed
	 */
	public static function get_description();

	/**
	 * Get iterator custom argument definitions
	 *
	 * @return mixed
	 */
	public static function get_custom_args();
}
