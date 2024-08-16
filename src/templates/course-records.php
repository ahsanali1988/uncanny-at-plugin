<?php

namespace uncanny_advance_trainings;
$user_id    = absint( $_GET['user_id'] );
$final_data = [];
$sort_key   = '';
$sort_order = '';
$args       = array(
	'post_type'      => 'sfwd-courses',
	'posts_per_page' => 9999,
);
if ( isset( $_GET['ld-category'] ) && 'all' !== $_GET['ld-category'] ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'ld_course_category',
			'field'    => 'slug',
			'terms'    => $_GET['ld-category'],
		),
	);
}
if ( isset( $_GET['orderby'] ) ) {
	switch ( $_GET['orderby'] ) {
		case 'course_id':
			$args['orderby']  = 'meta_value';
			$args['meta_key'] = 'post_identifier';
			break;
		case 'short_course_name':
			$args['orderby']  = array( 'meta_value' => $_GET['order'], 'title' => $_GET['order'] );
			$args['meta_key'] = 'course_abbr';
			break;
		case 'enrolled':
			break;
		case 'completed':
			break;
		case 'ncb':
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'ncb-credits';
			break;
		case 'camt':
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'camt-units';
			break;
		default:
			$args['orderby'] = 'menu_order';
			break;
	}
} else {
	$args['orderby'] = 'menu_order';
}
if ( isset( $_GET['order'] ) ) {
	$args['order'] = $_GET['order'];
} else {
	$args['order'] = 'ASC';
}

if ( isset( $_GET['order'] ) ) {
	$sort_order = $_GET['order'];
} else {
	$sort_order = 'ASC';
}

$courses  = get_posts( $args );
$key      = '';
$class_id = '';
$short    = '';
if ( $courses ) {
	foreach ( $courses as $row ) {
		$post_id = $row->ID;
		if ( ld_course_check_user_access( $post_id, $user_id ) ) {
			$short = get_post_meta( $post_id, 'course_abbr', true );
			if ( empty( $short ) ) {
				$short = $row->post_title;
			}
			$class_id         = get_post_meta( $post_id, 'post_identifier', true );
			$enrolled         = get_user_meta( $user_id, 'course_' . $post_id . '_access_from', true );
			$lapsed           = get_user_meta( $user_id, 'course_completed_' . $post_id, true );
			$notes            = get_user_meta( $user_id, 'admin_record_notes_' . $post_id, true );
			$last_modified_by = get_user_meta( $user_id, 'record_last_modified_' . $post_id, true );
			$course_completed = get_user_meta( $user_id, 'course_completed_' . $post_id, true );
			$ncb_user         = get_user_meta( $user_id, 'ncb-credits-' . $post_id, true );
			$camt_user        = get_user_meta( $user_id, 'camt-credits-' . $post_id, true );

			if ( empty( $ncb_user ) ) {
				$ncb = get_post_meta( $post_id, 'ncb-credits', true );
			} else {
				$ncb = $ncb_user;
			}

			if ( empty( $camt_user ) ) {
				$camt = get_post_meta( $post_id, 'camt-units', true );
			} else {
				$camt = $camt_user;
			}

			if ( empty( $course_completed ) ) {
				$course_completed = '';
			}
			$applied_to = get_user_meta( $user_id, 'certification_applied_to_' . $post_id . '_' . $course_completed, true );
			switch ( $_GET['orderby'] ) {
				case 'course_id':
					$key = $post_id;
					break;
				case 'short_course_name':
					$key = sanitize_title( $short );
					break;
				case 'enrolled':
					$key = $enrolled;
					break;
				case 'completed':
					$key = $course_completed;
					break;
				case 'ncb':
					$key = $ncb;
					break;
				case 'camt':
					$key = $camt;
					break;
				default:
					$key = $post_id;
					break;
			}
			$final_data[ $key ][] = [
				'post_id'          => $post_id,
				'class_id'         => $class_id,
				'short'            => $short,
				'enrolled'         => ! empty( $enrolled ) ? $enrolled : '',
				'course_completed' => $course_completed,
				'applied_to'       => $applied_to,
				'ncb'              => $ncb,
				'camt'             => $camt,
				'last_modified_by' => $last_modified_by,
				'notes'            => $notes,
				'lapsed'           => ! empty( $lapsed ) ? $lapsed : '',
				'background'       => '',
			];
		}

		/**********************************************************/
		/**********************************************************/
		/**********************************************************/
		/****************** CEU STARTS HERE ***********************/
		/**********************************************************/
		/**********************************************************/
		/**********************************************************/
		/**********************************************************/

		$ceus = LearnerDashboardShortcodes::get_ceu_course_details( $post_id, $user_id );
		$enrolled_check = get_user_meta( $user_id, 'certification_start_date_' . $post_id, true );
		if ( $ceus && $enrolled_check) {
			$j = 1;
			foreach ( $ceus as $ceu ) {
				switch ( $j ) {
					case 1:
						$background = 'background: rgba(232,232,232, 1);';
						break;
					case 2:
						$background = 'background: rgba(224,224,224, 1);';
						break;
					case 3:
						$background = 'background: rgba(220,220,220, 1);';
						break;
					case 4:
						$background = 'background: rgba(216,216,216, 1);';
						break;
					case 5:
						$background = 'background: rgba(208,208,208, 1);';
						break;
					case 6:
						$background = 'background: rgba(200,200,200, 1);';
						break;
					case 7:
						$background = 'background: rgba(192,192,192, 1);';
						break;
					case 8:
						$background = 'background: rgba(190,190,190, 1);';
						break;
					case 9:
						$background = 'background: rgba(184,184,184, 1);';
						break;
					case 10:
						$background = 'background: rgba(176,176,176, 1);';
						break;
					default:
						$background = 'background: rgba(211,211,211, 1);';
						break;
				}
				$j ++;
				$applied_to = get_user_meta( $user_id, 'certification_applied_to_' . $post_id . '_' . $ceu['completed'], true );

				$ncb_user  = get_user_meta( $user_id, 'ncb-renewal-credits-' . $post_id . '-' . $ceu['completed'], true );
				$camt_user = get_user_meta( $user_id, 'camt-renewal-units-' . $post_id . '-' . $ceu['completed'], true );
				if ( empty( $ncb_user ) ) {
					$ncb = get_post_meta( $post_id, 'ncb-renewal-credits', true );
				} else {
					$ncb = $ncb_user;
				}
				if ( empty( $camt_user ) ) {
					$camt = get_post_meta( $post_id, 'camt-renewal-units', true );
				} else {
					$camt = $camt_user;
				}
				$last_modified = get_user_meta( $user_id, 'ceu_record_last_modified_' . $ceu['completed'] . '_' . $post_id, true );
				$admin_notes   = get_user_meta( $user_id, 'ceu_admin_record_notes_' . $ceu['completed'] . '_' . $post_id, true );
				switch ( $_GET['orderby'] ) {
					case 'completed':
						$key = $ceu['completed'];
						break;
					case 'ncb':
						$key = $ncb;
						break;
					case 'camt':
						$key = $camt;
						break;
					default:
						$key = $post_id;
						break;
				}
				$final_data[ $key ][] = [
					'post_id'          => $post_id,
					'class_id'         => $class_id,
					'short'            => $short,
					'enrolled'         => get_user_meta( $user_id, 'certification_start_date_' . $post_id, true ),
					'course_completed' => ! empty( $ceu['completed'] ) ? $ceu['completed'] : '',
					'applied_to'       => $applied_to,
					'ncb'              => $ncb,
					'camt'             => $camt,
					'last_modified_by' => $last_modified,
					'notes'            => $admin_notes,
					'lapsed'           => '',
					'background'       => $background,
				];
			}
		}
	}
}
if ( 'asc' === strtolower( $sort_order ) ) {
	ksort( $final_data );
} else {
	krsort( $final_data );
}

include( 'course-records-template.php' );