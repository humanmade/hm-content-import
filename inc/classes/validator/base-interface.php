<?php

namespace HMCI\Validator;

interface Base_Interface {

	public static function get_description();

	public function get_count();

	public function parse_item( $item );

	function get_items( $offset, $count );

	public static function get_source_args();

}