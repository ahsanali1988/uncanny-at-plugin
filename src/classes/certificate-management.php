<?php

namespace uncanny_advance_trainings;


/**
 * Class CertificateManagement
 * @package uncanny_advance_trainings
 */
class CertificateManagement {
	/**
	 * CertificateManagement constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( __CLASS__, 'add_certificate_management_page' ), 999 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts_func' ) );
		add_action( 'wp_ajax_search_user', array( __CLASS__, 'search_user' ), 33 );
		add_action( 'wp_ajax_update_certification', array( __CLASS__, 'update_certification_func' ), 33 );
		add_action( 'wp_ajax_update_tally', array( __CLASS__, 'update_tally_func' ), 33 );
		add_action( 'wp_ajax_update_records', array( __CLASS__, 'update_records_func' ), 33 );
		add_action( 'wp_ajax_fetch_certification_notes', array( __CLASS__, 'fetch_certification_notes_func' ), 33 );
		add_action( 'wp_ajax_fetch_tally_notes', array( __CLASS__, 'fetch_tally_notes_func' ), 33 );
		add_action( 'wp_ajax_fetch_record_notes', array( __CLASS__, 'fetch_record_notes_func' ), 33 );
		add_action( 'wp_ajax_delete_ceu_record', array( __CLASS__, 'delete_ceu_record_func' ), 33 );

		add_action( 'uo_generate_course_certificate_content', array($this, 'modify_pdf_certificate_content'), 20, 3 );

	}

	public function modify_pdf_certificate_content( $content, $user_id, $course_id ) {
		//enter your modifications or use regex to modify content
		Boot::log( [ $content, $user_id, $course_id ], '$content, $user_id, $course_id', 'cert' );

		return $content;
	}

	public static function admin_enqueue_scripts_func() {
		if ( isset( $_GET['page'] ) && 'certifications-management' === $_GET['page'] ) {
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_register_script( 'user-autocomplete', plugins_url( 'assets/js/autocomplete.js', UO_AT_MAIN_FILE ), '', '1.0.2', true );
			wp_localize_script( 'user-autocomplete', 'ajax_url', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
			wp_localize_script( 'user-autocomplete', 'admin_url', array( 'url' => admin_url( 'admin.php?page=certifications-management' ) ) );
			wp_enqueue_script( 'user-autocomplete' );
		}
	}

	/**
	 *
	 */
	public static function add_certificate_management_page() {
		add_menu_page( 'Certifications Management', 'Certifications Management', 'manage_options', 'certifications-management', array(
			__CLASS__,
			'certifications_management_func',
		), 'dashicons-welcome-learn-more', 83 );

		add_submenu_page( 'certifications-management', 'Certifications Management', 'Certifications Management', 'manage_options', 'certifications-management', array(
			__CLASS__,
			'certifications_management_func',
		) );

	}

	public static function certifications_management_func() {
		?>
        <div class="wrap">
            <h2>Certifications Management</h2>
			<?php
			$table = new CertificateManagementTable();
			$table->prepare_items();
			?>
            <p>&nbsp;</p>
            <form method="get" action="">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <h3>Search:</h3>
                <p class="search-box" style="float:left;">
                    <input type="text" placeholder="Search by User ID, Name, Email or pk_Contact_ID"
                           style="width:400px;" name="uncanny-at-plugin-search-input"
                           id="uncanny-at-plugin-search-input" value=""/>
                </p>
				<?php //$table->search_box( 'Search User', 'uncanny-at-plugin' ); ?>
            </form>
			<?php
			$table->display();
			if ( isset( $_GET['user_id'] ) ) {
				include( dirname( UO_AT_MAIN_FILE ) . '/src/templates/students-certifications.php' );
				include( dirname( UO_AT_MAIN_FILE ) . '/src/templates/tally-table.php' );
				include( dirname( UO_AT_MAIN_FILE ) . '/src/templates/course-records.php' );
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

	/**
	 *
	 */
	public static function update_certification_func() {
		if ( isset( $_GET['user_id'] ) ) {
			$user_id    = absint( $_GET['user_id'] );
			$meta_key   = esc_attr( $_GET['meta_key'] );
			$meta_value = esc_attr( $_GET['meta_value'] );

			if ( strpos( $meta_key, 'date' ) ) {
				$meta_value = strtotime( $meta_value );
			}

			if ( isset( $_GET['is_textarea'] ) ) {
				$existing   = get_user_meta( $user_id, $meta_key, true );
				$meta_value = $existing . '<br />' . $meta_value;
			}

			global $wpdb;
			if ( isset( $_GET['is_select'] ) && ! empty( $_GET['umeta'] ) ) {
				$umeta = $_GET['umeta'];
				$wpdb->query( "UPDATE {$wpdb->usermeta} SET meta_value = '$meta_value' WHERE umeta_id = $umeta" );
			} else {
				$umeta = $wpdb->get_var( "SELECT umeta_id FROM $wpdb->usermeta WHERE meta_key = '{$meta_key}' AND user_id = {$user_id}" );
				if ( empty( $umeta ) ) {
					add_user_meta( $user_id, $meta_key, $meta_value );
				} else {
					$wpdb->query( "UPDATE {$wpdb->usermeta} SET meta_value = '$meta_value' WHERE umeta_id = $umeta" );
				}
			}
			$message = 'Data Saved.';
			if ( strpos( $meta_key, 'status' ) ) {
				$message = 'Status Saved.';
			} elseif ( strpos( $meta_key, 'lapse' ) ) {
				$message = 'Date Completed Saved.';
			} elseif ( strpos( $meta_key, 'notes' ) ) {
				$message = 'Notes Saved.';
			}
			echo $message;
			die();
		}
	}

	/**
	 *
	 */
	public static function update_records_func() {
		if ( isset( $_GET['user_id'] ) ) {
			$user_id    = absint( $_GET['user_id'] );
			$meta_key   = esc_attr( $_GET['meta_key'] );
			$meta_value = esc_attr( $_GET['meta_value'] );
			$course_id  = esc_attr( $_GET['course_id'] );
			$message    = 'Certification Applied.';

			if ( isset( $_GET['is_textarea'] ) ) {
				$existing   = get_user_meta( $user_id, $meta_key, true );
				$meta_value = $existing . '<br />' . $meta_value;
				$message    = 'Notes Saved.';
			}
			if ( 0 !== $meta_value ) {
				update_user_meta( $user_id, $meta_key, $meta_value );
			} else {
				delete_user_meta( $user_id, $meta_key );
			}

			$user        = new \WP_User( wp_get_current_user()->ID );
			$f_name      = strtolower( $user->first_name );
			$l_name      = strtolower( $user->last_name );
			$modified_by = date( 'm/d/Y', time() ) . substr( $f_name, 0, 1 ) . substr( $l_name, 0, 1 );
			update_user_meta( $user_id, 'record_last_modified_' . $course_id, $modified_by );

			echo $message;
			die();
		}
	}

	/**
	 *
	 */
	public static function delete_ceu_record_func() {
		if ( isset( $_GET['user_id'] ) ) {
			$user_id   = absint( $_GET['user_id'] );
			$course_id = esc_attr( $_GET['course_id'] );
			$time      = esc_attr( $_GET['time'] );
			$message   = 'Record Deleted.';
			if ( ! empty( $time ) && ! empty( $user_id ) && ! empty( $course_id ) ) {
				$keys = [
					"ceu_earned_{$time}_{$course_id}",
					"ceu_date_{$time}_{$course_id}",
					"ceu_certificate_{$course_id}_{$time}",
					"course_completed_{$course_id}",
					"event_course_completed_{$course_id}",
					"uo_event_completed_course_{$course_id}",
					//"_uo-course-cert-{$course_id}",
					"event_course_completed_{$course_id}",
					"uo_event_completed_course_{$course_id}"
				];
				if ( $keys ) {
					foreach ( $keys as $key ) {
						$backup_data [ $key ] = get_user_meta( $user_id, $key, true );
					}
					update_user_meta( $user_id, "bak_deleted_record_{$time}_{$course_id}", $backup_data );

					//Delete Data now
					foreach ( $keys as $key ) {
						if ( $key !== "course_completed_{$course_id}" ) {
							delete_user_meta( $user_id, $key );
						}
					}
					delete_user_meta( $user_id, "course_completed_{$course_id}", $time );
				}

				/*$backup_data = [
					"ceu_earned_{$time}_{$course_id}" => get_user_meta( $user_id, "ceu_earned_{$time}_{$course_id}", true ),
					"ceu_date_{$time}_{$course_id}"   => get_user_meta( $user_id, "ceu_date_{$time}_{$course_id}", true ),
					get_user_meta( $user_id, "ceu_title_{$time}_{$course_id}", true ),
					get_user_meta( $user_id, "ceu_course_{$time}_{$course_id}", true ),
					get_user_meta( $user_id, "ceu_certificate_{$course_id}_{$time}", true ),
					get_user_meta( $user_id, "course_completed_{$course_id}", true ),
					get_user_meta( $user_id, "event_course_completed_{$course_id}", true ),
					get_user_meta( $user_id, "uo_event_completed_course_{$course_id}", true ),
					get_user_meta( $user_id, '_uo-course-cert-' . $course_id, true ),
				];*/

				///Delete CEU Records
				/*delete_user_meta( $user_id, "ceu_earned_{$time}_{$course_id}" );
				delete_user_meta( $user_id, "ceu_date_{$time}_{$course_id}" );
				delete_user_meta( $user_id, "ceu_title_{$time}_{$course_id}" );
				delete_user_meta( $user_id, "ceu_course_{$time}_{$course_id}" );
				delete_user_meta( $user_id, "ceu_certificate_{$course_id}_{$time}" );


				//Delete Event Check-In Records
				delete_user_meta( $user_id, "event_course_completed_{$course_id}" );
				delete_user_meta( $user_id, "uo_event_completed_course_{$course_id}" );*/

				//Delete Certificate Key
				/*$current_certs = get_user_meta( $user_id, '_uo-course-cert-' . $course_id, true );
				if ( ! empty( $current_certs ) && key_exists( $time, $current_certs ) ) {
					unset( $current_certs[ $time ] );
					update_user_meta( $user_id, '_uo-course-cert-' . $course_id, $current_certs );
				}*/
			}

			$user        = new \WP_User( wp_get_current_user()->ID );
			$f_name      = strtolower( $user->first_name );
			$l_name      = strtolower( $user->last_name );
			$modified_by = date( 'm/d/Y', time() ) . substr( $f_name, 0, 1 ) . substr( $l_name, 0, 1 );
			update_user_meta( $user_id, 'record_last_modified_' . $course_id, $modified_by );

			echo $message;
			die();
		}
	}

	/**
	 *
	 */
	public static function update_tally_func() {
		if ( $_GET['user_id'] ) {
			$user_id        = absint( $_GET['user_id'] );
			$meta_key       = esc_attr( $_GET['meta_key'] );
			$meta_value     = esc_attr( $_GET['meta_value'] );
			$certificate_id = esc_attr( $_GET['certificate_id'] );
			$term_id        = esc_attr( $_GET['term_id'] );
			if ( isset( $_GET['is_textarea'] ) ) {
				$existing   = get_user_meta( $user_id, $meta_key, true );
				$meta_value = $existing . '<br />' . $meta_value;
			}
			update_user_meta( $user_id, $meta_key, $meta_value );
			$user        = new \WP_User( wp_get_current_user()->ID );
			$f_name      = strtolower( $user->first_name );
			$l_name      = strtolower( $user->last_name );
			$modified_by = date( 'm/d/Y', time() ) . substr( $f_name, 0, 1 ) . substr( $l_name, 0, 1 );
			update_user_meta( $user_id, 'certification_last_modified_' . $certificate_id . '_' . $term_id, $modified_by );
			$message = 'Data Saved.';
			if ( strpos( $meta_key, 'units' ) ) {
				$message = 'Units Earned Saved.';
			} elseif ( strpos( $meta_key, 'notes' ) ) {
				$message = 'Notes Saved.';
			}
			echo $message;
			die();
		}
	}

	/**
	 *
	 */
	public static function fetch_certification_notes_func() {
		echo get_user_meta( absint( $_GET['user_id'] ), esc_attr( $_GET['meta_key'] ), true );
		die();
	}

	/**
	 *
	 */
	public static function fetch_tally_notes_func() {
		echo get_user_meta( absint( $_GET['user_id'] ), esc_attr( $_GET['meta_key'] ), true );
		die();
	}

	/**
	 *
	 */
	public static function fetch_record_notes_func() {
		echo get_user_meta( absint( $_GET['user_id'] ), esc_attr( $_GET['meta_key'] ), true );
		die();
	}

	/**
	 * @param $user_email
	 *
	 * @return array
	 */
	public static function get_certification_records_for_directory( $user_email ) {
		if ( email_exists( $user_email ) ) {
			$user         = get_user_by( 'email', $user_email );
			$user_id      = $user->ID;
			$posts        = get_posts( array(
				'post_type'      => 'certification',
				'posts_per_page' => 99,
				'order_by',
				'post_title',
				'order'          => 'ASC'
			) );
			$return_empty = true;
			$user_certs   = array( 'certified' => array(), 'lapsed' => array(), 'enrolled' => array(), );
			if ( $posts ) {
				foreach ( $posts as $cert ) {
					$cert_id = $cert->ID;
					$status  = get_user_meta( $user_id, 'certification_status_' . $cert_id, true );
					if ( ! empty( $status ) && 'Not Yet Enrolled' !== $status ) {
						$user_certs[ $status ][] = $cert->post_title;
						$return_empty            = false;
					}
				}
			}

			if ( true === $return_empty ) {
				return array();
			} else {
				return $user_certs;
			}
		} else {
			return array();
		}
	}
}