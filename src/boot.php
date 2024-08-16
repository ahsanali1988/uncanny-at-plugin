<?php

namespace uncanny_advance_trainings;

/**
 * Class Boot
 * @package uncanny_advance_trainings
 */
class Boot {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_learner_dashboard_css' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_admin_scripts' ) );
		//add_action( 'shutdown', array( __CLASS__, 'shutdown' ) );
	}

	public static function add_learner_dashboard_css() {
		global $post;
		if ( has_shortcode( $post->post_content, 'uo-list-courses' ) ) {
			wp_enqueue_style( 'learner-dashboard', plugins_url( 'assets/css/learner-dashboard.css', UO_AT_MAIN_FILE ), array(), '1.1', 'all' );
		}
	}

	public static function add_admin_scripts() {
		if ( isset( $_GET['page'] ) && ( 'certifications-management' === $_GET['page'] || 'auto-complete-practice-period-course-topic' === $_GET['page']) ) {
			wp_enqueue_style( 'certificate-management', plugins_url( 'assets/css/certificate-management.css', UO_AT_MAIN_FILE ), array(), '2.0', 'all' );
			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'jquery-ui-datepicker' );

			// You need styling for the datepicker. For simplicity I've linked to Google's hosted jQuery UI CSS.
			wp_register_style( 'jquery-ui', '//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'jquery-ui' );
		}
	}

	/**
	 *
	 */
	public static function init() {


		$includes_path = dirname( UO_AT_MAIN_FILE ) . '/src/classes/';

		include_once( $includes_path . 'event-extra-meta-fields.php' );
		new EventExtraMetaFields();

		include_once( $includes_path . 'learner-dashboard-shortcodes.php' );
		new LearnerDashboardShortcodes();

		include_once( $includes_path . 'learndash-global-settings.php' );
		new LearnDashGlobalSettings();

		include_once( $includes_path . 'certificate-management-table.php' );

		include_once( $includes_path . 'certificate-management.php' );
		new CertificateManagement();

		include_once( $includes_path . 'auto-complete-topic.php' );
		new AutoCompleteTopic();

		include_once( $includes_path . 'wp-data-tables.php' );
		new WpDataTables();

		include_once( $includes_path . 'credit-values.php' );
		new CreditValues();

		include_once( $includes_path . 'class-id-meta-box.php' );
		new ClassIDMetaBox();

		include_once( $includes_path . 'sabai-directory-mods.php' );
		new SabaiDirectoryMods();

		include_once( $includes_path . 'unassigned-courses.php' );
		new UnassignedCourses();

		include_once( $includes_path . 'woocommerce-mods.php' );
		new WoocommerceMods();

		include_once( $includes_path . 'tribe-custom-order.php' );
		new TribeCustomOrder();

		include_once( $includes_path . 'wp-profile-fields.php' );
		new WpProfileFields();

		include_once( $includes_path . 'add-to-directory-courses.php');
		new AddToDirectoryCourses();
	}

	/**
	 * @param $trace
	 * @param $trace_name
	 * @param string $file_name
	 */
	public static function log( $trace, $trace_name, $file_name = 'advanced-trainings' ) {
		$timestamp   = date( 'F d, Y H:i:s' );
		$boundary    = "\n===========================<<<< {$timestamp} >>>>===========================\n";
		$log_type    = "*******************************[[[[[[[[[[ {$trace_name} ]]]]]]]]]]*******************************\n";
		$log_end     = "\n===========================<<<< TRACE END >>>>===========================\n\n";
		$final_trace = print_r( $trace, true );
		$file        = WP_CONTENT_DIR . '/uo-' . $file_name . '.log';
		error_log( $boundary . $log_type . $final_trace . $log_end, 3, $file );
	}

	public static function shutdown() {
		global $wpdb;
		$log_file = fopen( ABSPATH . '/sql_log.txt', 'a' );
		fwrite( $log_file, "//////////////////////////////////////////\n\n" . date( "F j, Y, g:i:s a" ) . "\n" );
		foreach ( $wpdb->queries as $q ) {
			fwrite( $log_file, $q[0] . " - ($q[1] s)" . "\n\n" );
		}
		fclose( $log_file );
	}

}