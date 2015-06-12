<?php

namespace HMCI;

require_once( __DIR__ . '/inc/classes/importer/traits/file-trait.php' );

require_once( __DIR__ . '/inc/classes/importer/base.php' );
require_once( __DIR__ . '/inc/classes/importer/database.php' );
require_once( __DIR__ . '/inc/classes/importer/file.php' );
require_once( __DIR__ . '/inc/classes/importer/files.php' );
require_once( __DIR__ . '/inc/classes/importer/json-files.php' );
require_once( __DIR__ . '/inc/classes/importer/csv-file.php' );

require_once( __DIR__ . '/inc/classes/import-type/interface.php' );
require_once( __DIR__ . '/inc/classes/import-type/base.php' );
require_once( __DIR__ . '/inc/classes/import-type/post.php' );
require_once( __DIR__ . '/inc/classes/import-type/user.php' );
require_once( __DIR__ . '/inc/classes/import-type/guest-author.php' );
require_once( __DIR__ . '/inc/classes/import-type/attachment.php' );

require_once( __DIR__ . '/inc/classes/master.php' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( __DIR__ . '/inc/classes/cli/import.php' );
}

add_action( 'init', function() {
	Master::get_instance();
} );