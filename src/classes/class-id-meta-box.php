<?php

namespace uncanny_advance_trainings;


class ClassIDMetaBox {
	private $screens = array(
		'post',
		'page',
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'customize_changeset',
		'oembed_cache',
		'et_pb_layout',
		'project',
		'scheduled-action',
		'product',
		'product_variation',
		'shop_order',
		'shop_order_refund',
		'shop_coupon',
		'shop_webhook',
		'sfwd-courses',
		'sfwd-lessons',
		'sfwd-topic',
		'sfwd-quiz',
		'sfwd-certificates',
		'sfwd-transactions',
		'badges',
		'ticket-meta-fieldset',
		'certification',
		'sfwd-essays',
		'sfwd-assignment',
		'groups',
		'tablepress_table',
		'tribe_rsvp_tickets',
		'tribe_rsvp_attendees',
		'tribe_venue',
		'tribe_organizer',
		'tribe_events',
		'tribe-ea-record',
		'deleted_event',
		'achievement-type',
		'step',
		'submission',
		'nomination',
		'badgeos-log-entry',
		'tribe_wooticket',
		'follow_up_email',
	);
	private $fields = array(
		array(
			'id'    => 'class-id',
			'label' => 'Class ID',
			'type'  => 'text',
		),
	);

	/**
	 * Class construct method. Adds actions to their respective WordPress hooks.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 500 );
		add_action('add_meta_boxes', array($this, 'add_meta_atct_course'));
        add_action('save_post', array($this, 'save_meta_group_courses'), 10, 2);
		//add_filter( 'add_post_metadata', array( $this, 'unique_class_id_meta_value' ), 10, 4 );
		//add_filter( 'update_post_metadata', array( $this, 'unique_class_id_meta_value' ), 10, 4 );
		//add_action( 'save_posts', array( $this, 'unique_class_id_meta_value' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'class_id_admin_notice_handler' ) );
		add_action( 'admin_footer', array( $this, 'add_class_id_tester' ) );
		add_action( 'wp_ajax_verify_post_identifier', array( $this, 'verify_post_identifier_func' ) );
		add_action( 'learndash_course_completed', array( $this, 'check_camt_atc_topic' ) , 102);
	}
/**
     * check if course set meta for auto complete topic of CAMT course
     */
    public function check_camt_atc_topic($course_data) {
		$user_id        = get_current_user_id();
		$user           = new \WP_User( $user_id );
		$course_id      = $course_data['course']->ID;

		$atc_topic_id = get_post_meta($course_id,'_course_meta_topic_atc',true);
		if($atc_topic_id)
		{
			if ( ! learndash_is_topic_complete( $user_id, $atc_topic_id ) ) {
				var_dump(learndash_process_mark_complete($user_id, $atc_topic_id , false, 89182));
			    update_user_meta($user_id , 'auto_comp_topic_'.$atc_topic_id, time());
			}
			
		}
    }
	/**
     * Adds the meta box to right side.
     */
    public function add_meta_atct_course() {
		add_meta_box(
			'atc_topic_course',
			__( 'CAMT I Practice Periods', 'uncanny-owl' ),
			array( $this, 'show_campt_course_topics' ),
			'sfwd-courses',
			'side',
			'high'
		);
    }

	  /**
     * Renders the meta box.
     */
    public function show_campt_course_topics() {

        global $post;
        // Add nonce for security and authentication.
        wp_nonce_field('custom_nonce_action', 'custom_nonce');

       // CAMT I Practice Periods Course
        // $courses = get_post(89182);
		$course_id = 89182;
		$course_lessons_ids = array();
		$course_lessons     = learndash_get_lesson_list( $course_id );
		if ( ! empty( $course_lessons ) ) {
			$course_lessons_ids = wp_list_pluck( $course_lessons, 'ID' );
		}
		
        // get current custom post selected courses Id
        $atct_course = get_post_meta($post->ID, "_course_meta_topic_atc", true);
        ?>
        <label for="my_meta_box_post_type">Auto-Complete Topics</label>

        <select name='my_meta_box_courses' id='my_meta_box_courses'>
            <option value="">Select Topic</option>
            <?php
            if (count($course_lessons_ids) > 0) {
				$lesson_topics = array();
                foreach ($course_lessons_ids as $course_lessons_id) {
					$lesson_topic_ids = array();
					$lesson_topics = learndash_get_topic_list($course_lessons_id, $course_id);
					if ( ! empty( $lesson_topics ) ) {
						$lesson_topic_ids = wp_list_pluck( $lesson_topics, 'ID' );
					}
					if ( ! empty( $lesson_topic_ids ) ) {
						foreach ($lesson_topic_ids as $lesson_topic_id) {
					
                    ?>
                    <option value="<?php echo $lesson_topic_id; ?>" <?php echo isset($atct_course) && trim($atct_course) == $lesson_topic_id ? "selected" : "" ?>><?php echo get_the_title($lesson_topic_id); ?></option>
					<?php } ?>
					<?php } ?>
                <?php } ?>

            <?php } ?>
        </select>
        <?php
    }
	/**
     * Handles saving the meta box.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @return null
     */
    public function save_meta_group_courses($post_id, $post) {

        // Add nonce for security and authentication.


        $nonce_name = isset($_POST['custom_nonce']) ? $_POST['custom_nonce'] : '';
        $nonce_action = 'custom_nonce_action';

        // Check if nonce is set.
        if (!isset($nonce_name)) {

            return;
        }

        // Check if nonce is valid.
        if (!wp_verify_nonce($nonce_name, $nonce_action)) {

            return;
        }
        // Sanitize the user input.

        $courseId = sanitize_text_field($_POST['my_meta_box_courses']);

        // Update the meta field.
        update_post_meta($post_id, '_course_meta_topic_atc', $courseId);
    }


	public function verify_post_identifier_func() {
		global $wpdb;
		$error  = array();
		$exists = $wpdb->get_col( $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
			'post_identifier', $_GET['post_identifier']
		) );
		if ( ! empty( $exists ) ) {
			if ( isset( $_GET['post_id'] ) && absint( $_GET['post_id'] ) === absint( $exists[0] ) ) {
				$error['status']  = 'success';
				$error['message'] = __( 'Class ID used for this post.', 'uncanny-owl' );
			} else {
				$error['status']  = 'error';
				$error['message'] = __( 'Class ID already used.', 'uncanny-owl' );
			}
		} else {
			$error['status']  = 'success';
			$error['message'] = __( 'Class ID available.', 'uncanny-owl' );
		}

		echo wp_json_encode( $error );
		//return $error;
		wp_die();
	}

	public function add_class_id_tester() {
		?>
		<script>
			//jQuery(document).ready(function ($) {
			jQuery('#check_class_id').click(function () {
				var test_class_id = jQuery('input[name="class-id"]').val();
				var url = '/wp-admin/admin-ajax.php';
				if (test_class_id === '') {
					jQuery('#class_id_error').html('Add Class ID first.').addClass('err').removeClass('suc').show();
				} else {
					jQuery('#class_id_error').html('').hide().removeClass('suc').removeClass('err');
					jQuery.ajax({
						type: 'get',
						data: {
							action: 'verify_post_identifier',
							post_identifier: test_class_id<?php if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['post'] ) ) {
								echo ',
								post_id: ' . $_GET['post'];
							} ?>
						},
						dataType: "json",
						url: url,
						success: function (data) {
							if ('success' === data.status) {
								jQuery('#class_id_error').html(data.message).removeClass('err').addClass('suc').show();
							} else {
								jQuery('#class_id_error').html(data.message).removeClass('suc').addClass('err').show();
							}
						}
					});
				}
			})
			//})
		</script>
		<?php
	}

	/**
	 *
	 */
	public function class_id_admin_notice_handler() {
		$errors = get_option( 'class_id_error' );
		if ( $errors ) {
			echo '<div class="error"><h3>' . $errors . '</h3></div>';
		}
		update_option( 'class_id_error', false );

	}

	/**
	 * Hooks into WordPress' add_meta_boxes function.
	 * Goes through screens (post types) and adds the meta box.
	 */
	public function add_meta_boxes() {
		foreach ( $this->screens as $screen ) {
			add_meta_box(
				'class-id',
				__( 'Class ID', 'uncanny-owl' ),
				array( $this, 'add_meta_box_callback' ),
				$screen,
				'side',
				'high'
			);
		}
	}

	/**
	 * Generates the HTML for the meta box
	 *
	 * @param object $post WordPress post object
	 */
	public function add_meta_box_callback( $post ) {
		wp_nonce_field( 'class_id_data', 'class_id_nonce' );
		echo 'Enter Unique Class ID for this post.';
		$this->generate_fields( $post );
	}

	/**
	 * Generates the field's HTML for the meta box.
	 */
	public function generate_fields( $post ) {
		$output = '';
		foreach ( $this->fields as $field ) {
			$label    = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';
			$db_value = get_post_meta( $post->ID, 'post_identifier', true );
			switch ( $field['type'] ) {
				default:
					$input = sprintf(
						'<input id="%s" name="%s" type="%s" value="%s">',
						$field['id'],
						$field['id'],
						$field['type'],
						$db_value
					);
			}
			$style  = '<style>
							#class_id_error{ background: #f7f7f7; border-left: 8px solid #fff; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); margin: 5px 15px 2px 0; font-weight:bold; padding: 12px; font-size:16px; border-left-color: #dc3232;}
							#class_id_error.err{border-left-color: #dc3232;}
							#class_id_error.suc{border-left-color: #46b450;}
						</style>';
			$div    = '<div id="class_id_error" style="display:none"></div>';
			$a      = ' <a href="javascript:;" id="check_class_id">Check</a>';
			$output .= $style . $div . '<p>' . $label . '<br>' . $input . $a . '</p>';
		}
		echo $output;
	}

	/**
	 * Hooks into WordPress' save_post function
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST['class_id_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['class_id_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'class_id_data' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		$post_id = (int) $_POST['post_ID'];

		foreach ( $this->fields as $field ) {
			if ( isset( $_POST[ $field['id'] ] ) ) {
				switch ( $field['type'] ) {
					case 'email':
						$_POST[ $field['id'] ] = sanitize_email( $_POST[ $field['id'] ] );
						break;
					case 'text':
						$_POST[ $field['id'] ] = sanitize_text_field( $_POST[ $field['id'] ] );
						break;
				}
				if ( ! empty( $_POST[ $field['id'] ] ) ) {
					global $wpdb;
					$exists = $wpdb->get_col( $wpdb->prepare(
						"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
						'post_identifier', $_POST[ $field['id'] ]
					) );
					if ( ! empty( $exists ) && absint( $exists[0] ) !== absint( $post_id ) ) {
						$errors = 'Whoops...Class ID is not unique.';
						update_option( 'class_id_error', $errors );

						return false;
					}
					update_post_meta( $post_id, 'post_identifier', $_POST[ $field['id'] ] );
				} //elseif ( ! empty( $_POST[ $field['id'] ] ) ) {
				//}
			} elseif ( $field['type'] === 'checkbox' ) {
				update_post_meta( $post_id, 'class_id_' . $field['id'], '0' );
			}
		}
	}
}