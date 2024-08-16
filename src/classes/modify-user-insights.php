<?php

namespace uncanny_advance_trainings;

/**
 * Class ModifyUserInsights
 * @package uncanny_advance_trainings
 */
class ModifyUserInsights {
	/**
	 * ModifyUserInsights constructor.
	 */
	public function __construct() {
		add_filter( 'usin_user_db_data', [ $this, 'usin_user_db_data' ], 99 );
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	function usin_user_db_data( $data ) {
		if ( $data ) {
			$data = (array) $data;
			//Boot::log( $data, '$data-before', 'data' );
			foreach ( $data as $k => $v ) {
				if ( strpos( $k, 'certification_lapse_date_' ) ) {
					$data[ $k ] = date( 'F d, Y', $v );
				}
			}
			$data = (object) $data;
			//Boot::log( $data, '$data-after', 'data' );
		}

		return $data;
	}
}