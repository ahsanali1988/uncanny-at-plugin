<?php

namespace uncanny_advance_trainings;

/**
 * Class UnassignedCourses
 * @package uncanny_advance_trainings
 */
class UnassignedCourses {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_unassigned_courses' ), 9999 );
		add_action( 'init', array( $this, 'add_to_bank_func' ) );
	}

	/**
	 *
	 */
	function add_unassigned_courses() {
		add_submenu_page( 'certifications-management', 'Unassigned Courses', 'Unassigned Courses', 'manage_options', 'unassigned-courses', array(
			$this,
			'unassigned_courses_wrap',
		) );
	}

	/**
	 *
	 */
	function add_to_bank_func() {
		if ( isset( $_GET['action'] ) && 'add-to-bank' === $_GET['action'] && ! empty( $_GET['user_id'] ) && ! empty( $_GET['meta'] ) ) {
			$user_id = (int) $_GET['user_id'];
			$meta    = $_GET['meta'];
			update_user_meta( $user_id, $meta, 999999 );
			$paged = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
			wp_safe_redirect( admin_url( 'admin.php' ) . '?page=unassigned-courses&paged=' . $paged );
			exit;
		}
	}

	/**
	 *
	 */
	function unassigned_courses_wrap() {
		?>
		<style>
			ul.quick-nav {
				list-style-type: none;
				margin: 0;
				margin-bottom: 10px;
				padding: 0;
				overflow: hidden;
				background-color: #dcdcdc;
			}

			.quick-nav li {
				float: left;
				margin-bottom: 0;
			}

			.quick-nav li a {
				display: block;
				color: #585858;
				text-align: center;
				padding: 14px 16px;
				text-decoration: none;
			}

			.quick-nav li a:hover:not(.active) {
				background-color: #9a9a9a;
				color: #000;
			}

			.quick-nav .active {
				background-color: #9a9a9a;
				color: #000;
			}
		</style>
		<div class="wrap">
			<h1>Unassigned Courses</h1>
			<div class="uo-at-table uo-at-table-fixed">
				<?php
				global $wpdb;
				$total = count( $wpdb->get_results( "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key LIKE 'course_completed_%' ORDER BY ABS(meta_value) DESC" ) );
				$pages = floor( $total / 1000 );
				if ( $pages > 0 ) {
					?>
					<ul class="quick-nav">
						<?php
						$j = 1;
						while ( $j <= $pages ) {
							?>
							<li<?php if ( isset( $_GET['paged'] ) && absint( $_GET['paged'] ) === absint( $j ) ) {
								echo ' class="active"';
							} ?>>
								<a href="<?php echo admin_url( 'admin.php' ) ?>?page=unassigned-courses&paged=<?php echo $j; ?>"><?php echo $j; ?></a>
							</li>
							<?php
							$j ++;
						}
						?>
					</ul>
					<?php
				}
				?>
				<table class="tftable_tally wp-list-table widefat fixed striped" id="tftable_tally" border="0" cellpadding="0" cellspacing="0">
					<thead>
					<tr>
						<th>User's Name</th>
						<th>Course Name</th>
						<th>Completion Date</th>
						<th>In The Bank?</th>
					</tr>
					</thead>
					<?php $this->fetch_completed_courses(); ?>
					<tfoot>
					<tr>
						<th>User's Name</th>
						<th>Course Name</th>
						<th>Completion Date</th>
						<th>In The Bank?</th>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * @return array
	 */
	function fetch_completed_courses() {
		global $wpdb;
		$return  = array();
		$paged   = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$start   = isset( $_GET['paged'] ) ? ( $_GET['paged'] - 1 ) * 1000 : 0;
		$results = $wpdb->get_results( "SELECT um.user_id, um.meta_key, um.meta_value, u.display_name 
												FROM $wpdb->usermeta um
												LEFT JOIN $wpdb->users u
												ON u.ID = um.user_id
												WHERE um.meta_key 
												LIKE 'course_completed_%%' 
												ORDER BY ABS(um.meta_value) DESC LIMIT $start, 2000" );
		if ( $results ) {
			foreach ( $results as $result ) {
				$user_id    = $result->user_id;
				$meta_key   = $result->meta_key;
				$meta_value = $result->meta_value;
				$full_name   = $result->display_name;
				$course_id   = (int) str_replace( 'course_completed_', '', $meta_key );
				$course_name = get_the_title( $course_id );
				$applied_to  = $this->get_applied_to_status( $user_id, $course_id, $meta_value );
				if ( 'No' === $applied_to ) {
					?>
					<tr>
					<td>
						<a target="_blank" href="<?php echo admin_url( 'admin.php' ) ?>?page=certifications-management&user_id=<?php echo $user_id ?>#course-records-headings"><?php echo $full_name ?></a>
					</td>
					<td><?php echo $course_name ?></td>
					<td><?php echo date( 'F d, Y', $meta_value ) ?></td>
					<td>No,
						<a href="<?php echo admin_url( 'admin.php' ) ?>?page=unassigned-courses&paged=<?php echo $paged; ?>&action=add-to-bank&user_id=<?php echo $user_id ?>&meta=certification_applied_to_<?php echo $course_id; ?>_<?php echo $meta_value ?>">
							Add To Bank...
						</a>
					</td>
					</tr><?php
				}

				$ceus = LearnerDashboardShortcodes::get_ceu_course_details( $course_id, $user_id );
				if ( $ceus ) {
					foreach ( $ceus as $ceu ) {
						$applied_to = $this->get_applied_to_status( $user_id, $course_id, $ceu['completed'] );
						if ( 'No' === $applied_to ) {
							?>
							<tr>
								<td>
									<a target="_blank" href="<?php echo admin_url( 'admin.php' ) ?>?page=certifications-management&user_id=<?php echo $user_id ?>#course-records-headings"><?php echo $full_name ?></a>
								</td>
								<td><?php echo $course_name ?></td>
								<td><?php echo date( 'F d, Y', $meta_value ) ?></td>
								<td>No,
									<a href="<?php echo admin_url( 'admin.php' ) ?>?page=unassigned-courses&paged=<?php echo $paged; ?>&action=add-to-bank&user_id=<?php echo $user_id ?>&meta=certification_applied_to_<?php echo $course_id; ?>_<?php echo $meta_value ?>">
										Add To Bank...
									</a>
								</td>
							</tr>
							<?php
						}
					}
				}
			}
		}

		return $return;
	}

	/**
	 * @param $user_id
	 * @param $course_id
	 * @param $completed_time
	 *
	 * @return string
	 */
	function get_applied_to_status( $user_id, $course_id, $completed_time ) {
		global $wpdb;
		$applied_to = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key LIKE %s AND user_id = %d", 'certification_applied_to_' . $course_id . '_' . $completed_time, $user_id ) );

		if ( empty( $applied_to ) ) {
			return 'No';
		} else {
			return 'Yes';
		}
	}
}