<?php

namespace uncanny_advance_trainings;

/**
 * Class LearnDashGlobalSettings
 * @package uncanny_advance_trainings
 */
class LearnDashGlobalSettings {
	public function __construct() {
		//add_filter( 'sfwd-courses_display_settings', array( __CLASS__, 'change_course_setting' ), 9, 3 );
		add_filter( 'learndash_post_args', array( __CLASS__, 'add_global_setting' ), 9, 3 );
		add_filter( 'learndash_post_args', array( __CLASS__, 'add_purchase_url' ), 8, 3 );
	}

	/**
	 * @param $setting
	 *
	 * @return array
	 */
	/*public static function change_course_setting( $setting ) {

		$array['sfwd-courses_global_dashboard'] = array(
			'name'            => 'Show in Global Dashboard',
			'type'            => 'checkbox',
			'default'         => 0,
			'help_text'       => 'Show this course on Learners Dashboard if user is enrolled.',
			'initial_options' => 0,
			'nowrap'          => '',
			'label'           => '',
			'save'            => 1,
			'prefix'          => 1,
		);

		return array_merge( array_slice( $setting, 0, 2 ), $array, array_slice( $setting, 2 ) );
	}*/

	/**
	 * @param $args
	 *
	 * @return mixed
	 */
	public static function add_global_setting( $args ) {
		$courses                        = $args['sfwd-courses'];
		$fields                         = $courses['fields'];
		$new_field ['global_dashboard'] = array(
			'name'      => 'Show in Global Dashboard',
			'type'      => 'checkbox',
			'default'   => 0,
			'help_text' => 'Show this course on Learners Dashboard if user is enrolled.',
		);
		$args['sfwd-courses']['fields'] = array_merge( $new_field, $fields );

		return $args;
	}

	/**
	 * @param $args
	 *
	 * @return mixed
	 */
	public static function add_purchase_url( $args ) {
		$courses                        = $args['sfwd-courses'];
		$fields                         = $courses['fields'];
		$new_field ['purchase_url']     = array(
			'name'      => 'Dashboard Purchase URL',
			'type'      => 'text',
			'default'   => '',
			'help_text' => 'Use this url to redirect user to purchase course on Learners Dashboard.',
		);
		$args['sfwd-courses']['fields'] = array_merge( $new_field, $fields );

		return $args;
	}
}