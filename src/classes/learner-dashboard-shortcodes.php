<?php

namespace uncanny_advance_trainings;

/**
 * Class LearnerDashboardShortcodes
 * @package uncanny_advance_trainings
 */
class LearnerDashboardShortcodes {
	public function __construct() {
// 		ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
		add_shortcode( 'uo-list-courses', array( __CLASS__, 'uo_list_courses_func' ) );
		add_action( 'uo_course_certificate_pdf_url', array( __CLASS__, 'uo_course_certificate_pdf_url_func' ), 20, 4 );
		add_filter( 'sfwd_lms_has_access', array( $this, 'modify_course_access_behavior' ), 20, 3 );
	}

	/**
	 * @param $current
	 * @param $course_id
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function modify_course_access_behavior( $current, $course_id, $user_id ) {
		if ( $current ) {
			return $current;
		}
		if ( learndash_course_completed( $user_id, $course_id ) ) {
			return true;
		}

		return $current;
	}

	/**
	 * @param $http_link_to_file
	 * @param $user_id
	 * @param $course_id
	 * @param $time
	 */
	public static function uo_course_certificate_pdf_url_func( $http_link_to_file, $course_id, $time, $user_id ) {
		if ( ! $http_link_to_file || ! $user_id ) {
			return;
		}

		update_user_meta( $user_id, 'ceu_certificate_' . $course_id . '_' . $time, $http_link_to_file );
	}

	/**
	 * @param $atts
	 *
	 * @return string
	 */
	public static function uo_list_courses_func( $atts ) {
		$atts         = shortcode_atts( array(
			'category'     => 'core-myofascial-courses',
			'course_label' => 'Courses',
		), $atts, 'uo-list-courses' );
		$args         = array(
			'post_type'      => 'sfwd-courses',
			'posts_per_page' => 999,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'tax_query'      => array(
				array(
					'taxonomy' => 'ld_course_category',
					'field'    => 'slug',
					'terms'    => $atts['category'],
				),
			),
		);
		$course_label = $atts['course_label'];

		$posts = get_posts( $args );
		if ( $atts['category'] == 'subscription-catalog' ) {
			// Start output
			ob_start();

			// We're going to add a button to this banner that sends to the category permalink
			$category_data = get_term_by( 'slug', 'online-courses', 'product_cat' );
			$category_url  = get_category_link( $category_data->term_id );

			?>

			<div class="uo-dashboard-promotional-banner">
				<div class="uo-dashboard-promotional-banner__content">
					<div class="uo-dashboard-promotional-banner__title">
						<?php _e( 'Unlimited access to specially selected courses', 'advanced-trainings' ); ?>
					</div>
					<div class="uo-dashboard-promotional-banner__actions">
						<a href="/subscriptions/" class="uo-dashboard-promotional-banner__action">
							<?php _e( 'Learn More', 'advanced-trainings' ); ?>
						</a>
					</div>
				</div>
			</div>

			<?php

			// End output and append it to the $html variable
			$html .= ob_get_clean();
		}
		$html  .= '<div class="uo-dashboard-courses"><div class="uo-dashboard-courses__table"><div class="uo-dashboard-courses__head uncanny-courses-heading"><ul><li class="course-name">' . $course_label . '</li><li class="certificate-pdf">Status</li><li class="applied-to">Applied To</li></ul></div>';
		$html  .= '<div class="uo-dashboard-courses__body uncanny-courses learner-dashboard">';

		if ( $posts ) {
			if ( isset( $_GET['override_current_user'] ) ) {
				$user_id = absint( $_GET['override_current_user'] );
			} else {
				$user_id = wp_get_current_user()->ID;
			}
			foreach ( $posts as $course ) {
				$settings     = get_post_meta( $course->ID, '_sfwd-courses', true );
				$inner        = '';
				$course_title = '';
				$pdf          = '';
				//$applied_to   = '';
				//Utilities::log( [ '$course' => $course ], '', true, 'debug11' );

				if ( 'on' === $settings['sfwd-courses_global_dashboard'] ) {
					if ( ! sfwd_lms_has_access( $course->ID, $user_id ) ) {
						$permalink    = isset( $settings['sfwd-courses_purchase_url'] ) && ! empty( $settings['sfwd-courses_purchase_url'] ) ? $settings['sfwd-courses_purchase_url'] : get_permalink( $course->ID );
						$course_title = '<a href="' . $permalink . '" title="Purchase Course Access">' . $course->post_title . '</a>';
						$pdf          = '<a href="' . $permalink . '" class="btn btn-default learn-more">Learn More</a>';
					} elseif ( sfwd_lms_has_access( $course->ID, $user_id ) ) {
						$course_title = '<a href="' . get_permalink( $course->ID ) . '"><strong>' . $course->post_title . '</strong></a>';
						if ( learndash_course_completed( $user_id, $course->ID ) ) {
							$completion_time = get_user_meta( $user_id, 'course_completed_' . $course->ID, true );
							$course_certs    = get_user_meta( $user_id, '_uo-course-cert-' . $course->ID, true );
							$cert_url        = '#';
							if ( ! empty( $completion_time ) && ! empty( $course_certs ) ) {
								// $cert_url = self::return_cert_url( $user_id, $course->ID, $completion_time, false, false );
								$cert_url = learndash_get_course_certificate_link(  $course->ID,  $user_id);
							    $cert_url = $cert_url.'&cmpd='.$completion_time;
							}

							if ( (string) '#' === (string) $cert_url ) {
								//fallback to check for imported courses
								$cert_url = self::return_cert_url( $user_id, $course->ID, $completion_time, false, true );
							}
							$pdf = '<a href="' . $cert_url . '" title="Download PDF Certificate" target="_blank" class="certificate-pdf-anchor"><img title="Download PDF Certificate" alt="Download PDF Certificate" src="' . site_url() . '/wp-content/uploads/2018/01/cert.png" /></a>';
						} else {
							$pdf = '<a href="' . get_permalink( $course->ID ) . '"><strong>Enrolled</strong></a>';
						}
					}

				} elseif ( sfwd_lms_has_access( $course->ID, $user_id ) ) {
					$course_title = '<a title="View Course Contents" href="' . get_permalink( $course->ID ) . '"><strong>' . $course->post_title . '</strong></a>';
					if ( learndash_course_completed( $user_id, $course->ID ) ) {
						$completion_time = get_user_meta( $user_id, 'course_completed_' . $course->ID, true );
						$course_certs    = get_user_meta( $user_id, '_uo-course-cert-' . $course->ID, true );
						$cert_url        = '#';
						if ( ! empty( $completion_time ) && ! empty( $course_certs ) ) {
							// $cert_url = self::return_cert_url( $user_id, $course->ID, $completion_time, false, false );
							$cert_url = learndash_get_course_certificate_link(  $course->ID,  $user_id);
							$cert_url = $cert_url.'&cmpd='.$completion_time;
						}

						if ( (string) '#' === (string) $cert_url ) {
							//fallback to check for imported courses
							$cert_url = self::return_cert_url( $user_id, $course->ID, $completion_time, false, true );
						}
						$pdf = '<a href="' . $cert_url . '" title="Download PDF Certificate" target="_blank" class="certificate-pdf-anchor"><img title="Download PDF Certificate" alt="Download PDF Certificate" src="' . site_url() . '/wp-content/uploads/2018/01/cert.png" /></a>';
					} else {
						$pdf = '<a href="' . get_permalink( $course->ID ) . '"><strong>Enrolled</strong></a>';
					}
				}
				//Utilities::log( [ '$course_title' => $course_title, '$pdf' => $pdf, '$cert_url' => $cert_url ], '', true, 'debug112' );
				$applied_to     = ''; //TODO:: ADD Applied to logic
				$applied_to_raw = ''; //TODO:: ADD Applied to logic
				if ( learndash_course_completed( $user_id, $course->ID ) ) {
					$course_completed = get_user_meta( $user_id, 'course_completed_' . $course->ID, true );
					if ( empty( $course_completed ) ) {
						$course_completed = '';
					} else {
						$course_completed = '_' . $course_completed;
					}
					$applied_to_raw = get_user_meta( $user_id, 'certification_applied_to_' . $course->ID . $course_completed, true );
					if ( 911911911 !== (int) $applied_to_raw ) {
						if ( 888888 === (int) $applied_to_raw ) {
							$applied_to = 'Unapplied';
						} elseif ( ! empty( $applied_to_raw ) ) {
							$raw        = explode( '_', $applied_to_raw );
							$applied_to = get_the_title( $raw[0] );
							$term       = get_term_by( 'id', $raw[1], 'certification_category' );
							$applied_to .= ' - ' . $term->name;
						}
					}
				}
				if ( ! empty( $course_title ) ) {
					if ( 911911911 !== (int) $applied_to_raw ) {
						$inner .= '<ul>';
						$inner .= '<li class="course-name">' . $course_title . '</li>';
						$inner .= '<li class="certificate-pdf">' . $pdf . '</li>';
						$inner .= '<li class="applied-to">' . $applied_to . '</li>';
						$inner .= '</ul>';
					}
				}

				$ceus = LearnerDashboardShortcodes::get_ceu_course_details( $course->ID, $user_id );
				$enrolled_check = get_user_meta( $user_id, 'certification_start_date_' . $course->ID, true );
				//Utilities::log( [ '$ceus' => $ceus, '$inner' => $inner, '$applied_to' => $applied_to, '$course_completed' => $course_completed ], '', true, 'debug112' );
				$j = 1;
				if ( $ceus && $enrolled_check) {
					foreach ( $ceus as $date => $ceu ) {
						$course_completed = $date;

						if ( empty( $course_completed ) ) {
							$course_completed = '';
						} else {
							$course_completed = '_' . $course_completed;
						}
						$applied_to_raw = get_user_meta( $user_id, 'certification_applied_to_' . $course->ID . $course_completed, true );

						if ( 911911911 !== (int) $applied_to_raw ) {
							if ( 888888 === (int) $applied_to_raw ) {
								$applied_to = 'Unapplied';
							} elseif ( ! empty( $applied_to_raw ) ) {
								$raw        = explode( '_', $applied_to_raw );
								$applied_to = get_the_title( $raw[0] );
								$term       = get_term_by( 'id', $raw[1], 'certification_category' );
								$applied_to .= ' - ' . $term->name;

							}
							// $cert_url = self::return_cert_url( $user_id, $course->ID, $date, true );
							$cert_url = learndash_get_course_certificate_link(  $course->ID,  $user_id);
							$cert_url = $cert_url.'&cmpd='.$date;
							$pdf      = '<a href="' . $cert_url . '" title="Download PDF Certificate" target="_blank" class="certificate-pdf-anchor"><img title="Download PDF Certificate" alt="Download PDF Certificate" src="' . site_url() . '/wp-content/uploads/2018/01/cert.png" /></a>';
                            if ( ! empty( $course_title ) ) {
							$inner .= '<ul>';
							$inner .= '<li class="course-name">' . $course_title . '</li>';
							$inner .= '<li class="certificate-pdf">' . $pdf . '</li>';
							$inner .= '<li class="applied-to">' . $applied_to . '</li>';
							$inner .= '</ul>';
							}
						}
					}
				}
				// var_dump($atts['category']);
				
				$html .= $inner;
			}
		}

		$html .= '</div></div>';

		// Add promotional banner to the "Online Courses" category
		// First, check if the requested category it's the online-courses category
		if ( $atts['category'] == 'online-courses' ) {
			// Start output
			ob_start();

			// We're going to add a button to this banner that sends to the category permalink
			$category_data = get_term_by( 'slug', 'online-courses', 'product_cat' );
			$category_url  = get_category_link( $category_data->term_id );

			?>

            <div class="uo-dashboard-promotional-banner">
                <div class="uo-dashboard-promotional-banner__content">
                    <div class="uo-dashboard-promotional-banner__title">
						<?php _e( 'Discover more courses', 'advanced-trainings' ); ?>
                    </div>
                    <div class="uo-dashboard-promotional-banner__actions">
                        <a href="<?php echo $category_url; ?>" class="uo-dashboard-promotional-banner__action">
							<?php _e( 'Explore Online Courses', 'advanced-trainings' ); ?>
                        </a>
                    </div>
                </div>
            </div>

			<?php

			// End output and append it to the $html variable
			$html .= ob_get_clean();
		}
        if ( $atts['category'] == 'other-courses' ) {
			// Start output
			ob_start();

			// We're going to add a button to this banner that sends to the category permalink
			$category_data = get_term_by( 'slug', 'online-courses', 'product_cat' );
			$category_url  = get_category_link( $category_data->term_id );

			?>

            <div class="uo-dashboard-promotional-banner">
                <div class="uo-dashboard-promotional-banner__content">
                    <div class="uo-dashboard-promotional-banner__title">
						<?php _e( 'Discover more courses', 'advanced-trainings' ); ?>
                    </div>
                    <div class="uo-dashboard-promotional-banner__actions">
                        <a href="/workshops/" class="uo-dashboard-promotional-banner__action">
							<?php _e( 'Explore Other Courses', 'advanced-trainings' ); ?>
                        </a>
                    </div>
                </div>
            </div>

			<?php

			// End output and append it to the $html variable
			$html .= ob_get_clean();
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * @param $user_id
	 * @param $course_id
	 * @param $date
	 * @param bool $is_ceu
	 * @param bool $fallback
	 *
	 * @return mixed|string
	 */
	public static function return_cert_url( $user_id, $course_id, $date, $is_ceu = false, $fallback = false ) {
		//\uncanny_ceu\Utilities::log( [ $user_id, $course_id, $date, $is_ceu, $fallback ], $course_id.'-$user_id, $course_id, $date, $is_ceu, $fallback', true, 'urlcheck' );
		if ( $is_ceu || $fallback ) {
			$meta_key = 'ceu_certificate_' . $course_id . '_' . $date;

			return get_user_meta( $user_id, $meta_key, true );
		} else {
			$completion_time = get_user_meta( $user_id, 'course_completed_' . $course_id, true );
			$course_certs    = get_user_meta( $user_id, '_uo-course-cert-' . $course_id, true );
			$course_certs    = array_reverse( $course_certs );
			/*Utilities::log( [
				$completion_time,
				$course_certs
			], $course_id . '-$completion_time, $course_certs', true, 'urlcheck' );*/

			if ( ! empty( $completion_time ) && ! empty( $course_certs ) ) {
				foreach ( $course_certs as $certificate ) {
					if ( key_exists( $completion_time, $certificate ) ) {
						return str_replace( site_url(), 'https://s3.amazonaws.com/atcertificates', $certificate[ $completion_time ] );
					} else {
						foreach ( $certificate as $k => $v ) {
							$sim = similar_text( $completion_time, $k, $perc );
							//Difference in days (recent issue)
							$diff = ( $k - $completion_time ) / 60 / 1000;
							/*Utilities::log( [
								$sim,
								$diff,
								$completion_time,
								$k,
								$perc
							], $course_id . '-$sim, $diff, $completion_time, $k, $perc', true, 'urlcheck' );*/
							if ( $perc >= 80 || $diff < 6 ) {
								return str_replace( site_url(), 'https://s3.amazonaws.com/atcertificates', $v ) . '?v=perc';
							}
						}
					}
				}
			}
		}

		return '#';
	}


	/**
	 * @param $course_id
	 * @param $user_id
	 *
	 * @return array
	 */
	public static function get_ceu_course_details( $course_id, $user_id ) {
		global $wpdb;
		$return  = array();
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s", $user_id, 'ceu_date_%%_' . $course_id ) );
		/*\uncanny_ceu\Utilities::log( [
			$results,
			$course_id,
			$user_id
		], '$results, $course_id, $user_id', true, 'recheck' );*/
		if ( $results ) {
			foreach ( $results as $result ) {
				$course_completed = get_user_meta( $user_id, 'course_completed_' . $course_id, true );
				$sim              = similar_text( $course_completed, $result->meta_value, $perc );
				/*\uncanny_ceu\Utilities::log( [
					$results,
					$course_completed,
					$course_id,
					$sim,
					$perc,
					$result->meta_value
				], $user_id . '-$results, $course_completed, $course_id, $sim, $perc, $result->meta_value', true, 'recheck' );*/
				if ( (int) $result->meta_value !== (int) $course_completed ) {
					if ( $perc < 90 ) {
						$date                         = $result->meta_value;
						$return[ $date ]['completed'] = $date;
					}
				}
			}
		}

		return $return;

	}
}