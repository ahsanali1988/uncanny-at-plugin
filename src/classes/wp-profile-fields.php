<?php

namespace uncanny_advance_trainings;


/**
 * Class WpProfileFields
 * @package uncanny_advance_trainings
 */
class WpProfileFields {

	public $fields;

	public function __construct() {
		add_action( 'show_user_profile', array( $this, 'extra_user_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'extra_user_profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_extra_user_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_extra_user_profile_fields' ) );
		$this->fields = [
			'pk_Contact_ID',
			'prefix',
			'first_name',
			'middle_name',
			'last_name',
			'nickname',
			'user_nicename',
			'title',
			'company',
			'primary_address_source',
			'primary_address_type',
			'secondary_address_source',
			'secondary_address_type',
			'user_address_line_1',
			'user_address_line_2',
			'user_city',
			'user_country',
			'user_state',
			'user_postcode',
			'secondary_address',
			'secondary_address2',
			'secondary_city',
			'secondary_country',
			'secondary_state',
			'secondary_zip',
			'billing_phone',
			'shipping_phone',
			'phone2',
			/*'billing_first_name',
			'billing_last_name',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_country',
			'billing_state',
			'billing_postcode',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_country',
			'shipping_state',
			'shipping_postcode',*/
			'email',
			'birthdate',
			'comments',
			'store_customer_id',
			'state_license_expiry',
			'state_license_id',
			'state_license_state',
			'state_license_status',
			'state_license_type',
			'tag_alumni',
			'tag_session_provider',
			'tag_assistant',
			'tag_assistant_lapsed',
			'tag_inquiry',
			'tag_website_listed',
			'tag_website_no_listing',
			'tag_no_calls',
			'tag_no_emails',
			'tag_no_mail',
			'tag_video_owner',
			'email_url1',
			'email_url2',
			'email_url3',
			//Additional Fields
			'referral_source',
			'date_of_last_contact',
			'regonline_participant_number',
		];
	}

	function extra_user_profile_fields( $user ) { ?>
	<h3><?php _e( "Multiple Completion", "uncanny-owl" ); ?></h3>
			<?php
		
			$LearnerDashboardShortcodes = new LearnerDashboardShortcodes();
		
			$args         = array(
				'post_type'      => 'sfwd-courses',
				'posts_per_page' => 999,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			);
			// $course_label = $atts['course_label'];
	
			$posts = get_posts( $args );
			$inner        = '';
			// $html  .= '<div class="uo-dashboard-courses"><div class="uo-dashboard-courses__table"><div class="uo-dashboard-courses__head uncanny-courses-heading"><ul><li class="course-name">Course Name</li><li class="certificate-pdf">Status</li><li class="applied-to">Applied To</li></ul></div>';
		// $html  .= '<div class="uo-dashboard-courses__body uncanny-courses learner-dashboard">';
			foreach ( $posts as $course ) {
				// echo 'adasdads';
				$ceus = $LearnerDashboardShortcodes->get_ceu_course_details( $course->ID, $user->ID );
				
				$j = 1;
				if ( $ceus ) {
					// var_dump($ceus);
					// return;
					foreach ( $ceus as $date => $ceu ) {
						$course_completed = $date;

						if ( empty( $course_completed ) ) {
							$course_completed = '';
						} else {
							$course_completed = '_' . $course_completed;
						}
						$applied_to_raw = get_user_meta( $user->ID , 'certification_applied_to_' . $course->ID . $course_completed, true );

						if ( 911911911 !== (int) $applied_to_raw ) {
							if ( 888888 === (int) $applied_to_raw ) {
								$applied_to = 'Unapplied';
							} elseif ( ! empty( $applied_to_raw ) ) {
								$raw        = explode( '_', $applied_to_raw );
								$applied_to = get_the_title( $raw[0] );
								$term       = get_term_by( 'id', $raw[1], 'certification_category' );
								// $applied_to .= ' - ' . $term->name;

							}
							// $cert_url = self::return_cert_url( $user_id, $course->ID, $date, true );
							$cert_url = learndash_get_course_certificate_link(  $course->ID,  $user->ID );
							$cert_url = $cert_url.'&cmpd='.$date;
							$pdf      = '<a href="' . $cert_url . '" title="Download PDF Certificate" target="_blank" class="certificate-pdf-anchor"><img title="Download PDF Certificate" alt="Download PDF Certificate" src="' . site_url() . '/wp-content/uploads/2018/01/cert.png" /></a>';
                            if ( ! empty( $course->post_title ) ) {
							$inner .= '<ul>';
							$inner .= '<li class="course-name">' . $course->post_title .' '. $pdf .' ( '.date('d-m-Y',(int)$date) .' )</li>';
							// $inner .= '<li class="certificate-pdf">' . $pdf . '</li>';
							// $inner .= '<li class="applied-to">' . $applied_to . '</li>';
							$inner .= '</ul>';
						
							}
						}
					}
				}
			
			}
			echo $inner;
			// echo $html .= '</div></div>';

			
			?>
		<h3><?php _e( "Extra profile information", "uncanny-owl" ); ?></h3>

		<table class="form-table">
			<?php foreach ( $this->fields as $meta_key ) { ?>
				<?php
				$label = ucwords( str_replace( '_', ' ', $meta_key ) );
				?>
				<tr>
					<th><label for="<?php echo $meta_key ?>"><?php echo $label; ?></label></th>
					<td>
						<textarea name="<?php echo $meta_key ?>" id="<?php echo $meta_key ?>"><?php echo esc_attr( get_the_author_meta( $meta_key, $user->ID ) ); ?></textarea>
					</td>
				</tr>
			<?php } ?>
			
			
			<!--<tr>
				<th><label for="pk_Contact_ID"><?php /*_e( "pk_Contact_ID" ); */ ?></label></th>
				<td>
					<input type="text" name="pk_Contact_ID" id="pk_Contact_ID" value="<?php /*echo esc_attr( get_the_author_meta( 'pk_Contact_ID', $user->ID ) ); */ ?>" class="regular-text"/><br/>
					<span class="description"><?php /*_e( "Please enter your pk_Contact_ID." ); */ ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="Phone2"><?php /*_e( "Phone2" ); */ ?></label></th>
				<td>
					<input type="text" name="Phone2" id="Phone2" value="<?php /*echo esc_attr( get_the_author_meta( 'phone2', $user->ID ) ); */ ?>" class="regular-text"/><br/>
					<span class="description"><?php /*_e( "Please enter your Phone2." ); */ ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="Email_URL_2"><?php /*_e( "Email URL 2" ); */ ?></label></th>
				<td>
					<input type="text" name="Email_URL_2" id="Email_URL_2" value="<?php /*echo esc_attr( get_the_author_meta( 'email_url2', $user->ID ) ); */ ?>" class="regular-text"/><br/>
					<span class="description"><?php /*_e( "Please enter your Email URL 2." ); */ ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="Email_URL_3"><?php /*_e( "Email URL 3" ); */ ?></label></th>
				<td>
					<input type="text" name="Email_URL_3" id="Email_URL_3" value="<?php /*echo esc_attr( get_the_author_meta( 'email_url3', $user->ID ) ); */ ?>" class="regular-text"/><br/>
					<span class="description"><?php /*_e( "Please enter your postal code." ); */ ?></span>
				</td>
			</tr>-->
		</table>
	<?php }

	/**
	 * @param $user_id
	 *
	 * @return bool
	 */
	function save_extra_user_profile_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}
		foreach ( $this->fields as $meta_key ) {
			if(isset($_POST[$meta_key])){
				update_user_meta( $user_id, $meta_key, $_POST[$meta_key] );
			}
		}
		/*update_user_meta( $user_id, 'pk_Contact_ID', $_POST['pk_Contact_ID'] );
	update_user_meta( $user_id, 'phone2', $_POST['Phone2'] );
	update_user_meta( $user_id, 'email_url2', $_POST['Email_URL_2'] );
	update_user_meta( $user_id, 'email_url3', $_POST['Email_URL_3'] );*/
	}
}
