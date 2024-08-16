<?php

namespace uncanny_advance_trainings;

use uncanny_ceu\Utilities;

/**
 * Class SabaiDirectoryMods
 * @package uncanny_advance_trainings
 */
class SabaiDirectoryMods {
	public static $exclude_pages;

	/**
	 * SabaiDirectoryMods constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'wp_footer', array( $this, 'enable_check_manually' ) );
		add_action( 'wp_head', array( $this, 'custom_css' ) );
		// add_action( 'wp', array( $this, 'maybe_redirect_user' ), 20 );
		add_action( 'woocommerce_after_order_notes', array( $this, 'add_me_to_directory_option' ), 1001 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_custom_field' ) );
		add_action( 'init', array( $this, 'skip_addition_to_directory' ), 18 );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'purchase_notice_on_checkout' ), 12 );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'practitioner_links' ) );
		add_filter( 'woocommerce_get_endpoint_url', array( $this, 'endpoint_urls' ), 20, 4 );
	}

	function purchase_notice_on_checkout() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			echo '<div class="woocommerce-info" style="background:#df4a32 !important;">' . sprintf( __( 'Right now you are checking out as ( ' .$current_user->user_email. ' ). If you want a different person’s name on any certificates, or want to access your materials later with a different login, <a href="%s"><strong>sign out</strong></a> now and complete your purchase with the other person’s login information instead.', 'uncanny-owl' ), wp_logout_url() ) . '</div>';
			// echo '<div class="woocommerce-info" style="background:#df4a32 !important;">' . sprintf( __( 'Right now you are buying this product for yourself. If this purchase is for someone else, <a href="%s"><strong>Click here</strong></a> to sign out and complete the purchase with the other person\'s information instead.', 'uncanny-owl' ), wp_logout_url() ) . '</div>';
		}
	}


	/**
	 *
	 */
	function skip_addition_to_directory() {
		if ( isset( $_GET['skip-directory-entry'] ) && is_user_logged_in() ) {
			$user_id = wp_get_current_user()->ID;
			update_user_meta( $user_id, 'uo-add-to-practitioner-directory', 0 );
		}
	}

	/**
	 *
	 */
	function plugins_loaded() {
		add_shortcode( 'user_course_history', array( $this, 'user_course_history' ) );
	}

	/**
	 * @param $atts
	 *
	 * @return string
	 */
	public function user_course_history( $atts ) {
		$atts = shortcode_atts( array(
			'user_email' => '',
		), $atts );

		if ( ! empty( $atts['user_email'] ) && is_email( $atts['user_email'] ) ) {
			$user = get_user_by( 'email', $atts['user_email'] );
			if ( $user ) {
				ob_start();
				$courses      = get_posts( array(
					'post_type'      => 'sfwd-courses',
					'posts_per_page' => 999,
					'post_status'    => 'publish',
				) );
				$course_array = array();
				if ( $courses ) {
					foreach ( $courses as $course ) {
						$course_id           = $course->ID;
						$is_directory_course = get_post_meta( $course_id, '_exclude_in_directory', true );
						if ( empty( $is_directory_course ) || ! $is_directory_course ) {
							if ( learndash_course_completed( $user->ID, $course_id ) ) {
								$short = get_post_meta( $course_id, 'course_abbr', true );
								if ( empty( $short ) ) {
									$short = $course->post_title;
								}
								$course_array[] = $short;
							}
						}
					}
				}
				$sorted = $course_array;
				asort( $sorted );
				//Utilities::log( [ $course_array, $sorted ], 'courses', true, 'array' );
				if ( $sorted ) {
					echo '<h4>Completed Courses</h4>';
					echo join( ', ', $sorted );
				}
				$end = ob_get_clean();
			} else {
				$end = '';
			}
		} else {
			$end = '';
		}

		return $end;
	}

	/**
	 *
	 */
	public function custom_css() {
		if ( is_page( 41799 ) || is_page( 34795 ) ) {
			?>
            <style>
                <?php if ( is_page( 34795 ) ){ ?>.hide_on_edit, <?php } ?> .sabai-googlemaps-map .sabai-googlemaps-map-map,
                .sabai-googlemaps-map .sabai-googlemaps-map-lng-container,
                .sabai-googlemaps-map .sabai-googlemaps-map-lat-container {
                    display: none !important;
                }

                /* Loading Overlay */

                .uo-loading-overlay {
                    position: relative;
                }

                .uo-loading-overlay:before {
                    content: '';

                    position: absolute;
                    top: 0;
                    left: 0;

                    width: 100%;
                    height: 100%;

                    z-index: 10;

                    background-color: rgba(255, 255, 255, .8);
                    background-image: url('<?php echo get_stylesheet_directory_uri() ?>/img/spinning-line.svg');
                    background-repeat: no-repeat;
                    background-position: center center;
                }
            </style>
			<?php
		}
	}

	/**
	 *
	 */
	public function enable_check_manually() {
		if ( is_page( 41799 ) || is_page( 34795 ) ) {
			?>
            <script>
                jQuery(document).ready(function () {
                    jQuery('.sabai-googlemaps-map-manual').trigger('click')
                    jQuery('.sabai-form-field.sabai-form-type-email input').attr('readonly', 'readonly')
                    jQuery('.sabai-googlemaps-address-street, .sabai-googlemaps-map-manual').parent().hide()
                    jQuery('.sabai-btn.sabai-btn-info.sabai-btn-xs.sabai-googlemaps-find-on-map').parent().hide()
                    //jQuery('input[name="directory_contact[0][fax]"]').hide().parent().hide();
                    jQuery('textarea[name="field_credentials[0]"]').attr('maxlength', 100)
                    jQuery('textarea[name="field_practice_notess[0]"]').attr('maxlength', 250)
					<?php if ( is_page( 41799 ) ){ ?>
                    jQuery('.sabai-form-type-submit').append(' &nbsp;<a href="<?php echo home_url(); ?>?skip-directory-entry=yes&_wpnonce=<?php echo wp_create_nonce( time() ) ?>" class="sabai-content-btn-edit-directory-listing sabai-btn sabai-btn-primary">Skip</a>')
					<?php } ?>
                    //jQuery('.sabai-googlemaps-address-city, .sabai-googlemaps-address-zip').parent().removeClass('sabai-col-sm-6').addClass('sabai-col-sm-12');
                })

                jQuery(function ($) {
                    $(document).ready(function () {
                        Locate()
                        $('.sabai-googlemaps-formatted-address').attr('placeholder', 'Enter your address or zip/postal code (for location finding only; this text will not appear in your listing)')
                    })

                    var Locate = function () {
                        var $trigger = $('.sabai-googlemaps-find-on-map'),
                            $input = $('.sabai-googlemaps-formatted-address'),
                            $display = $('.sabai-googlemaps-map'),
                            $search = $('.sabai-entity-field-type-googlemaps-marker > .sabai-form-fields > .sabai-form-field span')

                        $search.click(function () {
                            Loading_Overlay.create($display, 5000)
                        })

                        var timer = null
                        $input.keydown(function () {
                            var $this = $(this)

                            clearTimeout(timer)
                            timer = setTimeout(function () {
                                if ($this.val().length > 3) {
                                    $trigger.click()
                                    Loading_Overlay.create($display, 5000)
                                }
                            }, 1500)

                        })
                    }

                    var Loading_Overlay = {
                        container: null,
                        css_class: 'uo-loading-overlay',

                        create: function ($element, timeout) {
                            var has_timeout = timeout === undefined ? false : $.isNumeric(timeout) && Math.floor(timeout) == timeout,
                                this_overlay = this

                            this.container = $element

                            if (has_timeout) {
                                this.show()

                                setTimeout(function () {
                                    this_overlay.hide()
                                }, timeout)
                            }

                            return this
                        },

                        show: function () {
                            this.container.addClass(this.css_class)
                        },

                        hide: function () {
                            this.container.removeClass(this.css_class)
                        }
                    }
                })
            </script>
			<?php
		}
	}

	/**
	 *
	 */
	public function maybe_redirect_user() {
		global $wpdb;
		if ( ! is_admin() && ! is_woocommerce() && ! is_cart() && ! is_checkout() && is_user_logged_in() && ! wp_doing_ajax() && ! is_page( 41799 ) ) {
			$user       = wp_get_current_user();
			$user_email = $user->user_email;
			if ( ! in_array( 'administrator', $user->roles ) ) {
				$add_to_directory = get_user_meta( $user->ID, 'uo-add-to-practitioner-directory', true );
				if ( 1 === (int) $add_to_directory ) {
					$results = $wpdb->get_var( $wpdb->prepare( "SELECT entity_id FROM {$wpdb->prefix}sabai_entity_field_directory_contact WHERE email LIKE %s", $user_email ) );
					if ( ! $results ) {
						wp_safe_redirect( get_permalink( 41799 ) . '?error=fill' );
						exit();
					}
				}
			}
			//}
		}
	}

	/**
	 * @param $checkout
	 */
	public function add_me_to_directory_option( $checkout ) {
		global $wpdb;
		if ( is_user_logged_in() ) {
			$user    = wp_get_current_user();
			$results = $wpdb->get_var( $wpdb->prepare( "SELECT entity_id FROM {$wpdb->prefix}sabai_entity_field_directory_contact WHERE email LIKE %s", $user->user_email ) );
			if ( ! $results ) {
				$already_exists = false;
			} else {
				$already_exists = true;
			}
		} else {
			$already_exists = false;
		}
		$has_an_event_ticket = $this->checkout_has_an_event_ticket();

		if ( false === $already_exists && true === $has_an_event_ticket ) {
			$classes = array( 'form-row-wide' );
			woocommerce_form_field( 'uo-add-to-practitioner-directory', array(
				'type'         => 'checkbox',
				'class'        => $classes,
				'label'        => esc_html__( 'Add Me To The Practitioner Directory', 'uncanny-owl' ),
				'default'      => 'checked',
				'required'     => false,
				'autocomplete' => false,
			), true );
		}
	}

	/**
	 * @return bool
	 */
	public function checkout_has_an_event_ticket() {
		$exists = false;
		$cart   = WC()->cart->cart_contents;
		if ( $cart ) {
			foreach ( $cart as $key => $content ) {
				$product_id = $content['product_id'];
				$wootickcet = get_post_meta( $product_id, '_tribe_wooticket_for_event', true );
				if ( ! empty( $wootickcet ) && is_numeric( $wootickcet ) ) {
					$exists = true;
					break;
				}
			}
		}

		return $exists;
	}

	/**
	 * @param $order_id
	 */
	public function save_custom_field( $order_id ) {
		if ( isset( $_POST['uo-add-to-practitioner-directory'] ) && $order_id ) {
			$order   = new \WC_Order( $order_id );
			$user_id = $order->get_user_id();
			update_user_meta( $user_id, 'uo-add-to-practitioner-directory', sanitize_text_field( $_POST['uo-add-to-practitioner-directory'] ) );
		}
	}

	/**
	 * @param $menu_links
	 *
	 * @return mixed
	 */
	function practitioner_links( $menu_links ) {
		if ( is_user_logged_in() ) {
			array_pop( $menu_links );
			global $wpdb;
			$user_email = wp_get_current_user()->user_email;
			$results    = $wpdb->get_var( $wpdb->prepare( "SELECT entity_id FROM {$wpdb->prefix}sabai_entity_field_directory_contact WHERE email LIKE %s", $user_email ) );
			if ( ! $results ) {
				$menu_links['add-to-directory'] = 'Add yourself to Practitioner Directory';
			} else {
				$menu_links['update-directory'] = 'Edit Practitioner Directory listing';
			}
			$menu_links['add-to-directory'] = 'Add yourself to Practitioner Directory';
			$menu_links['customer-logout'] = __( 'Logout', 'woocommerce' );
			$menu_links['dashboard']       = __( 'Account Settings', 'woocommerce' );
		}

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
	function endpoint_urls( $url, $endpoint, $value, $permalink ) {

		if ( $endpoint === 'add-to-directory' ) {
			$url = get_permalink( 41799 );
		}

		global $wpdb;
		$user_email = wp_get_current_user()->user_email;
		$entity_id  = $wpdb->get_var( $wpdb->prepare( "SELECT entity_id FROM {$wpdb->prefix}sabai_entity_field_directory_contact WHERE email LIKE %s ORDER BY entity_id DESC LIMIT 0,1", $user_email ) );
		$post_slug  = $wpdb->get_var( $wpdb->prepare( "SELECT post_slug FROM {$wpdb->prefix}sabai_content_post WHERE post_id = %d", $entity_id ) );

		if ( $endpoint === 'update-directory' ) {
			$url = site_url( '/practitioners/listing/' . $post_slug . '/edit' );
		}

		return $url;

	}
}