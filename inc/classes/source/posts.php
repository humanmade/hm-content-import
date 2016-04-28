<?php

namespace HMCI\Source;

trait Posts {

	use Base;

	public function get_items( $offset, $count ) {

		$query = $this->get_post_query( array(
			'posts_per_page' => $count,
			'offset'         => $offset,
		) );

		return $query->get_posts();
	}

	protected function get_post_query( $args ) {

		$query = new \WP_Query( wp_parse_args( $args, $this->get_global_query_args() ) );

		return $query;
	}

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

	public static function get_source_args() {

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
