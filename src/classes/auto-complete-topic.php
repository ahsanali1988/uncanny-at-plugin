<?php

namespace uncanny_advance_trainings;


/**
 * Class CertificateManagement
 * @package uncanny_advance_trainings
 */
class AutoCompleteTopic {
	/**
	 * CertificateManagement constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( __CLASS__, 'add_auto_complete_topic_page' ), 999 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts_func' ) );
		add_action( 'wp_ajax_search_user', array( __CLASS__, 'search_user' ), 33 );

	}


	public static function admin_enqueue_scripts_func() {
		if ( isset( $_GET['page'] ) && 'auto-complete-practice-period-course-topic' === $_GET['page'] ) {
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_register_script( 'user-autocomplete', plugins_url( 'assets/js/autocomplete.js', UO_AT_MAIN_FILE ), '', '1.0.2', true );
			wp_localize_script( 'user-autocomplete', 'ajax_url', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
			wp_localize_script( 'user-autocomplete', 'admin_url', array( 'url' => admin_url( 'admin.php?page=auto-complete-practice-period-course-topic' ) ) );
			wp_enqueue_script( 'user-autocomplete' );
		}
	}

	/**
	 *
	 */
	public static function add_auto_complete_topic_page() {
		add_submenu_page( 'certifications-management', 'Auto Complete Practice Period Course Topics', 'Auto Complete Practice Period Course Topics', 'manage_options', 'auto-complete-practice-period-course-topic', array(
			__CLASS__,
			'auto_complete_topics_func',
		) );
		// add_submenu_page( 'auto-complete-practice-period-course-topic', 'Auto Complete Practice Period Course Topics', 'Auto Complete Practice Period Course Topics', 'manage_options', 'auto-complete-practice-period-course-topic', array(
		// 	__CLASS__,
		// 	'auto_complete_topics_func',
		// ) );

	}

	public static function auto_complete_topics_func() {
		?>
        <div class="wrap">
            <h2>Auto Complete Practice Period Course Topics</h2>
			<?php
			$table = new CertificateManagementTable();
			$table->prepare_items('yes');
			?>
            <p>&nbsp;</p>
            <form method="get" action="">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <h3>Search:</h3>
                <p class="search-box" style="float:left;">
                    <input type="text" placeholder="Search by User ID, Name, Email or pk_Contact_ID"
                           style="width:400px;" name="uncanny-at-plugin-search-input-topic"
                           id="uncanny-at-plugin-search-input-topic" value=""/>
                </p>
				<?php //$table->search_box( 'Search User', 'uncanny-at-plugin' ); ?>
            </form>
			<?php
			$table->display();
			if ( isset( $_GET['user_id'] ) ) {
				// include( dirname( UO_AT_MAIN_FILE ) . '/src/templates/students-certifications.php' );
				// include( dirname( UO_AT_MAIN_FILE ) . '/src/templates/tally-table.php' );
				// include( dirname( UO_AT_MAIN_FILE ) . '/src/templates/course-records.php' );
			}
			?>
        </div>
		<?php

	}

	/**
	 *
	 */
	public static function search_user() {

		if ( isset( $_GET['term'] ) && ! empty( $_GET['term'] ) ) {
			$term = strtolower( $_GET['term'] );
		} elseif ( isset( $_GET['name'] ) && ! empty( $_GET['name'] ) ) {
			$term = strtolower( $_GET['name'] );
		} else {
			echo wp_json_encode( array() );
			die();
		}
		$suggestions = array();

		$loop = get_users( array( 'search' => "{$term}*" ) );
		if ( $loop ) {
			foreach ( $loop as $user ) {
				$suggestions[ $user->ID ] = array(
					'user_id'    => $user->ID,
					'label'      => $user->last_name . ', ' . $user->first_name . ' [' . $user->user_email . ']',
					'first_name' => $user->first_name,
					'last_name'  => $user->last_name,
					'user_email' => $user->user_email,
				);
			}
		}

		$meta_users = get_users( array(
			'meta_query' => array(
				array( 'key' => 'last_name', 'value' => $term, 'compare' => 'LIKE' ),
			),
		) );
		if ( $meta_users ) {
			foreach ( $meta_users as $user ) {
				$suggestions[ $user->ID ] = array(
					'user_id'    => $user->ID,
					'label'      => $user->last_name . ', ' . $user->first_name . ' [' . $user->user_email . ']',
					'first_name' => $user->first_name,
					'last_name'  => $user->last_name,
					'user_email' => $user->user_email,
				);
			}
		}

		$pk_Contact_ID = get_users( array(
			'meta_query' => array(
				array( 'key' => 'pk_Contact_ID', 'value' => $term, 'compare' => '=' ),
			),
		) );
		if ( $pk_Contact_ID ) {
			foreach ( $pk_Contact_ID as $user ) {
				$suggestions[ $user->ID ] = array(
					'user_id'    => $user->ID,
					'label'      => $user->last_name . ', ' . $user->first_name . ' [' . $user->user_email . ']',
					'first_name' => $user->first_name,
					'last_name'  => $user->last_name,
					'user_email' => $user->user_email,
				);
			}
		}
		$response = wp_json_encode( $suggestions );
		echo $response;
		die();
	}
}