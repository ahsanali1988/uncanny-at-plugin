<?php

namespace uncanny_advance_trainings;


/**
 * Class GravityFormsLogon
 * @package uncanny_advance_trainings
 */
class GravityFormsLogon {

	public function __construct() {
		add_action( 'gform_user_registered', array( $this, 'we_autologin_gfregistration' ), 20, 4 );

	}

	/**
	 * Auto login to site after GF User Registration Form Submittal
	 *
	 */
	function we_autologin_gfregistration( $user_id, $config, $entry, $password ) {
		wp_set_auth_cookie( $user_id, false, '' );
	}
}