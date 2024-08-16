<?php


namespace uncanny_advance_trainings;


/**
 * Class WoocommerceMods
 * @package uncanny_advance_trainings
 */
class WoocommerceMods {
	public static $url = 'https://advanced-trainings.api-us1.com';
	public static $key = 'c3d462d2d0753aa0ce748248210bde72c155d75c615a1efc3ba39f2bd0b547199d314a33';

	/**
	 * WoocommerceMods constructor.
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'add_css_class' ) );
		add_action( 'wp_footer', array( $this, 'add_js_handler' ) );
		add_filter( 'woocommerce_order_button_html', array( $this, 'update_place_order' ) );

		add_action( 'woocommerce_review_order_before_payment', array( $this, 'custom_field_visit_source' ), 10 );
		add_action( 'woocommerce_checkout_process', array( $this, 'signup_source_validation' ), 22 );
		add_action( 'woocommerce_checkout_process', array( $this, 'signup_source_details_validation' ), 32 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_order_meta' ), 32 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'add_source_details' ), 30, 1 );
		add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'wc_csv_modify_headers' ) );
		add_filter( 'wc_customer_order_csv_export_order_row', array( $this, 'wc_csv_modify_row_data' ), 10, 3 );
		add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'remove_order_notes' ), 99 );

		add_action( 'woocommerce_checkout_after_terms_and_conditions', array( $this, 'add_additional_optins' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'handle_optins' ) );

		add_filter( 'woocommerce_account_menu_items', array( $this, 'email_pref_links' ) );
		add_filter( 'woocommerce_get_endpoint_url', array( $this, 'email_pref_links_urls' ), 20, 4 );

		add_action( 'init', array( $this, 'add_endpoint' ), 0 );
		add_filter( 'wc_get_template', array( $this, 'custom_endpoint' ), 20, 5 );

		add_action( 'init', array( $this, 'save_email_preferences' ) );

		/* Disable tickets emails for Woo */
		add_filter( 'wootickets-tickets-email-enabled', '__return_false' );

		/* Remove the message 'You'll receive your tickets in another email' from the Woo Order email */
		add_filter( 'wootickets_email_message', '__return_empty_string' );

		// add_filter( 'woocommerce_default_catalog_orderby', '__return_popularity' );
		add_filter( 'woocommerce_is_sold_individually', '__return_true' );
	}

	/**
	 *
	 */
	function add_endpoint() {
		$mask = $this->get_endpoints_mask();
		add_rewrite_endpoint( 'show-events', $mask );
		add_rewrite_endpoint( 'user-email-preferences', $mask );
	}

	/**
	 * @return int
	 */
	function get_endpoints_mask() {
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$page_on_front     = get_option( 'page_on_front' );
			$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
			$checkout_page_id  = get_option( 'woocommerce_checkout_page_id' );
			if ( in_array( $page_on_front, array( $myaccount_page_id, $checkout_page_id ), true ) ) {
				return EP_ROOT | EP_PAGES;
			}
		}

		return EP_PAGES;
	}

	/**
	 * @param $located
	 * @param $template_name
	 * @param $args
	 * @param $template_path
	 * @param $default_path
	 *
	 * @return string
	 */
	function custom_endpoint( $located, $template_name, $args, $template_path, $default_path ) {

		if ( $template_name == 'myaccount/my-account.php' ) {
			global $wp_query;
			if ( isset( $wp_query->query['user-email-preferences'] ) ) {
				$located = dirname( UO_AT_MAIN_FILE ) . '/src/templates/email-pref.php';
			}
		}

		if ( $template_name == 'myaccount/my-account.php' ) {
			global $wp_query;
			if ( isset( $wp_query->query['show-events'] ) ) {
				$located = dirname( UO_AT_MAIN_FILE ) . '/src/templates/events.php';
			}
		}

		return $located;
	}

	/**
	 *
	 */
	function add_css_class() {
		if ( is_checkout() ) {
			?>
			<style>
				.input-hidden {
					display: none;
				}
			</style>
			<?php
		}
	}

	/**
	 *
	 */
	function add_js_handler() {
		if ( is_checkout() ) {
			?>
			<script>
              jQuery('#signup_source').change(function () {
                var val = jQuery(this).val()
                switch (val) {
                  case 'Email from...':
                    show_hide_source_details('Enter Sender’s Name', 1)
                    break
                  case 'Website...':
                    show_hide_source_details('Enter Site Name', 1)
                    break
                  case 'Ad in...':
                    show_hide_source_details('Enter Details...', 1)
                    break
                  case 'Referral from...':
                    show_hide_source_details('Enter Name', 1)
                    break
                  case 'Other...':
                    show_hide_source_details('Enter Description...', 1)
                    break
                  default:
                    show_hide_source_details('', 0, 1)
                    break
                }
              })

              function show_hide_source_details (placeholder = 'Enter Details...', show = 1, hide = 0) {
                if (1 === show) {
                  jQuery('#signup_source_details_field').removeClass('input-hidden').show().attr('required', 'required')
                } else if (1 === hide) {
                  jQuery('#signup_source_details_field').addClass('input-hidden').hide().removeAttr('required')
                }
                jQuery('#signup_source_details').attr('placeholder', placeholder)
              }

			</script>
			<?php
		}
	}


	/**
	 * @param $html
	 *
	 * @return string
	 */
	public function update_place_order( $html ) {
		$order_button_text = __( 'Place Order', 'woocommerce' );

		return '<input type="submit" class="button alt" name="woocommerce_checkout_place_order" disabled="disabled" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '" />';
	}

	/**
	 *
	 */
	public function add_additional_optins() {
		?>
		<p style='display:none'>
		<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="sign-up-at-newsletter" id="sign-up-at-newsletter" checked/>
		<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="marketing-related-emails" id="marketing-related-emails" checked/>
		</p>
		<!-- <p class="form-row terms wc-sign-up-at-newsletter">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="sign-up-at-newsletter" id="sign-up-at-newsletter"/>
				<span><?php _e( 'OK to send me the Advanced-Trainings Newsletter (articles, techniques, announcements).', 'woocommerce' ) ?></span>
			</label>
		</p>
		<p class="form-row terms wc-marketing-related-emails">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="marketing-related-emails" id="marketing-related-emails"/>
				<span><?php _e( 'OK to send me emails related to my purchase (course followup info, product updates, etc).', 'woocommerce' ); ?></span>
			</label>
		</p> -->
		<!-- <p>
			<i>
				<small><?php _e( 'You can easily stop receiving emails at any time.', 'woocommerce' ) ?></small>
			</i></p> -->
		<?php
	}

	/**
	 * @param $order_id
	 */
	public function handle_optins( $order_id ) {
		if ( isset( $_POST['sign-up-at-newsletter'] ) ) {
			$this->add_user_to_active_campaign( $order_id );
		}
	}


	/**
	 * @param $fields
	 *
	 * @return mixed
	 */

	function custom_field_visit_source() {
		?>
		<div class="uo-checkout-source"> <?php

			woocommerce_form_field( 'signup_source', array(
				'label'    => __( 'What prompted your visit to our site? Select FIRST or primary source', 'uncanny-owl' ),
				'required' => true,
				'type'     => 'select',
				'priority' => '300',
				'class'    => array( 'form-row-wide' ),
				'options'  => array(
					'0'                 => __( 'Choose a source', 'uncanny-owl' ),
					'I don\'t remember' => __( 'I don\'t remember', 'uncanny-owl' ),
					'Email from...'     => __( 'Email from...', 'uncanny-owl' ),
					'Website...'        => __( 'Website...', 'uncanny-owl' ),
					'Ad in...'          => __( 'Ad in...', 'uncanny-owl' ),
					'Referral from...'  => __( 'Referral from...', 'uncanny-owl' ),
					'Postcard'          => __( 'Postcard', 'uncanny-owl' ),
					'Facebook'          => __( 'Facebook', 'uncanny-owl' ),
					'YouTube'           => __( 'YouTube', 'uncanny-owl' ),
					'Other...'          => __( 'Other...', 'uncanny-owl' ),
				),
			) );

			woocommerce_form_field( 'signup_source_details', array(
				'label'       => __( 'Enter Details', 'uncanny-owl' ),
				'placeholder' => __( 'Enter Details...', 'uncanny-owl' ),
				'required'    => true,
				'type'        => 'text',
				'priority'    => '301',
				'class'       => array( 'form-row-wide', 'input-hidden' ),
			) );

			?> </div> <?php
	}

	/**
	 *
	 */
	function signup_source_validation() {
		if ( ! $_POST['signup_source'] || 0 === $_POST['signup_source'] ) {
			wc_add_notice( __( 'Please select a First or Primary Source before you submit order.', 'uncanny-owl' ), 'error' );
		}
	}

	/**
	 *
	 */
	function signup_source_details_validation() {

		if ( isset( $_POST['signup_source'] ) && ! empty( $_POST['signup_source'] ) ) {
			switch ( $_POST['signup_source'] ) {
				case 'Email from...':
				case 'Website...':
				case 'Ad in...':
				case 'Referral from...':
				case 'Other...':
					if ( ! isset( $_POST['signup_source_details'] ) || empty( $_POST['signup_source_details'] ) ) {
						wc_add_notice( __( 'Please fill in Primary Source Details before you submit order.', 'uncanny-owl' ), 'error' );
					}
					break;
			}
		}
	}

	/**
	 * @param $order_id
	 */
	function update_order_meta( $order_id ) {
		if ( isset( $_POST['signup_source'] ) && 0 !== $_POST['signup_source'] ) {
			update_post_meta( $order_id, 'uo-signup-source', sanitize_text_field( $_POST['signup_source'] ) );
		}

		if ( isset( $_POST['signup_source_details'] ) && ! empty( $_POST['signup_source_details'] ) ) {
			update_post_meta( $order_id, 'uo-signup-source-details', sanitize_text_field( $_POST['signup_source_details'] ) );
		}

		/*if ( isset( $_POST['referral_details'] ) && ! empty( $_POST['referral_details'] ) ) {
			update_post_meta( $order_id, 'uo-referral-details', sanitize_text_field( $_POST['referral_details'] ) );
		}*/
	}

	/**
	 * @param $order
	 */
	function add_source_details( $order ) {
		if ( ! empty( get_post_meta( $order->id, 'uo-signup-source', true ) ) ) {
			echo '<p><strong>' . __( 'Primary Source', 'uncanny-owl' ) . ':</strong> ' . get_post_meta( $order->id, 'uo-signup-source', true ) . '</p>';
		}

		if ( ! empty( get_post_meta( $order->id, 'uo-signup-source-details', true ) ) ) {
			echo '<p><strong>' . __( 'Source Details', 'uncanny-owl' ) . ':</strong> ' . get_post_meta( $order->id, 'uo-signup-source-details', true ) . '</p>';
		}

		/*if ( ! empty( get_post_meta( $order->id, 'uo-referral-details', true ) ) ) {
			echo '<p><strong>' . __( 'Referral Details', 'uncanny-owl' ) . ':</strong> ' . get_post_meta( $order->id, 'uo-referral-details', true ) . '</p>';
		}*/
	}

	/**
	 * @param $column_headers
	 *
	 * @return array
	 */
	function wc_csv_modify_headers( $column_headers ) {
		$new_headers = array(
			'signup_source'  => 'Signup Source',
			'source_details' => 'Source Details',
			//'referral_details' => 'Referral Details',
		);

		return array_merge( $column_headers, $new_headers );
	}

	/**
	 * @param $order_data
	 * @param $order
	 * @param $csv_generator
	 *
	 * @return array
	 */
	function wc_csv_modify_row_data( $order_data, $order, $csv_generator ) {

		$custom_data = array(
			'signup_source'  => get_post_meta( $order->id, 'uo-signup-source', true ),
			'source_details' => get_post_meta( $order->id, 'uo-signup-source-details', true ),
			//'referral_details' => get_post_meta( $order->id, 'uo-referral-details', true ),
		);

		$new_order_data   = array();
		$one_row_per_item = false;

		if ( version_compare( wc_customer_order_csv_export()->get_version(), '4.0.0', '<' ) ) {
			// pre 4.0 compatibility
			$one_row_per_item = ( 'default_one_row_per_item' === $csv_generator->order_format || 'legacy_one_row_per_item' === $csv_generator->order_format );
		} elseif ( isset( $csv_generator->format_definition ) ) {
			// post 4.0 (requires 4.0.3+)
			$one_row_per_item = 'item' === $csv_generator->format_definition['row_type'];
		}
		if ( $one_row_per_item ) {
			foreach ( $order_data as $data ) {
				$new_order_data[] = array_merge( (array) $data, $custom_data );
			}
		} else {
			$new_order_data = array_merge( $order_data, $custom_data );
		}

		return $new_order_data;
	}

	/**
	 * @param $fields
	 *
	 * @return mixed
	 */
	function remove_order_notes( $fields ) {
		unset( $fields['order']['order_comments'] );

		return $fields;
	}

	/**
	 * @param $menu_links
	 *
	 * @return mixed
	 */
	function email_pref_links( $menu_links ) {
		if ( is_user_logged_in() ) {
			array_pop( $menu_links );
			$menu_links['user-email-preferences'] = 'Email preferences';
			$menu_links['customer-logout']        = __( 'Logout', 'woocommerce' );
		}

		$array_chunks              = array_chunk( $menu_links, 1, true );
		$array_chunks[1]['events'] = __( 'Registrations', 'woocommerce' );
		$menu_links                = [];
		if ( $array_chunks ) {
			foreach ( $array_chunks as $chunk ) {
				$menu_links = array_merge( $menu_links, $chunk );
			}
		}


		$menu_links = array_merge( [ 'learner-dashboard' => 'Learner Dashboard' ], $menu_links );

		return $menu_links;
	}

	/**
	 * @param $url
	 * @param $endpoint
	 * @param $value
	 * @param $permalink
	 *
	 * @return false|string
	 */
	function email_pref_links_urls( $url, $endpoint, $value, $permalink ) {

		if ( 'user-email-preferences' === $endpoint ) {
			$url = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'user-email-preferences';
		}
		if ( 'events' === $endpoint ) {
			$url = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'show-events';
		}
		if ( 'learner-dashboard' === $endpoint ) {
			$url = site_url( '/learner-dashboard' );
		}

		return $url;
	}

	/**
	 * @param $order_id
	 */
	public function add_user_to_active_campaign( $order_id ) {
		$url     = self::$url;
		$api_key = self::$key;
		$order   = wc_get_order( $order_id );
		$user    = $order->get_user();
		$ip      = getenv( 'HTTP_CLIENT_IP' ) ?:
			getenv( 'HTTP_X_FORWARDED_FOR' ) ?:
				getenv( 'HTTP_X_FORWARDED' ) ?:
					getenv( 'HTTP_FORWARDED_FOR' ) ?:
						getenv( 'HTTP_FORWARDED' ) ?:
							getenv( 'REMOTE_ADDR' );

		if ( isset( $_POST['marketing-related-emails'] ) ) {
			$tags = 'Send Product Emails';
		} else {
			$tags = '';
		}

		if ( $user ) {
			$params = array(
				'api_key'    => $api_key,
				'api_action' => 'contact_add',
				'api_output' => 'serialize',
			);

			$post   = array(
				'email'                => $user->user_email,
				'first_name'           => $user->first_name,
				'last_name'            => $user->last_name,
				'phone'                => $order->get_billing_phone(),
				'orgname'              => $order->get_billing_company(),
				'tags'                 => $tags,
				'ip4'                  => $ip,
				'p[3]'                 => 3, //Add to Newsletter List Only
				'status[3]'            => 1,
				'instantresponders[3]' => 1,
			);
			$result = WoocommerceMods::run_curl_for_active_campaign( $params, $url, $post );
			if ( $result ) {
				update_user_meta( $user->ID, 'activecampaign_contact_id', $result['subscriber_id'] );
			}
		}
	}

	/**
	 * @param $params
	 * @param $url
	 * @param $post
	 *
	 * @return mixed
	 */
	public static function run_curl_for_active_campaign( $params, $url, $post ) {
		// This section takes the input fields and converts them to the proper format
		$query = '';
		foreach ( $params as $key => $value ) {
			$query .= urlencode( $key ) . '=' . urlencode( $value ) . '&';
		}
		$query = rtrim( $query, '& ' );

		// This section takes the input data and converts it to the proper format
		if ( ! empty( $post ) ) {
			$data = '';
			foreach ( $post as $key => $value ) {
				$data .= urlencode( $key ) . '=' . urlencode( $value ) . '&';
			}
			$data = rtrim( $data, '& ' );
		}
		// clean up the url
		$url = rtrim( $url, '/ ' );

		if ( ! function_exists( 'curl_init' ) ) {
			die( 'CURL not supported. (introduced in PHP 4.0.2)' );
		}

		// define a final API request - GET
		$api     = $url . '/admin/api.php?' . $query;
		$request = curl_init( $api ); // initiate curl object
		curl_setopt( $request, CURLOPT_HEADER, 0 ); // set to 0 to eliminate header info from response
		curl_setopt( $request, CURLOPT_RETURNTRANSFER, 1 ); // Returns response data instead of TRUE(1)
		if ( ! empty( $post ) ) {
			curl_setopt( $request, CURLOPT_POSTFIELDS, $data ); // use HTTP POST to send form data
		}
		//curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
		curl_setopt( $request, CURLOPT_FOLLOWLOCATION, true );

		$response = (string) curl_exec( $request ); // execute curl post and store results in $response
		curl_close( $request ); // close curl object

		if ( ! $response ) {
			//die( 'Nothing was returned. Do you have a connection to Email Marketing server?' );
			Boot::log( $response, '!$response', 'api' );
		}

		$result = unserialize( $response );

		return $result;
	}

	/**
	 * @param $subscriber_id
	 *
	 * @return mixed
	 */
	public static function get_subscriber_details( $subscriber_id ) {
		$url = self::$url;


		$params = array(
			'api_key'    => self::$key,
			'api_action' => 'contact_view',
			'api_output' => 'serialize',
			'id'         => $subscriber_id,
		);

		$result = WoocommerceMods::run_curl_for_active_campaign( $params, $url, null );

		return $result;
	}

	/**
	 * @param $subscriber_id
	 * @param $status
	 *
	 * @return mixed
	 */
	public function active_campaign_subscription_update( $subscriber_id, $status ) {
		$url     = self::$url;
		$api_key = self::$key;
		if ( 0 === (int) $status ) {
			$api_action = 'contact_tag_add';
		} /*else {
			$api_action = 'contact_tag_remove';
		}*/
		$tags   = 'Unsubscribe';
		$params = array(
			'api_key'    => $api_key,
			'api_action' => $api_action,
			'api_output' => 'serialize',
		);

		$post   = array(
			'id'   => $subscriber_id,
			'tags' => $tags,
		);
		$result = WoocommerceMods::run_curl_for_active_campaign( $params, $url, $post );

		return $result;
	}


	/**
	 * @param $subscriber_id
	 * @param $status
	 *
	 * @return mixed
	 */
	public function active_campaign_subscription_subscribe( $subscriber_id, $status ) {
		$url        = self::$url;
		$api_key    = self::$key;
		$user       = wp_get_current_user();
		$user_email = $user->user_email;

		$params = array(
			'api_key'    => $api_key,
			'api_action' => 'contact_edit',
			'api_output' => 'serialize',
			'overwrite'  => 0,
		);
		if ( 1 === (int) $status ) {
			$post = array(
				'id'        => $subscriber_id,
				'email'     => $user_email,
				'p[3]'      => 3, //Add to Newsletter List Only
				'status[3]' => 1,
			);
		}
		$result = WoocommerceMods::run_curl_for_active_campaign( $params, $url, $post );

		$tags   = 'Unsubscribe';
		$params = array(
			'api_key'    => $api_key,
			'api_action' => 'contact_tag_remove',
			'api_output' => 'serialize',
		);

		$post   = array(
			'id'   => $subscriber_id,
			'tags' => $tags,
		);
		$result = WoocommerceMods::run_curl_for_active_campaign( $params, $url, $post );

		return $result;
	}


	/**
	 * @param $subscriber_id
	 * @param $status
	 *
	 * @return mixed
	 */
	public function active_campaign_tag_update( $subscriber_id, $status ) {
		$url     = self::$url;
		$api_key = self::$key;
		if ( 1 === (int) $status ) {
			$api_action = 'contact_tag_add';
		} else {
			$api_action = 'contact_tag_remove';
		}
		$tags   = 'Send Product Emails';
		$params = array(
			'api_key'    => $api_key,
			'api_action' => $api_action,
			'api_output' => 'serialize',
		);

		$post   = array(
			'id'   => $subscriber_id,
			'tags' => $tags,
		);
		$result = WoocommerceMods::run_curl_for_active_campaign( $params, $url, $post );

		return $result;
	}

	/**
	 *
	 */
	public function save_email_preferences() {
		if ( isset( $_POST['save_email_preferences'] ) && wp_verify_nonce( $_POST['save_email_preferences'], 'save_email_preferences' ) ) {
			$user_id       = wp_get_current_user()->ID;
			$subscriber_id = get_user_meta( $user_id, 'activecampaign_contact_id', true );

			if ( isset( $_POST['uo-sign-up-at-newsletter'] ) ) {
				$this->active_campaign_subscription_subscribe( $subscriber_id, 1 );
			} else {
				$this->active_campaign_subscription_update( $subscriber_id, 0 );
			}

			if ( isset( $_POST['marketing-related-emails'] ) ) {
				$this->active_campaign_tag_update( $subscriber_id, 1 );
			} else {
				$this->active_campaign_tag_update( $subscriber_id, 0 );
			}

			wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'user-email-preferences?' . wp_create_nonce( time() ) );
			exit;
		}
	}

}