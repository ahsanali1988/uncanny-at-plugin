<?php

namespace uncanny_advance_trainings;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class CertificateManagementTable
 * @package uncanny_advance_trainings
 */
class CertificateManagementTable extends \WP_List_Table {
    
	var $auto_complete_topics = array();
	var $example_data = array();

	/**
	 * CertificateManagementTable constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'wp_list_text_link', //Singular label
			'plural'   => 'wp_list_test_links', //plural label, also this well be one of the table css class
			'ajax'     => false //We won't support Ajax for this table
		) );
		if ( isset( $_GET['topics'] ) && ! empty( $_GET['topics'] ) ) {
			$user_id            = absint( $_GET['user_id'] );
			$user               = new \WP_User( $user_id );
			$course_id = 89182;
			$course_lessons_ids = array();
			$course_lessons     = learndash_get_lesson_list( $course_id );
			if ( ! empty( $course_lessons ) ) {
				$course_lessons_ids = wp_list_pluck( $course_lessons, 'ID' );
			}
			if (count($course_lessons_ids) > 0) {
				$lesson_topics = array();
				$topics_ids = array();
                foreach ($course_lessons_ids as $course_lessons_id) {
					$lesson_topic_ids = array();
					$lesson_topics = learndash_get_topic_list($course_lessons_id, $course_id);
					if ( ! empty( $lesson_topics ) ) {
						$lesson_topic_ids = wp_list_pluck( $lesson_topics, 'ID' );
					}
					if ( ! empty( $lesson_topic_ids ) ) {
						foreach ($lesson_topic_ids as $lesson_topic_id) {
							$topics_ids[] = $lesson_topic_id;
						}
				}
			}
			foreach ($topics_ids as $topics_id) {
				$status = '';
				if ( ! learndash_is_topic_complete( $user_id, $topics_id ) ) {
					$status = 'Not completed';
				}
				if(empty($status))
				{
					$usermeta = get_user_meta($user_id , 'auto_comp_topic_'.$topics_id,true);
					if($usermeta)
					{
						$status = 'Auto completed';
					}else{
						$status = 'Completed by form';
					}
				}
				$this->auto_complete_topics[] = array(
					'topic'    => get_the_title($topics_id),
					'status'      => $status,
				);
			
			}
			
		}
	}
		if ( isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ) {
			$user_id            = absint( $_GET['user_id'] );
			$user               = new \WP_User( $user_id );
			$this->example_data = array(
				array(
					'wordpress_id'    => $user_id,
					'first_name'      => $user->first_name,
					'last_name'       => $user->last_name,
					'email_address'   => $user->user_email,
					'profile'         => get_edit_user_link( $user_id ),
					'active_campaign' => '',
				),
			);
		}
	}
	

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'wordpress_id'    => 'ID',
			'first_name'      => 'First Name',
			'last_name'       => 'Last Name',
			'email_address'   => 'Email Address',
			'profile'         => 'Profile',
			'active_campaign' => 'Active Campaign',
		);

		return $columns;
	}
/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 *
	 * @return array
	 */
	public function get_column_topics() {
		$columns = array(
			'topic'    => 'Topic',
			'status'      => 'Status',
		);

		return $columns;
	}
	/**
	 *
	 */
	public function prepare_items($status = '') {
		if($status)
		{
	    $columns               = $this->get_column_topics();	
		}else{
		$columns               = $this->get_columns();
		}
		$hidden                = array();
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		if($status)
		{
			$this->items           = $this->auto_complete_topics;
		}else{
			$this->items           = $this->example_data;
		}
	}

	/**
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return mixed|string|void
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'topic':
			case 'status':
			case 'wordpress_id':
			case 'first_name':
			case 'last_name':
			case 'email_address':
			case 'active_campaign':
				return $item[ $column_name ];
			case 'profile':
				return '<a href="' . $item[ $column_name ] . '" target="_blank">Edit Profile</a>';
			default:
				return print_r( $item, true );
		}
	}
}