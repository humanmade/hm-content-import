<?php
/*
Plugin Name: HM Content Import
Description: Developer Framework for Importing external data into WordPress during site migration
Version: 1.0
Author: Human Made
Author URI: http://hmn.md
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Text-Domain: hmci
*/

namespace HMCI;

require_once( __DIR__ . '/inc/utils/namespace.php' );

require_once( __DIR__ . '/inc/classes/iterator/class-base-interface.php' );
require_once( __DIR__ . '/inc/classes/iterator/class-base.php' );

require_once( __DIR__ . '/inc/classes/iterator/db/class-base.php' );

require_once( __DIR__ . '/inc/classes/iterator/wp/class-base.php' );
require_once( __DIR__ . '/inc/classes/iterator/wp/class-posts.php' );

require_once( __DIR__ . '/inc/classes/iterator/files/class-base.php' );
require_once( __DIR__ . '/inc/classes/iterator/files/class-json.php' );
require_once( __DIR__ . '/inc/classes/iterator/files/class-xml.php' );

require_once( __DIR__ . '/inc/classes/iterator/file/class-base.php' );
require_once( __DIR__ . '/inc/classes/iterator/file/class-csv.php' );
require_once( __DIR__ . '/inc/classes/iterator/file/class-xml.php' );
require_once( __DIR__ . '/inc/classes/iterator/file/class-json.php' );

require_once( __DIR__ . '/inc/classes/inserter/class-base-interface.php' );
require_once( __DIR__ . '/inc/classes/inserter/class-base.php' );

require_once( __DIR__ . '/inc/classes/inserter/file/class-base.php' );
require_once( __DIR__ . '/inc/classes/inserter/file/class-csv.php' );

require_once( __DIR__ . '/inc/classes/inserter/wp/class-base-interface.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/class-base.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/class-post.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/class-user.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/class-guest-author.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/class-attachment.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/class-term.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/class-comment.php' );

require_once( __DIR__ . '/inc/classes/class-master.php' );

/**
 * Meta key where canonical IDs are stored.
 */
const CANONICAL_ID_LOOKUP_KEY = 'hmci_canonical_id';

// Only incude CLI command file if WP_CLI is defined
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( __DIR__ . '/inc/classes/cli/class-hmci.php' );
}

// Initialise the master instance
add_action( 'init', function() {
	Master::get_instance();
}, 1 );
