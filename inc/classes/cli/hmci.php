<?php

namespace HMCI\CLI;

use HMCI\Master;

class HMCI extends \WP_CLI_Command {

	/**
	 * Run a given importer.
	 *
	 * ## OPTIONS
	 *
	 * <importer>
	 * : The name of the importer you want to run.
	 *
	 * [--count=<number>]
	 * : The number of items to process in the import.
	 *
	 * [--offset=<number>]
	 * : How many items offset to start the import from.
	 *
	 * [--resume]
	 * : Resume from the previous run's progress.
	 *
	 * [--verbose]
	 * : Verbose debugging output.
	 *
	 * [--dry-run]
	 * : Run the import in dry-run mode, i.e. not inserting any content.
	 *
	 * [--export-path=<path>]
	 * : Where the export file(s) are located.
	 */
	public function import( $args, $args_assoc ) {

		$args_assoc = wp_parse_args( $args_assoc, array(
			'count'                       => 0,
			'offset'                      => 0,
			'resume'                      => false,
			'verbose'                     => false,
			'dry-run'                     => false,
			'export-path'                 => '',
			'disable-global-terms'        => true,
			'disable-trackbacks'          => true,
			'disable-intermediate-images' => false,
			'define-wp-importing'         => true,
			'db-user'                     => 'root',
			'db-pass'                     => '',
			'db-host'                     => 'localhost',
			'db-name'                     => '',
		) );

		$this->manage_disables( $args_assoc );

		$import_type = $args[0];
		$importer    = $this->get_importer( $import_type, $args_assoc );
		$count_all   = ( $importer->get_count() - $args_assoc['offset'] );
		$count       = ( $count_all < absint( $args_assoc['count'] ) || $args_assoc['count'] === 0 ) ? $count_all : absint( $args_assoc['count'] );
		$offset      = absint( $args_assoc['offset'] );
		$total       = $count + $offset;

		$progress = new \cli\progress\Bar( sprintf( __( 'Importing data for %s (%d items)', 'hmci' ), $import_type, $count ), $count, 100 );

		$progress->display();

		if ( $args_assoc['resume'] ) {
			$current_offset = $this->get_progress( 'importer', $import_type );
			$progress->tick( $current_offset );
		} else {
			$current_offset = 0;
		}

		while ( ( $offset + $current_offset ) < $total && $items = $importer->get_items( $offset + $current_offset, $importer->args['items_per_loop'] ) ) {

			$importer->import_items( $items );
			$current_offset += count( $items );
            $progress->tick( count( $items ) );

			$this->save_progress( 'importer', $import_type, $current_offset );
			$this->clear_local_object_cache();
		}

		$this->clear_progress( 'importer', $import_type );
		$progress->finish();
	}

	public function validate( $args, $args_assoc ) {

		$args_assoc = wp_parse_args( $args_assoc, array(
			'count'                => 0,
			'offset'               => 0,
			'resume'               => false,
			'verbose'              => true,
			'show-progress'        => true,
		) );

		$validate_type = $args[0];
		$validator    = $this->get_validator( $validate_type, $args_assoc );
		$count_all   = ( $validator->get_count() - $args_assoc['offset'] );
		$count       = ( $count_all < absint( $args_assoc['count'] ) || $args_assoc['count'] === 0 ) ? $count_all : absint( $args_assoc['count'] );
		$offset      = absint( $args_assoc['offset'] );
		$total       = $count + $offset;

		if ( $args_assoc['show-progress'] ) {
			$progress = new \cli\progress\Bar( sprintf( __( 'Validating data for %s (%d items)', 'hmci' ), $validate_type, $count ), $count, 100 );
		}

		if ( $args_assoc['resume'] ) {
			$current_offset = $this->get_progress( 'validator', $validate_type );

			if ( ! empty( $progress ) ) {
				$progress->tick( $current_offset );
			}
		} else {
			$current_offset = 0;
		}

		while ( ( $offset + $current_offset ) < $total && $items = $validator->get_items( $offset + $current_offset, $validator->args['items_per_loop'] ) ) {

			$validator->validate_items( $items );
			$current_offset += count( $items );

			if ( ! empty( $progress ) ) {
				$progress->tick( count( $items ) );
			}

			$this->save_progress( 'validator', $validate_type, $current_offset );
			$this->clear_local_object_cache();
		}

		$this->clear_progress( 'validator', $validate_type );

	}

	public function debug_contents( $args, $args_assoc ) {

		$args_assoc = wp_parse_args( $args_assoc, array(
			'count'       => 0,
			'offset'      => 0,
			'verbose'     => false,
			'export-path' => ''
		) );

		$import_type    = $args[0];
		$importer       = $this->get_importer( $import_type, $args_assoc );
		$count_all      = ( $importer->get_count() - $args_assoc['offset'] );
		$count          = ( $count_all < absint( $args_assoc['count'] ) || $args_assoc['count'] === 0 ) ? $count_all : absint( $args_assoc['count'] );
		$offset         = absint( $args_assoc['offset'] );
		$total          = $count + $offset;
		$current_offset = 0;

		while ( ( $offset + $current_offset ) < $total && $items = $importer->get_items( $offset + $current_offset, 1 ) ) {

			foreach( $items as $item ) {
				$this->debug( $item );
			}

			$current_offset += count( $items );
		}

	}

	protected function get_validator( $validator_type, $args ) {

		if ( $args['verbose'] ) {
			$args['debugger'] = array( $this, 'debug' );
		}

		$validator = Master::get_validator_instance( $validator_type, $args );

		if ( ! $validator ) {
			$this->debug( $validator_type . ' Is not a valid validator type', true );
		}

		if ( is_wp_error( $validator ) ) {
			$this->debug( $validator, true );
		}

		return $validator;
	}

	protected function get_importer( $import_type, $args ) {

		if ( $args['verbose'] ) {
			$args['debugger'] = array( $this, 'debug' );
		}

		$importer = Master::get_importer_instance( $import_type, $args );

		if ( ! $importer ) {
			$this->debug( $import_type . ' Is not a valid importer type', true );
		}

		if ( is_wp_error( $importer ) ) {
			$this->debug( $importer, true );
		}

		return $importer;
	}

	public static function debug( $output, $exit_on_output = false ) {

		if ( is_wp_error( $output ) ) {

			$output = $output->get_error_message();

		} elseif ( $output instanceof \Exception ) {

			$output = $output->getMessage();

		} else if ( ! is_string( $output ) ) {

			$output = var_export( $output, true );
		}

		if ( ! $output ) {
			return;
		}

		if ( $exit_on_output ) {
			\WP_CLI::Error( $output );
		} else {
			\WP_CLI::Line( $output );
		}
	}

	protected function clear_progress( $type, $name ) {

		delete_option( 'hmci_pg_' . md5( $type . '~' . $name ) );
	}

	protected function save_progress( $type, $name, $count ) {

		update_option( 'hmci_pg_' . md5( $type . '~' . $name ), $count );
	}

	protected function get_progress( $type, $name ) {

		return absint( get_option( 'hmci_pg_' . md5( $type . '~' . $name ), 0 ) );
	}

	protected function clear_local_object_cache() {

		global $wpdb, $wp_object_cache;

		$wpdb->queries = array(); // or define( 'WP_IMPORTING', true );

		if ( ! is_object( $wp_object_cache ) ) {
			return;
		}

		$wp_object_cache->group_ops = array();
		//$wp_object_cache->stats = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache = array();

		if ( is_callable( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset(); // important
		}

	}

	protected function manage_disables( $args ) {

		if ( ! empty( $args['disable-global-terms'] ) ) {
			$this->disable_global_terms();
		}

		if ( ! empty( $args['disable-trackbacks'] ) ) {
			$this->disable_trackbacks();
		}

		if ( ! empty( $args['disable-intermediate-images'] ) ) {
			$this->disable_intermediate_images();
		}

		if ( ! empty( $args['define-wp-importing'] ) && ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}
	}

	protected function disable_global_terms() {

		if ( ! empty( $this->global_terms_disabled ) ) {
			return;
		}

		add_filter( 'global_terms_enabled', '__return_false', 11 );
		$this->global_terms_disabled = true;
	}

	protected function disable_trackbacks() {

		if ( ! empty( $this->trackbacks_disabled ) ) {
			return;
		}

		add_filter( 'pre_option_default_ping_status', function() {
			return 'closed';
		}, 11 );

		add_filter( 'pre_option_default_pingback_flag', function() {
			return null;
		}, 11 );

		$this->trackbacks_disabled = true;
	}

	protected function disable_intermediate_images() {

		add_filter( 'intermediate_image_sizes_advanced', function( $sizes, $metadata ) {

			return array();

		}, 10, 2 );

	}
}
