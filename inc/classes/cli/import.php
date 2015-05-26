<?php

namespace HMCI\CLI;

use HMCI\Master;

class Import extends \WP_CLI_Command {

	public function import( $args, $args_assoc ) {

		$args_assoc = wp_parse_args( $args_assoc, array(
			'count'       => -1,
			'offset'      => 0,
			'resume'      => false,
			'verbose'     => true,
			'dry-run'     => false,
			'export-path' => ''
		) );

		$import_type = $args[0];
		$importer    = Master::get_importer_instance( $import_type, $args_assoc );

		if ( ! $importer ) {
			$this->debug( $import_type . ' Is not a valid importer type', true );
		}

		$count_all = ( $importer->get_count() - $args_assoc['offset'] );
		$count     = ( $count_all < $args_assoc['count'] || $args_assoc['count'] == -1 ) ? $count_all : $args_assoc['count'];
		$offset    = (int) $args_assoc['offset'];

		$progress = new \cli\progress\Bar( __( 'Importing data', 'hmci' ), $count, 100 );

		$progress->display();

		while ( $items = $importer->get_items( $offset, $importer->args['items_per_loop'] ) ) {

			$progress->tick( count( $items ) );
			$importer->import_items( $items );
			$offset += count( $items );
		}

		$progress->finish();
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
			WP_CLI::Error( $output );
		} else {
			WP_CLI::Line( $output );
		}
	}
}
