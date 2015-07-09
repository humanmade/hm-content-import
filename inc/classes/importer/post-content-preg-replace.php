<?php

// todo:: implement regex replacer with definable regex then callback with received data and expects replacement
// todo:: implement abstraction for replacing attachment urls -> should supply new url to callback along with any type data available, then replace.

namespace HMCI\Importer;

abstract class Post_Content_Preg_Replace extends Base {

	abstract protected function get_regex();

	abstract protected function replace_callback( $match, $post );

	protected function parse_post_content( $post_content, $post ) {

		$regex = $this->get_regex();

		$content = preg_replace_callback( $regex, function( $match ) use ( $post ) {

			return $this->replace_callback( $match, $post );

		}, $post_content );

		return $content;
	}
}
