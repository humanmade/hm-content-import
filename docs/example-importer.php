<?php
/**
 * Plugin Name: My Content Importer
 * Description: WordPress Importer
 * Version:     1.0
 *
 * Activate the migrator plugins: `wp plugin activate hm-content-import my-content-import`
 *
 * #### Run the import
 *
 * To run the import: `time wp hmci import my-importer --verbose --url="mysite.local" --export_path="my-import"`
 *
 * Note that the progress bar does not begin to indicate progress until it reaches 1%.
 */

namespace MY\Importer;

use HMCI\Master;
use HMCI\Inserter\WP\Post;

/**
 * Handles importing content.
 */
class MyContentImporter extends \HMCI\Iterator\Files\JSON {

	/**
	 * Processes the import item.
	 *
	 * @param array $item The import item.
	 * @return bool False on failure, true on success.
	 */
	protected function process_item( $item ) {
		$this->args['debugger'] = 'error_log';

		// Insert (or update if it's already imported) the post.
		$post = Post::insert(
			[
				'post_title'    => $item['title'],
				'post_content'  => $item['content'],
				'post_status'   => 'publish',
				'post_author'   => 1,
			],
			$item['id'] // Make sure to pass the canonical ID to the inserter.
		);

		if ( is_wp_error( $post ) ) {
			$this->debug( $post->get_error_message() );
			return false;
		}

		return true;
	}
}

add_action( 'plugins_loaded', function() {
	if ( ! class_exists( '\HMCI\Master' ) ) {
		return;
	}

	// Register the importer.
	Master::add_importer( 'my-importer', __NAMESPACE__ . '\\MyContentImporter' );
} );
