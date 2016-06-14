<?php

namespace HMCI\Importer;

interface Base_Interface {

	public static function get_description();

	public static function get_source_args();

	public function get_count();

	public function parse_item( $item );

	function get_items( $offset, $count );
}