<?php

// todo:: implement regex replacer with definable regex then callback with received data and expects replacement
// todo:: implement abstraction for replacing attachment urls -> should supply new url to callback along with any type data available, then replace.

namespace HMCI\Importer;

abstract class Post_Content extends Base {

	var $args     = array();
	var $debugger = false;

	protected function insert_item( $item ) {

		$post_content = $this->parse_post_content( $item->post_content, $item );

		wp_insert_post( array(
			'ID'            => $item->ID,
			'post_content'  => $post_content,
		) );
	}

	public function get_items( $offset, $count ) {

		$query = new \WP_Query( array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => $count,
			'offset'         => $offset
		) );

		return $query->get_posts();
	}

	abstract protected function parse_post_content( $post_content, $post );
}
