<?php

namespace HMCI\CLI;

use HMCI\Master;

use WP_CLI;
use function HMCI\Utils\clear_local_object_cache as clear_local_cache;

/**
 * Custon WP_CLI Command for HMCI
 *
 * Allows triggering of registered import/validation scripts
 *
 * Class HMCI
 * @package HMCI\CLI
 */
class HMCI extends \WP_CLI_Command {

	/**
	 * @var \cli\progress\Bar
	 */
	public static $progressbar;

	/**
	 * @var array
	 */
	protected $args_assoc;

	/**
	 *
	 * @var \HMCI\Iterator\Base_Interface
	 */
	public static $current_import;

	/**
	 * Register the import sub-commands from all the registered importers.
	 * @return void
	 */
	public function register_commands() {
		foreach ( Master::get_importers() as $import_type => $importer_class ) {
			$importer = Master::get_importer_instance( $import_type );

			$importer_args = array_merge( $this->get_importer_global_args(), $importer->get_importer_args() );
			$synopsis = [];
			foreach ( $importer_args as $arg => $data ) {
				$synopsis[] = [
					'type' => $data['type'] == 'bool' ? 'flag' : 'assoc',
					'name' => $arg,
					'description' => $data['description'],
					'optional' => ! empty( $data['default'] ),
					'default' => $data['default'],
				];
			}

			WP_CLI::add_command( 'hmci import ' . $import_type, [ $this, 'import' ], [
				'shortdesc' => $importer->get_description(),
				'synopsis' => $synopsis,
			] );
		}
	}

	/**
	 * Run an importer.
	 *
	 * This is the main callback for all the import sub-commands.
	 *
	 * @param mixed $args
	 * @param mixed $args_assoc
	 * @return void
	 */
	public function import( $args, $args_assoc ) {
		$runner = WP_CLI::get_runner();
		$import_type = $runner->arguments[2];
		$importer = $this->get_importer( $import_type, $args_assoc );
		static::$current_import = $importer;

		$args = $importer->get_args();

		$this->manage_global_settings( $args_assoc );

		$count_all = ( $importer->get_count() - $args_assoc['offset'] );
		$count = ( $count_all < absint( $args_assoc['count'] ) || $args_assoc['count'] === 0 ) ? $count_all : absint( $args_assoc['count'] );
		$offset = absint( $args_assoc['offset'] );
		$total = $count + $offset;

		// translators: %1$s refers to an importer type, i.e. 'Posts Importer`. %2$d Refers to number of items being imported
		/** @var \cli\progress\Bar $progress */
		$progress = \WP_CLI\Utils\make_progress_bar( sprintf( __( 'Importing data for %1$s (%2$d items)', 'hmci' ), $import_type, $count ), $count );
		// Expose the progressbar so importers can use for incremental updates
		self::$progressbar = $progress;

		$progress->display();

		if ( ! empty( $args_assoc['resume'] ) ) {
			$current_offset = $this->get_progress( 'importer', $import_type );
			$progress->tick( $current_offset );
		} else {
			$current_offset = 0;
		}

		$items = $importer->get_items( $offset + $current_offset, min( $importer->args['items_per_loop'], $count ) );

		while ( ( $offset + $current_offset ) < $total && $items ) {

			$importer->iterate_items( $items );
			$progress->tick( count( $items ) );
			$current_offset += count( $items );

			if ( ( $offset + $current_offset ) >= $total ) {
				break;
			}

			$this->save_progress( 'importer', $import_type, $current_offset );
			clear_local_cache();

			$items = $importer->get_items( $offset + $current_offset, $importer->args['items_per_loop'] );
		}

		$this->clear_progress( 'importer', $import_type );
		$progress->finish();

		$importer->iteration_complete();
	}

	public function get_importer_global_args() {
		return [
			'count' => [
				'default' => 0,
				'type' => 'numeric',
				'description' => __( 'Number of items to be processed on a single loop, larger are more efficient but more memory intensive.', 'hmci' ),
			],
			'offset' => [
				'default' => 0,
				'type' => 'numeric',
				'description' => __( 'Offset to begin importing at', 'hmci' ),
			],
			'resume' => [
				'default' => false,
				'type' => 'bool',
				'description' => __( 'Attempt to resume script (if there was a failure during last execution)', 'hmci' ),
			],
			'verbose' => [
				'default' => true,
				'type' => 'bool',
				'description' => __( 'Dictate level of outputting', 'hmci' ),
			],
			'disable_global_terms' => [
				'default' => true,
				'type' => 'bool',
				'description' => __( 'Disable global terms. Defaults to true.', 'hmci' ),
			],
			'disable_trackbacks' => [
				'default' => true,
				'type' => 'bool',
				'description' => __( 'Disable trackbacks. Defaults to true.', 'hmci' ),
			],
			'disable_intermediate_images' => [
				'default' => false,
				'type' => 'bool',
				'description' => __( 'Disable intermediate image sizes.', 'hmci' ),
			],
			'define_wp_importing' => [
				'default' => true,
				'type' => 'bool',
				'description' => __( 'Define WP_IMPORTING constant. Defaults to true.', 'hmci' ),
			],
			'thread_id' => [
				'default' => '',
				'type' => 'string',
				'description' => __( 'Thread ID to keep a unique progress value per each, when threading', 'hmci' ),
			],
		];
	}

	/**
	 * Show the progress of an import
	 *
	 * @subcommand show-progress <importer>
	 * @param string[] $args
	 */
	public function show_progress( array $args ) {
		WP_CLI::line( $this->get_progress( 'importer', $args[0] ) );
	}

	/**
	 * Get an importer instance
	 *
	 * @param $import_type
	 * @param $args
	 * @return bool|\HMCI\Iterator\Base|\WP_Error
	 */
	protected function get_importer( $import_type, $args ) {

		if ( ! empty( $args['verbose'] ) ) {
			$args['debugger'] = [ $this, 'debug' ];
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

	/**
	 * CLI Debug
	 *
	 * @param $output
	 * @param bool $exit_on_output
	 */
	public static function debug( $output, $exit_on_output = false ) {

		if ( is_wp_error( $output ) ) {

			$output = $output->get_error_message();

		} elseif ( $output instanceof \Exception ) {

			$output = $output->getMessage();

		} elseif ( ! is_string( $output ) ) {

			$output = var_export( $output, true );
		}

		if ( ! $output ) {
			return;
		}

		if ( $exit_on_output ) {
			WP_CLI::error( $output );
		} else {
			WP_CLI::line( $output );
		}
	}

	protected function get_thread_id( $type, $name ) {
		$thread_id = isset( $this->args_assoc['thread_id'] ) ? '~' . $this->args_assoc['thread_id'] : null;
		return md5( $type . '~' . $name . $thread_id );
	}

	/**
	 * Save progress of a given script
	 *
	 * @param $type
	 * @param $name
	 * @param $count
	 */
	protected function save_progress( $type, $name, $count ) {

		update_option( 'hmci_pg_' . $this->get_thread_id( $type, $name ), $count, false );
	}

	/**
	 * Clear saved progress of a given script
	 *
	 * @param $type
	 * @param $name
	 */
	protected function clear_progress( $type, $name ) {

		delete_option( 'hmci_pg_' . $this->get_thread_id( $type, $name ) );
	}

	/**
	 * Get progress of a given script
	 *
	 * @param $type
	 * @param $name
	 * @return int
	 */
	protected function get_progress( $type, $name ) {

		return absint( get_option( 'hmci_pg_' . $this->get_thread_id( $type, $name ), 0 ) );
	}

	/**
	 * Manages global settings defined when an import script is being run
	 *
	 * @param $args
	 */
	protected function manage_global_settings( $args ) {

		if ( ! empty( $args['disable_global_terms'] ) ) {
			$this->disable_global_terms();
		}

		if ( ! empty( $args['disable_trackbacks'] ) ) {
			$this->disable_trackbacks();
		}

		if ( ! empty( $args['disable_intermediate_images'] ) ) {
			$this->disable_intermediate_images();
		}

		if ( ! empty( $args['define_wp_importing'] ) && ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}
	}

	/**
	 * Disable global terms
	 *
	 */
	protected function disable_global_terms() {

		if ( ! empty( $this->global_terms_disabled ) ) {
			return;
		}

		add_filter( 'global_terms_enabled', '__return_false', 11 );
		$this->global_terms_disabled = true;
	}

	/**
	 * Disable trackbacks
	 *
	 */
	protected function disable_trackbacks() {

		if ( ! empty( $this->trackbacks_disabled ) ) {
			return;
		}

		add_filter( 'pre_option_default_ping_status', function () {
			return 'closed';
		}, 11 );

		add_filter( 'pre_option_default_pingback_flag', function () {
			return null;
		}, 11 );

		$this->trackbacks_disabled = true;
	}

	/**
	 * Disable intermediate image sizes
	 *
	 */
	protected function disable_intermediate_images() {

		add_filter( 'intermediate_image_sizes_advanced', function ($sizes, $metadata) {

			return [];

		}, 10, 2 );
	}

	/**
	 * Pad a string with spaces (for help function)
	 *
	 * @param $string
	 * @param int $chars
	 * @return string
	 */
	protected function pad_string( $string, $chars = 15 ) {

		while ( strlen( $string ) < $chars ) {
			$string .= ' ';
		}

		return $string;
	}

	/**
	 * A set of 4 space tabs as a string (for help function)
	 *
	 * @param int $tabs
	 * @return string
	 */
	protected function get_tabs( $tabs = 0 ) {

		$single_tab = '    ';
		$string = '';

		for ( $i = 0; $i < $tabs; $i++ ) {

			$string .= $single_tab;
		}

		return $string;
	}
}
