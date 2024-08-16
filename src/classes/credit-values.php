<?php


namespace uncanny_advance_trainings;


class CreditValues {

	private $screens = array(
		'sfwd-courses',
	);
	private $fields = array(
		array(
			'id'    => 'ncb-credits',
			'label' => 'NCB Credits',
			'type'  => 'number',
		),
		/*array(
			'id'    => 'ncb-renewal-credits',
			'label' => 'NCB Renewal Credits',
			'type'  => 'number',
		),*/
		array(
			'id'    => 'camt-units',
			'label' => 'CAMT Units',
			'type'  => 'number',
		),
		/*array(
			'id'    => 'camt-renewal-units',
			'label' => 'CAMT Renewal Units',
			'type'  => 'number',
		),*/
	);

	/**
	 * Class construct method. Adds actions to their respective WordPress hooks.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Hooks into WordPress' add_meta_boxes function.
	 * Goes through screens (post types) and adds the meta box.
	 */
	public function add_meta_boxes() {
		foreach ( $this->screens as $screen ) {
			add_meta_box(
				'credit-values',
				__( 'Credit Values', 'uncanny-owl' ),
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
		wp_nonce_field( 'credit_values_data', 'credit_values_nonce' );
		$this->generate_fields( $post );
	}

	/**
	 * Generates the field's HTML for the meta box.
	 */
	public function generate_fields( $post ) {
		$output = '';
		foreach ( $this->fields as $field ) {
			$label    = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';
			$db_value = get_post_meta( $post->ID, $field['id'], true );
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
			$output .= '<p>' . $label . '<br>' . $input . '</p>';
		}
		echo $output;
	}

	/**
	 * Hooks into WordPress' save_post function
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST['credit_values_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['credit_values_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'credit_values_data' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

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
				update_post_meta( $post_id, $field['id'], $_POST[ $field['id'] ] );
			} elseif ( $field['type'] === 'checkbox' ) {
				update_post_meta( $post_id, 'credit_values_' . $field['id'], '0' );
			}
		}
	}
}