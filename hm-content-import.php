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

require_once( __DIR__ . '/inc/classes/iterator/base-interface.php' );
require_once( __DIR__ . '/inc/classes/iterator/base.php' );

require_once( __DIR__ . '/inc/classes/iterator/db/base.php' );

require_once( __DIR__ . '/inc/classes/iterator/wp/base.php' );
require_once( __DIR__ . '/inc/classes/iterator/wp/posts.php' );

require_once( __DIR__ . '/inc/classes/iterator/files/base.php' );
require_once( __DIR__ . '/inc/classes/iterator/files/json.php' );
require_once( __DIR__ . '/inc/classes/iterator/files/xml.php' );

require_once( __DIR__ . '/inc/classes/iterator/file/base.php' );
require_once( __DIR__ . '/inc/classes/iterator/file/csv.php' );

require_once( __DIR__ . '/inc/classes/inserter/base-interface.php' );
require_once( __DIR__ . '/inc/classes/inserter/base.php' );

require_once( __DIR__ . '/inc/classes/inserter/file/base.php' );
require_once( __DIR__ . '/inc/classes/inserter/file/csv.php' );

require_once( __DIR__ . '/inc/classes/inserter/wp/base.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/post.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/user.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/guest-author.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/attachment.php' );
require_once( __DIR__ . '/inc/classes/inserter/wp/term.php' );

require_once( __DIR__ . '/inc/classes/master.php' );

// Only incude CLI command file if WP_CLI is defined
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( __DIR__ . '/inc/classes/cli/hmci.php' );
}

// Initialise the master instance
add_action( 'init', function() {
	Master::get_instance();
} );