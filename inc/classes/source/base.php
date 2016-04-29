<?php

namespace HMCI\Source;

trait Base {

	public function get_count() {

		$offset = 0;

		while ( $items = $this->get_items( $offset, $this->args['items_per_loop'] ) ) {

			$offset += count( $items );
		}

		return $offset;
	}

	public function parse_item( $item ) {

		return $item;
	}

	public static function get_source_args() {
		return array();
	}

	abstract public function get_items( $offset, $count );
}
