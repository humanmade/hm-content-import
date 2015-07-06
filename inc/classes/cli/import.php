<?php

namespace HMCI\CLI;

use HMCI\Master;

class Import extends \WP_CLI_Command {

	public function import( $args, $args_assoc ) {

		$args_assoc = wp_parse_args( $args_assoc, array(
			'count'       => 0,
			'offset'      => 0,
			'resume'      => false,
			'verbose'     => false,
			'dry-run'     => false,
			'export-path' => ''
		) );

		$import_type = $args[0];
		$importer    = $this->get_importer( $import_type, $args_assoc );
		$count_all   = ( $importer->get_count() - $args_assoc['offset'] );
		$count       = ( $count_all < absint( $args_assoc['count'] ) || $args_assoc['count'] === 0 ) ? $count_all : absint( $args_assoc['count'] );
		$offset      = absint( $args_assoc['offset'] );
		$total       = $count + $offset;

		$progress = new \cli\progress\Bar( sprintf( __( 'Importing data for %s (%d items)', 'hmci' ), $import_type, $count ), $count, 100 );

		$progress->display();

		if ( $args_assoc['resume'] ) {
			$current_offset = $this->get_progress( $import_type );
			$progress->tick( $current_offset );
		} else {
			$current_offset = 0;
		}

		while ( ( $offset + $current_offset ) < $total && $items = $importer->get_items( $offset + $current_offset, $importer->args['items_per_loop'] ) ) {

			$importer->import_items( $items );
			$current_offset += count( $items );
            $progress->tick( count( $items ) );

			$this->save_progress( $import_type, $current_offset );


		}

		$this->clear_progress( $import_type );
		$progress->finish();
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

			$this->clear_local_object_cache();
		}

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

		if ( $exit_on_output ) {
			\WP_CLI::Error( $output );
		} else {
			\WP_CLI::Line( $output );
		}
	}

	protected function clear_progress( $import_type ) {

		delete_option( 'hmci_import_pg_' . md5( $import_type ) );
	}

	protected function save_progress( $import_type, $count ) {

		update_option( 'hmci_import_pg_' . md5( $import_type ), $count );
	}

	protected function get_progress( $import_type ) {

		return absint( get_option( 'hmci_import_pg_' . md5( $import_type ), 0 ) );
	}

	protected function clear_local_object_cache() {

		// Mitigate memory leak issues
		global $wp_object_cache;

		if ( ! empty( $wp_object_cache->cache ) ) {
			$wp_object_cache->cache = array();
		}
	}
}
