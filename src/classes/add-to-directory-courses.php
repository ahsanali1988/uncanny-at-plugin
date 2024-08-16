<?php

namespace uncanny_advance_trainings;

/**
 * Class directorycourseMetabox
 * @package uncanny_advance_trainings
 */
class AddToDirectoryCourses {
	private $screen = array(
		'sfwd-courses',
	);
	private $meta_fields = array(
		array(
			'label'   => 'Exclude in Directory',
			'id'      => '_exclude_in_directory',
			'default' => '0',
			'type'    => 'checkbox',
		),
	);

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_fields' ) );
	}

	/**
	 *
	 */
	public function add_meta_boxes() {
		foreach ( $this->screen as $single_screen ) {
			add_meta_box(
				'directorycourse',
				__( 'Directory Course', 'uncanny-owl' ),
				array( $this, 'meta_box_callback' ),
				$single_screen,
				'side',
				'high'
			);
		}
	}

	/**
	 * @param $post
	 */
	public function meta_box_callback( $post ) {
		wp_nonce_field( 'directorycourse_data', 'directorycourse_nonce' );
		$this->field_generator( $post );
	}

	/**
	 * @param $post
	 */
	public function field_generator( $post ) {
		$output = '';
		foreach ( $this->meta_fields as $meta_field ) {
			$label      = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
			$meta_value = get_post_meta( $post->ID, $meta_field['id'], true );
			if ( empty( $meta_value ) ) {
				$meta_value = $meta_field['default'];
			}
			switch ( $meta_field['type'] ) {
				case 'checkbox':
					$input = sprintf(
						'<input %s id=" % s" name="% s" type="checkbox" value="1">',
						$meta_value === '1' ? 'checked' : '',
						$meta_field['id'],
						$meta_field['id']
					);
					break;
				default:
					$input = sprintf(
						'<input %s id="%s" name="%s" type="%s" value="%s">',
						$meta_field['type'] !== 'color' ? 'style="width: 100%"' : '',
						$meta_field['id'],
						$meta_field['id'],
						$meta_field['type'],
						$meta_value
					);
			}
			$output .= $this->format_rows( $label, $input );
		}
		echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
	}

	/**
	 * @param $label
	 * @param $input
	 *
	 * @return string
	 */
	public function format_rows( $label, $input ) {
		return '<tr><td>' . $input . '</td><th>' . $label . '</th></tr>';
	}

	/**
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function save_fields( $post_id ) {
		if ( ! isset( $_POST['directorycourse_nonce'] ) ) {
			return $post_id;
		}
		$nonce = $_POST['directorycourse_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'directorycourse_data' ) ) {
			return $post_id;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		foreach ( $this->meta_fields as $meta_field ) {
			if ( isset( $_POST[ $meta_field['id'] ] ) ) {
				switch ( $meta_field['type'] ) {
					case 'email':
						$_POST[ $meta_field['id'] ] = sanitize_email( $_POST[ $meta_field['id'] ] );
						break;
					case 'text':
						$_POST[ $meta_field['id'] ] = sanitize_text_field( $_POST[ $meta_field['id'] ] );
						break;
				}
				update_post_meta( $post_id, $meta_field['id'], $_POST[ $meta_field['id'] ] );
			} else if ( $meta_field['type'] === 'checkbox' ) {
				update_post_meta( $post_id, $meta_field['id'], '0' );
			}
		}
	}
}