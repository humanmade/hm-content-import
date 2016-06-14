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

require_once( __DIR__ . '/inc/classes/source/base.php' );
require_once( __DIR__ . '/inc/classes/source/database.php' );
require_once( __DIR__ . '/inc/classes/source/posts.php' );
require_once( __DIR__ . '/inc/classes/source/files.php' );
require_once( __DIR__ . '/inc/classes/source/file.php' );
require_once( __DIR__ . '/inc/classes/source/json-files.php' );
require_once( __DIR__ . '/inc/classes/source/csv-file.php' );
require_once( __DIR__ . '/inc/classes/source/xml-files.php' );

require_once( __DIR__ . '/inc/classes/importer/base-interface.php' );
require_once( __DIR__ . '/inc/classes/importer/base.php' );

require_once( __DIR__ . '/inc/classes/validator/base-interface.php' );
require_once( __DIR__ . '/inc/classes/validator/base.php' );
require_once( __DIR__ . '/inc/classes/validator/post-content-regex.php' );

require_once( __DIR__ . '/inc/classes/destination/base-interface.php' );
require_once( __DIR__ . '/inc/classes/destination/base.php' );
require_once( __DIR__ . '/inc/classes/destination/wp/base.php' );
require_once( __DIR__ . '/inc/classes/destination/wp/post.php' );
require_once( __DIR__ . '/inc/classes/destination/wp/user.php' );
require_once( __DIR__ . '/inc/classes/destination/wp/guest-author.php' );
require_once( __DIR__ . '/inc/classes/destination/wp/attachment.php' );
require_once( __DIR__ . '/inc/classes/destination/wp/term.php' );

require_once( __DIR__ . '/inc/classes/master.php' );

// Only incude CLI command file if WP_CLI is defined
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( __DIR__ . '/inc/classes/cli/hmci.php' );
}

// Initialise the master instance
add_action( 'init', function() {
	Master::get_instance();
} );