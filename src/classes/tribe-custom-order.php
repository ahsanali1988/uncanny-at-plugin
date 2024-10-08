<?php

namespace uncanny_advance_trainings;


/**
 * Class TribeCustomOrder
 * @package uncanny_advance_trainings
 */
class TribeCustomOrder {
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'custom_order_add_meta_box' ) );
		add_action( 'save_post', array( $this, 'custom_order_save' ) );
		add_action( 'save_post', array( $this, 'stop_waitlist_save' ) );
		add_filter( 'tribe_related_posts_args', array( $this, 'tribe_related_posts_args' ), 99 );

		add_action( 'event_tickets_woocommerce_tickets_generated_for_product', array(
			$this,
			'checkout_order_processed',
		), 99, 3 );

		add_action( 'woocommerce_admin_order_data_after_billing_address', array(
			$this,
			'my_custom_checkout_field_display_admin_order_meta',
		), 10, 1 );
		add_action( 'manage_shop_order_posts_custom_column', array(
			$this,
			'custom_orders_list_column_content',
		), 20, 2 );
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'custom_shop_order_column' ), 20 );

		add_filter( 'tribe_tickets_attendee_table_columns', array(
			$this,
			'custom_tribe_events_list_column_content',
		), 20, 2 );
		add_filter( 'tribe_events_tickets_attendees_table_column', array(
			$this,
			'custom_tribe_event_list_column_content',
		), 20, 3 );

	}

	/**
	 * @param $columns
	 *
	 * @return array
	 */
	function custom_shop_order_column( $columns ) {
		$reordered_columns = array();

		// Inserting columns to a specific location
		foreach ( $columns as $key => $column ) {
			$reordered_columns[ $key ] = $column;
			if ( $key == 'order_status' ) {
				// Inserting after "Status" column
				$reordered_columns['waitlisted'] = __( '<center>Event Waitlisted</center>', 'uncanny-owl' );
			}
		}

		return $reordered_columns;
	}

	/**
	 * @param $columns
	 *
	 * @return array
	 */
	function custom_tribe_events_list_column_content( $columns ) {
		$reordered_columns = array();

		// Inserting columns to a specific location
		foreach ( $columns as $key => $column ) {
			$reordered_columns[ $key ] = $column;
			if ( 'primary_info' == $key ) {
				// Inserting after "Status" column
				$reordered_columns['waitlisted'] = esc_html_x( 'Waitlisted', 'attendee table', 'event-tickets' );
			}
		}

		return $reordered_columns;
	}

	/**
	 * @param $value
	 * @param $item
	 * @param $column
	 *
	 * @return string
	 */
	function custom_tribe_event_list_column_content( $value, $item, $column ) {
		if ( ! empty( $column ) ) {
			switch ( $column ) {
				case 'waitlisted' :
					/*echo '<pre>';
					print_r( $item );
					echo '</pre>';*/
					$order_id      = $item['order_id'];
					$event_id      = $item['event_id'];
					$product_id    = $item['product_id'];
					$key           = "_is_waitlisted_{$product_id}_{$event_id}";
					$is_waitlisted = get_post_meta( $order_id, $key, true );
					if ( $is_waitlisted ) {
						return 'Yes';
					} else {
						return '&mdash;';
					}
					break;
			}
		}

		return $value;
	}

	/**
	 * @param $column
	 * @param $post_id
	 */
	function custom_orders_list_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'waitlisted' :
				// Get custom post meta data
				$is_waitlisted = get_post_meta( $post_id, '_is_waitlisted', true );
				if ( $is_waitlisted ) {
					echo '<center>Yes</center>';
				}

				break;
		}
	}

	/**
	 * @param $order
	 */
	function my_custom_checkout_field_display_admin_order_meta( $order ) {
		$is_waitlisted = get_post_meta( $order->get_id(), '_is_waitlisted', true );
		if ( $is_waitlisted ) {
			echo '<p><strong>' . __( 'Waitlist' ) . ':</strong> Yes</p>';
		}

	}

	/**
	 * @param $product_id
	 * @param $order_id
	 * @param $quantity
	 */
	function checkout_order_processed( $product_id, $order_id, $quantity ) {
		$product           = wc_get_product( $product_id );
		$stock             = $product->get_stock_quantity();
		$manage_stock      = $product->managing_stock();
		$user_id           = wc_get_order( $order_id )->get_user_id();
		$event_id          = get_post_meta( $product_id, '_tribe_wooticket_for_event', true );
		$waitlisting_ended = get_post_meta( $product_id, 'waitlisting-ended', true );
		if ( 'yes' === $waitlisting_ended ) {
			return;
		}
		$stock_adjustment = 100;
		if ( $manage_stock ) {
			$qty_available = $stock - $stock_adjustment;
			if ( $qty_available <= 0 ) {
				update_post_meta( $order_id, '_is_waitlisted', 1 );
				update_post_meta( $order_id, '_is_waitlisted_' . $product_id . '_' . $event_id, 1 );
				update_user_meta( $user_id, '_is_waitlisted_' . $order_id . '_' . $event_id, 1 );
			}
		}
	}

	/**
	 * Generated by the WordPress Meta Box generator
	 * at http://jeremyhixon.com/tool/wordpress-meta-box-generator/
	 *
	 * @param $value
	 *
	 * @return bool|mixed|string
	 */

	function custom_order_get_meta( $value ) {
		global $post;

		$field = get_post_meta( $post->ID, $value, true );
		if ( ! empty( $field ) ) {
			return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
		} else {
			return false;
		}
	}

	function custom_order_add_meta_box() {
		add_meta_box(
			'custom_order-custom-order',
			__( 'Custom Order', 'custom_order' ),
			array( $this, 'custom_order_html' ),
			'tribe_events',
			'side',
			'high'
		);
		add_meta_box(
			'advanced-trainings-waitlist',
			__( 'Waitlist', 'custom_order' ),
			array( $this, 'waitlist_html' ),
			'tribe_events',
			'side',
			'high'
		);
	}

	/**
	 * @param $post
	 */
	function custom_order_html( $post ) {
		wp_nonce_field( '_custom_order_nonce', 'custom_order_nonce' ); ?>

        <p>Re-order Events</p>

        <p>
        <label for="events_custom_order"><?php _e( 'Order', 'custom_order' ); ?></label><br>
        <input type="text" name="events_custom_order" id="events_custom_order"
               value="<?php echo $this->custom_order_get_meta( 'events_custom_order' ); ?>">
        </p><?php
	}

	/**
	 * @param $post
	 */
	function waitlist_html( $post ) {
		wp_nonce_field( '_waitlist_html_nonce', '_waitlist_html_nonce' ); ?>
        <p>
        <input type="checkbox" name="at-stop-waitlist" id="at-stop-waitlist"
               value="yes" <?php echo 'yes' === get_post_meta( $post->ID, 'waitlisting-ended', true ) ? ' checked="checked"' : '' ?>/>
        <label for="at-stop-waitlist">Close Waitlist</label>
        </p><?php
	}

	/**
	 * @param $post_id
	 */
	function custom_order_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['custom_order_nonce'] ) || ! wp_verify_nonce( $_POST['custom_order_nonce'], '_custom_order_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['events_custom_order'] ) ) {
			update_post_meta( $post_id, 'events_custom_order', esc_attr( $_POST['events_custom_order'] ) );
		}
	}

	/**
	 * @param $post_id
	 */
	function stop_waitlist_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['_waitlist_html_nonce'] ) || ! wp_verify_nonce( $_POST['_waitlist_html_nonce'], '_waitlist_html_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['at-stop-waitlist'] ) ) {
			update_post_meta( $post_id, 'waitlisting-ended', 'yes' );
		} else {
			delete_post_meta( $post_id, 'waitlisting-ended' );
		}
	}

	/*
		Usage: custom_order_get_meta( 'events_custom_order' )
	*/

	/**
	 * @param $args
	 *
	 * @return mixed
	 */
	function tribe_related_posts_args( $args ) {
		//Boot::log( $args, '$args', 'events' );
		$args['orderby']  = 'meta_value_num';
		$args['order']    = 'ASC';
		$args['meta_key'] = 'events_custom_order';

		//Boot::log( $args, '$args-after', 'events' );
		return $args;
	}

}