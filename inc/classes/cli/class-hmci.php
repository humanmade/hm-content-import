<?php

namespace HMCI\CLI;

use HMCI\Master;

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
	 * Run a registered import script
	 *
	 * @subcommand import
	 */
	public function import( $args, $args_assoc ) {

		$this->args_assoc = $args_assoc = wp_parse_args( $args_assoc, [
			'count'                       => 0,
			'offset'                      => 0,
			'resume'                      => false,
			'verbose'                     => false,
			'disable_global_terms'        => true,
			'disable_trackbacks'          => true,
			'disable_intermediate_images' => false,
			'define_wp_importing'         => true,
			'thread_id'                   => '', // Thread ID to keep a unique progress value per each, when threading
		] );

		$this->manage_global_settings( $args_assoc );

		$import_type = $args[0];
		$importer    = $this->get_importer( $import_type, $args_assoc );
		$count_all   = ( $importer->get_count() - $args_assoc['offset'] );
		$count       = ( $count_all < absint( $args_assoc['count'] ) || $args_assoc['count'] === 0 ) ? $count_all : absint( $args_assoc['count'] );
		$offset      = absint( $args_assoc['offset'] );
		$total       = $count + $offset;

		// translators: %1$s refers to an importer type, i.e. 'Posts Importer`. %2$d Refers to number of items being imported
		/** @var \cli\progress\Bar $progress */
		$progress = \WP_CLI\Utils\make_progress_bar( sprintf( __( 'Importing data for %1$s (%2$d items)', 'hmci' ), $import_type, $count ), $count );
		// Expose the progressbar so importers can use for incremental updates
		self::$progressbar = $progress;

		$progress->display();

		if ( $args_assoc['resume'] ) {
			$current_offset = $this->get_progress( 'importer', $import_type );
			$progress->tick( $current_offset );
		} else {
			$current_offset = 0;
		}

		$items = $importer->get_items( $offset + $current_offset, min( $importer->args['items_per_loop'], $count ) );

		while ( ( $offset + $current_offset ) < $total && $items ) {

			$importer->iterate_items( $items );
			// Only tick if the offset hasn't been changed by the importer
			if ( $progress->current() === $current_offset ) {
				$progress->tick( $importer->args['items_per_loop'] );
			}
			$current_offset += $importer->args['items_per_loop'];

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

	/**
	 *
	 * Run a registered validation script
	 *
	 * @subcommand validate
	 */
	public function validate( $args, $args_assoc ) {

		$args_assoc = wp_parse_args( $args_assoc, [
			'count'         => 0,
			'offset'        => 0,
			'resume'        => false,
			'verbose'       => true,
			'show_progress' => true,
		] );

		$validator_type = $args[0];
		$validator      = $this->get_validator( $validator_type, $args_assoc );
		$count_all      = ( $validator->get_count() - $args_assoc['offset'] );
		$count          = ( $count_all < absint( $args_assoc['count'] ) || $args_assoc['count'] === 0 ) ? $count_all : absint( $args_assoc['count'] );
		$offset         = absint( $args_assoc['offset'] );
		$total          = $count + $offset;

		if ( $args_assoc['show_progress'] && $args_assoc['show_progress'] !== 'false' ) {
			// translators: %1$s refers to a validator type, i.e. 'Posts Validator`. %1$d Refers to number of items being validated
			$progress = new \cli\progress\Bar( sprintf( __( 'Validating data for %1$s (%1$d items)', 'hmci' ), $validator_type, $count ), $count, 100 );
			$progress->display();
		}

		if ( $args_assoc['resume'] ) {
			$current_offset = $this->get_progress( 'validator', $validator_type );
			if ( $args_assoc['show_progress'] && $args_assoc['show_progress'] !== 'false' ) {
				$progress->tick( $current_offset );
			}
		} else {
			$current_offset = 0;
		}

		$items = $validator->get_items( $offset + $current_offset, $validator->args['items_per_loop'] );

		while ( ( $offset + $current_offset ) < $total && $items ) {

			$validator->iterate_items( $items );
			$current_offset += count( $items );
			if ( $args_assoc['show_progress'] && $args_assoc['show_progress'] !== 'false' ) {
				$progress->tick( count( $items ) );
			}

			$this->save_progress( 'validator', $validator_type, $current_offset );
			clear_local_cache();

			$items = $validator->get_items( $offset + $current_offset, $validator->args['items_per_loop'] );
		}

		$this->clear_progress( 'validator', $validator_type );

		if ( $args_assoc['show_progress'] && $args_assoc['show_progress'] !== 'false' ) {
			$progress->finish();
		}

		$this->clear_progress( 'validator', $validator_type );

		$validator->iteration_complete();
	}

	/**
	 * Custom help command to list importers/validators and their associated args
	 *
	 * @subcommand help
	 */
	public function help() {

		$this->debug( "\r\nAVAILABLE IMPORTERS (hmci import)" );

		foreach ( Master::get_importers() as $impoter_key => $importer ) {

			$this->debug( sprintf( "\r\n%s\r\n", $impoter_key ) );

			$this->debug( sprintf( '%sDescription', $this->get_tabs( 1 ) ) );

			$this->debug( sprintf( "\r\n%s%s\r\n", $this->get_tabs( 2 ), call_user_func( [ $importer, 'get_description' ] ) ) );

			$args = call_user_func( [ $importer, 'get_args' ] );

			$this->debug( sprintf( '%sArguments', $this->get_tabs( 1 ) ) );

			foreach ( $args as $arg => $data ) {

				$this->debug( sprintf( "\r\n%s%s", $this->get_tabs( 2 ), $arg ) );

				foreach ( $data as $data_key => $data_val ) {

					$this->debug( sprintf( '%s%s: %s', $this->get_tabs( 3 ), $this->pad_string( $data_key ),  $data_val ) );
				}
			}
		}

		$validators = Master::get_validators();

		if ( $validators ) {

			$this->debug( "\r\nAVAILABLE VALIDATORS (hmci validate)" );

			foreach ( $validators as $impoter_key => $importer ) {

				$this->debug( sprintf( "\r\n%s\r\n", $impoter_key ) );

				$this->debug( sprintf( '%sDescription', $this->get_tabs( 1 ) ) );

				$this->debug( sprintf( "\r\n%s%s\r\n", $this->get_tabs( 2 ), call_user_func( [ $importer, 'get_description' ] ) ) );

				$args = call_user_func( [ $importer, 'get_args' ] );

				$this->debug( sprintf( '%sArguments', $this->get_tabs( 1 ) ) );

				foreach ( $args as $arg => $data ) {

					$this->debug( sprintf( "\r\n%s%s", $this->get_tabs( 2 ), $arg ) );

					foreach ( $data as $data_key => $data_val ) {

						$this->debug( sprintf( '%s%s: %s', $this->get_tabs( 3 ), $this->pad_string( $data_key ),  $data_val ) );
					}
				}
			}
		}
	}

	/**
	 * Get an importer instance
	 *
	 * @param $import_type
	 * @param $args
	 * @return bool|\HMCI\Iterator\Base|\WP_Error
	 */
	protected function get_importer( $import_type, $args ) {

		if ( $args['verbose'] ) {
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
	 * Get a validator instance
	 *
	 * @param $validator_type
	 * @param $args
	 * @return bool|\HMCI\Iterator\Base|\WP_Error
	 */
	protected function get_validator( $validator_type, $args ) {

		if ( $args['verbose'] ) {
			$args['debugger'] = [ $this, 'debug' ];
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
			\WP_CLI::Error( $output );
		} else {
			\WP_CLI::Line( $output );
		}
	}

	protected function get_thread_id( $type, $name ) {
		$thread_id = $this->args_assoc['thread_id'] ? '~' . $this->args_assoc['thread_id'] : null;
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

		add_filter( 'pre_option_default_ping_status', function() {
			return 'closed';
		}, 11 );

		add_filter( 'pre_option_default_pingback_flag', function() {
			return null;
		}, 11 );

		$this->trackbacks_disabled = true;
	}

	/**
	 * Disable intermediate image sizes
	 *
	 */
	protected function disable_intermediate_images() {

		add_filter( 'intermediate_image_sizes_advanced', function( $sizes, $metadata ) {

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
		$string     = '';

		for ( $i = 0; $i < $tabs; $i++ ) {

			$string .= $single_tab;
		}

		return $string;
	}
}
