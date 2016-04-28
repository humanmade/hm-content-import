<?php

namespace HMCI\Validator;

use HMCI\Source;

class Post_Content_Regex extends Base {

	use Source\Posts;

	public function __construct( $args = array()  ) {

		$args = wp_parse_args( $args, array(
			'delimiter'       => '~',
			'match_index'     => 0,
		) );

		parent::__construct( $args );
	}

	protected function verify_args() {

		if ( empty( $this->args['regex'] ) ) {
			return new \WP_Error( 'hmci_no_regex_supplied', 'Missing --regex parameter.' );
		}

		return true;
	}

	static function get_args_definitions() {


	}

	protected function validate_item( $item ) {

		$content     = $item->post_content;
		$regex       = $this->args['regex'];
		$delimiter   = $this->args['delimiter'];
		$match_index = $this->args['match_index'];
		$has_match   = preg_match_all( sprintf( '%s%s%ss', $delimiter, $regex, $delimiter ), $content, $matches );

		if ( ! $has_match || empty( $matches[ $match_index ] ) ) {
			return false;
		}

		return array( $item->ID, implode( ', ', ( (array) $matches[ $match_index ] ) ) );
	}

	public static function get_description() {

		return __( 'Post content regex validator, pass in regex to match validation failure triggers.', 'hmci' );
	}

}