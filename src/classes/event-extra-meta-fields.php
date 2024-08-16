<?php

namespace uncanny_advance_trainings;

use uncanny_ceu\Utilities;


/**
 * Class EventExtraMetaFields
 * @package uncanny_advance_trainings
 */
class EventExtraMetaFields {

	/**
	 * EventExtraMetaFields constructor.
	 */
	function __construct() {
		add_action( 'save_post', array( __CLASS__, 'extra_fields_save' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'extra_fields_add_meta_box' ) );
		add_filter( 'tribe_events_meta_box_timepicker_step', array( __CLASS__, 'modify_default_step' ), 22 );

		add_filter( 'register_post_type_args', array( __CLASS__, 'modify_tribe_organizers_post_type_args' ), 20, 2 );
		add_filter( 'tribe_organizer_label_singular', array(
			__CLASS__,
			'modify_tribe_organizer_label_singular'
		), 20, 2 );
		add_filter( 'tribe_organizer_label_plural', array(
			__CLASS__,
			'modify_tribe_organizer_label_plural'
		), 20, 2 );
		add_filter( 'gettext', array( __CLASS__, 'change_website_text' ), 20, 3 );
	}

	public static function change_website_text( $translated_text, $untranslated_text, $domain ) {
		if ( 'the-events-calendar' === $domain && '%s Website' === $translated_text ) {
			$translated_text = 'External %s Website';
		}

		return $translated_text;
	}

	public static function modify_tribe_organizer_label_singular() {
		return 'Sponsor';
	}

	public static function modify_tribe_organizer_label_plural() {
		return 'Sponsors';
	}

	/**
	 * @param $args
	 * @param $post_type
	 *
	 * @return mixed
	 */
	public static function modify_tribe_organizers_post_type_args( $args, $post_type ) {
		if ( 'tribe_organizer' === $post_type ) {
			$args['labels']['name']                    = __( 'Sponsors', 'uncanny-owl' );
			$args['labels']['singular_name']           = __( 'Sponsor', 'uncanny-owl' );
			$args['labels']['singular_name_lowercase'] = __( 'sponsor', 'uncanny-owl' );
			$args['labels']['plural_name_lowercase']   = __( 'sponsors', 'uncanny-owl' );
			$args['labels']['add_new_item']            = __( 'Add New Sponsor', 'uncanny-owl' );
			$args['labels']['edit_item']               = __( 'Edit Sponsor', 'uncanny-owl' );
			$args['labels']['new_item']                = __( 'New Sponsor', 'uncanny-owl' );
			$args['labels']['view_item']               = __( 'View Sponsor', 'uncanny-owl' );
			$args['labels']['search_items']            = __( 'Search Sponsors', 'uncanny-owl' );
			$args['labels']['not_found']               = __( 'No sponsors found', 'uncanny-owl' );
			$args['labels']['not_found_in_trash']      = __( 'No sponsors found in trash', 'uncanny-owl' );
		}

		return $args;
	}

	public static function modify_default_step( $step ) {
		return 1;
	}

	function atom_search_where( $where ) {
		global $wpdb;
		if ( is_admin() ) {
			$where .= " OR (t.name LIKE '%" . get_search_query() . "%')";
		}

		return $where;
	}

	function atom_search_join( $join ) {
		global $wpdb;
		if ( is_admin() ) {
			$join .= "LEFT JOIN {$wpdb->term_relationships} tr ON {$wpdb->posts}.ID = tr.object_id INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id";
		}

		Boot::log( $join, '$join', 'wp' );

		return $join;
	}

	function atom_search_groupby( $groupby ) {
		global $wpdb;

		// we need to group on post ID
		$groupby_id = "{$wpdb->posts}.ID";
		if ( ! is_search() || strpos( $groupby, $groupby_id ) !== false ) {
			return $groupby;
		}

		// groupby was empty, use ours
		if ( ! strlen( trim( $groupby ) ) ) {
			return $groupby_id;
		}

		// wasn't empty, append ours
		return $groupby . ", " . $groupby_id;
	}


	/**
	 * @param $value
	 *
	 * @return bool|mixed|string
	 */
	public static function extra_fields_get_meta( $value ) {
		global $post;

		$field = get_post_meta( $post->ID, $value, true );
		if ( ! empty( $field ) ) {
			return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
		} else {
			return false;
		}
	}

	/**
	 *
	 */
	public static function extra_fields_add_meta_box() {
		add_meta_box(
			'extra_fields-extra-fields',
			__( 'Extra Fields', 'uncanny-owl' ),
			array( __CLASS__, 'extra_fields_html' ),
			'tribe_events',
			'normal',
			'default'
		);
	}

	/**
	 * @param $post
	 */
	public static function extra_fields_html( $post ) {
		wp_nonce_field( '_extra_fields_nonce', 'extra_fields_nonce' ); ?>
		<p>
			<label for="instructor"><?php _e( 'Instructor', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="instructor" id="instructor" value="<?php echo self::extra_fields_get_meta( 'instructor' ); ?>">
		</p>
		<p>
			<label for="subtitle"><?php _e( 'Subtitle', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="subtitle" id="subtitle" value="<?php echo self::extra_fields_get_meta( 'subtitle' ); ?>">
		</p>
		<p>
			<label for="subtitle2"><?php _e( 'Subtitle 2', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="subtitle2" id="subtitle2" value="<?php echo self::extra_fields_get_meta( 'subtitle2' ); ?>">
		</p>
		<p>
			<label for="ncb_ceu"><?php _e( 'NCB CEU', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="ncb_ceu" id="ncb_ceu" value="<?php echo self::extra_fields_get_meta( 'ncb_ceu' ); ?>">
		</p>
		<p>
			<label for="sponsors"><?php _e( 'Sponsors', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="sponsors" id="sponsors" value="<?php echo self::extra_fields_get_meta( 'sponsors' ); ?>">
		</p>
		<p>
			<label for="date_deadline"><?php _e( 'Date Deadline', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="date_deadline" id="date_deadline" value="<?php echo self::extra_fields_get_meta( 'date_deadline' ); ?>">
			<i>Format: YYYY-MM-DD HH:MM:SS</i>
		</p>
		<p>
			<label for="assistants"><?php _e( 'Assistants', 'uncanny-owl' ); ?></label><br>
			<textarea rows="4" class="widefat" name="assistants" id="assistants"><?php echo self::extra_fields_get_meta( 'assistants' ); ?></textarea>
		</p>
		<p>
			<label for="contract_status"><?php _e( 'Contract Status', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="contract_status" id="contract_status" value="<?php echo self::extra_fields_get_meta( 'contract_status' ); ?>">
		</p>
		<p>
			<input type="checkbox" name="eblast_required" id="eblast_required" value="1" <?php echo ( absint( self::extra_fields_get_meta( 'eblast_required' ) ) === 1 ) ? 'checked' : ''; ?>>
			<label for="eblast_required"><?php _e( 'Eblast Required', 'uncanny-owl' ); ?></label>
		</p>
		<p>
			<label for="notes"><?php _e( 'Notes', 'uncanny-owl' ); ?></label><br>
			<textarea rows="4" class="widefat" name="notes" id="notes"><?php echo self::extra_fields_get_meta( 'notes' ); ?></textarea>
		</p>
		<p>
			<label for="flier_link"><?php _e( 'Flier Link', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="flier_link" id="flier_link" value="<?php echo self::extra_fields_get_meta( 'flier_link' ); ?>">
		</p>
		<p>
			<label for="fee_early_last_differential"><?php _e( 'Fee Late Early Differential', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="fee_early_last_differential" id="fee_early_last_differential" value="<?php echo self::extra_fields_get_meta( 'fee_early_last_differential' ); ?>">
		</p>
		<p>
			<label for="fee_normal_registration"><?php _e( 'Fee Normal Registration', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="fee_normal_registration" id="fee_normal_registration" value="<?php echo self::extra_fields_get_meta( 'fee_normal_registration' ); ?>">
		</p>
		<p>
			<label for="description"><?php _e( 'Previews', 'uncanny-owl' ); ?></label><br>
			<textarea class="widefat" rows="4" name="description" id="description"><?php echo self::extra_fields_get_meta( 'description' ); ?></textarea>
		</p>
		<p>
			<label for="location_code"><?php _e( 'Location Code', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="location_code" id="location_code" value="<?php echo self::extra_fields_get_meta( 'location_code' ); ?>">
		</p>
		<p>
			<label for="emailing_family"><?php _e( 'Emailing Family', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="emailing_family" id="emailing_family" value="<?php echo self::extra_fields_get_meta( 'emailing_family' ); ?>">
		</p>
		<!--<p>
			<label for="pk_class_id"><?php /*_e( 'PK Class ID', 'uncanny-owl' ); */?></label><br>
			<input type="text" class="widefat" name="pk_Class_ID" id="pk_class_id" value="<?php /*echo self::extra_fields_get_meta( 'pk_Class_ID' ); */?>">
		</p>-->
		<p>
			<label for="qr_code_url"><?php _e( 'QR Code URL', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="qr_code_url" id="qr_code_url" value="<?php echo self::extra_fields_get_meta( 'qr_code_url' ); ?>">
		</p>
		<p>
			<label for="ready_to_advertise"><?php _e( 'Ready To Advertise', 'uncanny-owl' ); ?></label><br>
			<textarea class="widefat" name="ready_to_advertise" id="ready_to_advertise"><?php echo self::extra_fields_get_meta( 'ready_to_advertise' ); ?></textarea>

		</p>
		<p>
			<label for="rent_on_facility"><?php _e( 'Rent On Facility', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="rent_on_facility" id="rent_on_facility" value="<?php echo self::extra_fields_get_meta( 'rent_on_facility' ); ?>">
		</p>
		<p>
			<label for="short_name"><?php _e( 'Short Name', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="short_name" id="short_name" value="<?php echo self::extra_fields_get_meta( 'short_name' ); ?>">
		</p>
		<p>
			<label for="web_listed"><?php _e( 'Web Listed', 'uncanny-owl' ); ?></label><br>
			<input type="text" class="widefat" name="web_listed" id="web_listed" value="<?php echo self::extra_fields_get_meta( 'web_listed' ); ?>">
		</p>

		<!---------------------------------------->
		<hr/>
		<?php
	}

	/**
	 * @param $post_id
	 */
	public static function extra_fields_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;

		}

		if ( ! isset( $_POST['extra_fields_nonce'] ) || ! wp_verify_nonce( $_POST['extra_fields_nonce'], '_extra_fields_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['assistants'] ) ) {
			update_post_meta( $post_id, 'assistants', esc_attr( $_POST['assistants'] ) );
		}
		if ( isset( $_POST['contract_status'] ) ) {
			update_post_meta( $post_id, 'contract_status', esc_attr( $_POST['contract_status'] ) );
		}
		if ( isset( $_POST['date_deadline'] ) ) {
			update_post_meta( $post_id, 'date_deadline', esc_attr( $_POST['date_deadline'] ) );
		}
		if ( isset( $_POST['description'] ) ) {
			update_post_meta( $post_id, 'description', esc_attr( $_POST['description'] ) );
		}
		if ( isset( $_POST['eblast_required'] ) ) {
			update_post_meta( $post_id, 'eblast_required', absint( esc_attr( $_POST['eblast_required'] ) ) );
		} else {
			update_post_meta( $post_id, 'eblast_required', 0 );
		}
		if ( isset( $_POST['emailing_family'] ) ) {
			update_post_meta( $post_id, 'emailing_family', esc_attr( $_POST['emailing_family'] ) );
		}
		if ( isset( $_POST['fee_early_last_differential'] ) ) {
			update_post_meta( $post_id, 'fee_early_last_differential', esc_attr( $_POST['fee_early_last_differential'] ) );
		}
		if ( isset( $_POST['fee_normal_registration'] ) ) {
			update_post_meta( $post_id, 'fee_normal_registration', esc_attr( $_POST['fee_normal_registration'] ) );
		}
		if ( isset( $_POST['flier_link'] ) ) {
			update_post_meta( $post_id, 'flier_link', esc_attr( $_POST['flier_link'] ) );
		}
		if ( isset( $_POST['location_code'] ) ) {
			update_post_meta( $post_id, 'location_code', esc_attr( $_POST['location_code'] ) );
		}
		if ( isset( $_POST['ncb_ceu'] ) ) {
			update_post_meta( $post_id, 'ncb_ceu', esc_attr( $_POST['ncb_ceu'] ) );
		}
		if ( isset( $_POST['notes'] ) ) {
			update_post_meta( $post_id, 'notes', esc_attr( $_POST['notes'] ) );
		}
		if ( isset( $_POST['pk_class_id'] ) ) {
			update_post_meta( $post_id, 'pk_class_id', esc_attr( $_POST['pk_class_id'] ) );
		}
		if ( isset( $_POST['qr_code_url'] ) ) {
			update_post_meta( $post_id, 'qr_code_url', esc_attr( $_POST['qr_code_url'] ) );
		}
		if ( isset( $_POST['ready_to_advertise'] ) ) {
			update_post_meta( $post_id, 'ready_to_advertise', esc_attr( $_POST['ready_to_advertise'] ) );
		}
		if ( isset( $_POST['rent_on_facility'] ) ) {
			update_post_meta( $post_id, 'rent_on_facility', esc_attr( $_POST['rent_on_facility'] ) );
		}
		if ( isset( $_POST['short_name'] ) ) {
			update_post_meta( $post_id, 'short_name', esc_attr( $_POST['short_name'] ) );
		}
		if ( isset( $_POST['web_listed'] ) ) {
			update_post_meta( $post_id, 'web_listed', esc_attr( $_POST['web_listed'] ) );
		}
		if ( isset( $_POST['sponsors'] ) ) {
			update_post_meta( $post_id, 'sponsors', esc_attr( $_POST['sponsors'] ) );
		}
		if ( isset( $_POST['subtitle'] ) ) {
			update_post_meta( $post_id, 'subtitle', esc_attr( $_POST['subtitle'] ) );
		}
		if ( isset( $_POST['subtitle2'] ) ) {
			update_post_meta( $post_id, 'subtitle2', esc_attr( $_POST['subtitle2'] ) );
		}
		if ( isset( $_POST['instructor'] ) ) {
			update_post_meta( $post_id, 'instructor', esc_attr( $_POST['instructor'] ) );
		}
	}

}