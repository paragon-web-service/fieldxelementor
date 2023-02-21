<?php
/**
 * Class WSAL_Ext_MetadataTimestampProcessor.
 *
 * @since      4.3.0
 * @package    wsal
 * @subpackage external-db
 *
 * @see        \WSAL_Vendor\Monolog\Processor\MemoryProcessor::__construct() for options
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replaces the record timestamp with timestamp present in the context data. This is stored in GMT so we also set the
 * correct timezone in the process. It also removes the timestamp from the context data.
 *
 * @since      4.3.0
 * @package    wsal
 * @subpackage external-db
 *
 * @see        \WSAL_Vendor\Monolog\Processor\MemoryProcessor::__construct() for options
 */

if ( interface_exists( '\WSAL_Vendor\Monolog\Processor\ProcessorInterface' ) ) {
	class WSAL_Ext_MetadataTimestampProcessor implements \WSAL_Vendor\Monolog\Processor\ProcessorInterface {

		/**
		 * {@inheritDoc}
		 */
		public function __invoke( array $record ) {
			if ( ! empty( $record['context'] ) && array_key_exists( 'Timestamp', $record['context'] ) ) {
				/** @var \WSAL_Vendor\Monolog\DateTimeImmutable $event_created_datetime */
				$event_created_datetime = $record['datetime']->setTimestamp( $record['context']['Timestamp'] );
				$record['datetime']     = $event_created_datetime->setTimezone( wp_timezone() );
				unset( $record['context']['Timestamp'] );
			}

			return $record;
		}
	}
}
