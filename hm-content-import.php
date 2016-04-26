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

require_once( __DIR__ . '/inc/classes/importer/traits/file-trait.php' );
require_once( __DIR__ . '/inc/classes/importer/interfaces/base-interface.php' );

require_once( __DIR__ . '/inc/classes/importer/base.php' );
require_once( __DIR__ . '/inc/classes/importer/database.php' );
require_once( __DIR__ . '/inc/classes/importer/file.php' );
require_once( __DIR__ . '/inc/classes/importer/files.php' );
require_once( __DIR__ . '/inc/classes/importer/json-files.php' );
require_once( __DIR__ . '/inc/classes/importer/csv-file.php' );
require_once( __DIR__ . '/inc/classes/importer/post-content.php' );
require_once( __DIR__ . '/inc/classes/importer/xml-files.php' );

require_once( __DIR__ . '/inc/classes/validator/base.php' );
require_once( __DIR__ . '/inc/classes/validator/post.php' );
require_once( __DIR__ . '/inc/classes/validator/post-content-regex.php' );

require_once( __DIR__ . '/inc/classes/import-type/interface.php' );
require_once( __DIR__ . '/inc/classes/import-type/base.php' );
require_once( __DIR__ . '/inc/classes/import-type/post.php' );
require_once( __DIR__ . '/inc/classes/import-type/user.php' );
require_once( __DIR__ . '/inc/classes/import-type/guest-author.php' );
require_once( __DIR__ . '/inc/classes/import-type/attachment.php' );
require_once( __DIR__ . '/inc/classes/import-type/term.php' );

require_once( __DIR__ . '/inc/classes/master.php' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( __DIR__ . '/inc/classes/cli/hmci.php' );
}

add_action( 'init', function() {
	Master::get_instance();
} );