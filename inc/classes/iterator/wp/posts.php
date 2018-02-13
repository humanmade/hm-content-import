<?php

namespace HMCI\Iterator\WP;

/**
 * Base WP Posts iterator class
 * Iterates over provided WP post objects for processing
 *
 * Class Posts
 * @package HMCI\Iterator\WP
 */
abstract class Posts extends Base {

	/**
	 * Get post objects (paged)
	 *
	 * @param $offset
	 * @param $count
	 * @return array
	 */
	public function get_items( $offset, $count ) {

		$query = $this->get_post_query( array(
			'posts_per_page' => $count,
			'offset'         => $offset,
		) );

		return $query->get_posts();
	}

	/**
	 * Get post query
	 *
	 * @param $args
	 * @return \WP_Query
	 */
	protected function get_post_query( $args ) {

		$query = new \WP_Query( wp_parse_args( $args, $this->get_global_query_args() ) );

		return $query;
	}

	/**
	 * Get global post query args (no pagination)
	 *
	 * @return array
	 */
	protected function get_global_query_args() {

		$whitelist = array(
			'post_type',
			'post_status',
			'post_author',
		);

		$args = array();

		foreach( $whitelist as $accepted_arg ) {

			if ( isset( $this->args[ $accepted_arg ] ) ) {
				$args[ $accepted_arg ] = $this->args[ $accepted_arg ];
			}
		}

		return $args;
	}

	/**
	 * Get iterator argument definitions
	 *
	 * @return array
	 */
	public static function get_iterator_args() {

		return array(
			'post_type' => array(
				'default'       => 'any',
				'type'          => 'string',
				'description'   => __( 'Post type for post query.', 'hmci' )
			),
			'post_status' => array(
				'type'          => 'string',
				'description'   => __( 'Post status for post query.', 'hmci' )
			),
			'post_author' => array(
				'type'          => 'numeric',
				'description'   => __( 'Post author for post query.', 'hmci' )
			),
		);
	}
}
