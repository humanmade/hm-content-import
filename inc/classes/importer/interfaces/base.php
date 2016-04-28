<?php

namespace HMCI\Importer\Interfaces;

interface Base {

	public static function get_description();

	public static function get_source_args();

	public function get_count();

	public function parse_item( $item );

	function get_items( $offset, $count );
}