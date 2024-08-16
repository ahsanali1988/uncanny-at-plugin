<?php
namespace uncanny_advance_trainings;
$user_id = absint( $_GET['user_id'] );
?>
<form method="get" action="<?php echo admin_url( 'admin.php' ) ?>">
	<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
	<input type="hidden" name="user_id" value="<?php echo $_GET['user_id'] ?>"/>
	<?php if ( isset( $_GET['orderby'] ) ) { ?>
		<input type="hidden" name="orderby" value="<?php echo $_GET['orderby'] ?>"/>
	<?php } ?>
	<?php if ( isset( $_GET['order'] ) ) { ?>
		<input type="hidden" name="order" value="<?php echo $_GET['order'] ?>"/>
	<?php } ?>
	<p>
		<?php
		$cats = get_terms( array(
			'taxonomy'   => 'ld_course_category',
			'hide_empty' => false,
		) );
		?>
		<label for="ld-category"><strong>Select Category: </strong></label>
		<select name="ld-category" id="ld-category" onchange="this.form.submit()">
			<option value="all" <?php if ( 'all' === $_GET['ld-category'] ) {
				echo 'selected="selected"';
			} ?>>All Categories
			</option>
			<?php foreach ( $cats as $cat ) {
				$args = array(
					'post_type'      => 'sfwd-courses',
					'posts_per_page' => 9999,
				);

				$args['tax_query'] = array(
					array(
						'taxonomy' => 'ld_course_category',
						'field'    => 'slug',
						'terms'    => $cat->slug,
					),
				);

				$course_completed = [];
				$ceus             = [];
				$courses          = get_posts( $args );
				if ( $courses ) {
					foreach ( $courses as $row ) {
						$post_id = $row->ID;
						if ( ld_course_check_user_access( $post_id, $user_id ) ) {
							$cc = get_user_meta( $user_id, 'course_completed_' . $post_id, true );
							if ( ! empty( $cc ) ) {
								$course_completed[] = $cc;
							}
							$ceu = LearnerDashboardShortcodes::get_ceu_course_details( $post_id, $user_id );
							if ( ! empty( $ceu ) ) {
								$ceus[] = $ceu;
							}
						}
					}
				}
				$count = 0;
				$count = $count + count( $course_completed );
				$count = $count + count( $ceus );
				if ( $ceus ) {
					foreach ( $ceus as $k => $v ) {
						if ( count( $v ) > 1 ) {
							$count = ( $count + count( $v ) ) - 1;
						}
					}
				}
				?>
				<option <?php if ( $cat->slug === $_GET['ld-category'] ) {
					echo 'selected="selected"';
				} ?> value="<?php echo $cat->slug ?>"><?php echo $cat->name; ?>
					[<?php echo $count ?>]
				</option>
			<?php } ?>
		</select>
	</p>
</form>