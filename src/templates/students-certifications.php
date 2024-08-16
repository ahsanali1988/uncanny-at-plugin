<?php
namespace uncanny_advance_trainings;
global $wpdb;
$user_id = absint( $_GET['user_id'] );
?>
<hr/>
<div id="save_msg" class="table-msg" style="display: none;"></div>
<h3>Students Certifications</h3>
<form>
	<input type="hidden" value="<?php echo $user_id ?>" name="user_id" id="user_id"/>
	<table class="tftable wp-list-table widefat fixed striped" id="tftable" border="0" cellpadding="0" cellspacing="0">
		<thead>
		<tr>
			<th>Certification</th>
			<th>Status</th>
			<th>Date Enrolled</th>
			<th>Date Completed</th>
			<th>Renewal Date</th>
			<th>Admin Notes</th>
			<th>Add Notes</th>
		</tr>
		</thead>
		<?php
		$certifications = get_posts( array(
			'post_type'      => 'certification',
			'posts_per_page' => 999,
		) );
		if ( $certifications ) {
			foreach ( $certifications as $row ) {
				$post_id   = $row->ID;
				if($post_id == 39383 || $post_id == 39376)
				{
					continue;
				}
				$status    = get_user_meta( $user_id, 'certification_status_' . $post_id, true );
				$status_id = $wpdb->get_var( "SELECT umeta_id FROM {$wpdb->usermeta} WHERE user_id = {$user_id} AND meta_key LIKE 'certification_status_$post_id'" );
				$enrolled  = get_user_meta( $user_id, 'certification_start_date_' . $post_id, true );
				$lapsed    = get_user_meta( $user_id, 'certification_lapse_date_' . $post_id, true );
				$notes     = get_user_meta( $user_id, 'admin_notes_' . $post_id, true );
				?>
				<tr>
					<td>
						<input type="hidden" value="<?php echo $post_id ?>" name="post_id"/>
						<?php echo $row->post_title ?>
					</td>
					<td>
						<select style="max-width:100%;" name="certification_status_<?php echo $post_id ?>" data-umeta="<?php echo $status_id; ?>" id="certification_status_<?php echo $post_id ?>">
							<option value="">--- Select Status ---</option>
							<option value="lapsed" <?php if ( 'lapsed' === strtolower( $status ) ) {
								echo 'selected="selected"';
							} ?>>Expired
							</option>
							<option value="enrolled" <?php if ( 'enrolled' === strtolower( $status ) ) {
								echo 'selected="selected"';
							} ?>>Enrolled
							</option>
							<option value="Not Yet Enrolled" <?php if ( '' === strtolower( $status ) || 'not yet enrolled' === strtolower( $status ) ) {
								echo 'selected="selected"';
							} ?>>Not Yet Enrolled
							</option>
							<option value="certified" <?php if ( 'certified' === strtolower( $status ) ) {
								echo 'selected="selected"';
							} ?>>Certified
							</option>
						</select></td>
					<td>
						<!--<input type="text" name="certification_start_date_<?php /*echo $post_id */ ?>" id="certification_start_date_<?php /*echo $post_id */ ?>" class="datepicker" value="<?php /*if ( ! empty( $enrolled ) ) {
										echo date( 'F j, Y', $enrolled );
									} */ ?>"/>-->
						<?php if ( ! empty( $enrolled ) ) {
							if ( is_numeric( $enrolled ) ) {
								echo date( 'F j, Y', $enrolled );
							} elseif ( strpos( $enrolled, '-' ) ) {
								echo date( 'F j, Y', strtotime( $enrolled ) );
							}
						} ?></td>
					<td>
						<input style="max-width:100%;" type="text" name="certification_lapse_date_<?php echo $post_id ?>" id="certification_lapse_date_<?php echo $post_id ?>" class="datepicker" value="<?php if ( ! empty( $lapsed ) ) {
							if ( is_numeric( $lapsed ) ) {
								echo date( 'F j, Y', $lapsed );
							} elseif ( strpos( $lapsed, '-' ) ) {
								echo date( 'F j, Y', strtotime( $lapsed ) );
							}
						} ?>"/></td>
					<td>
						<?php

						$renewal_key = 'certification_renewal_date';
						//if ( 39391 !== $post_id ) {
						$renewal_key .= '_' . $post_id;
						//}
						$renewal = get_user_meta( $user_id, $renewal_key, true );
						?>
						<input style="max-width:100%;" type="text" name="<?php echo $renewal_key ?>" id="<?php echo $renewal_key ?>" class="datepicker" value="<?php if ( ! empty( $renewal ) ) {
							if ( is_numeric( $renewal ) ) {
								echo date( 'F j, Y', $renewal );
							} elseif ( strpos( $renewal, '-' ) ) {
								echo date( 'F j, Y', strtotime( $renewal ) );
							}
						} ?>"/>
					</td>
					<td id="admin_notes_<?php echo $post_id ?>_html">
						<p><?php echo $notes; ?></p>
					</td>
					<td>
						<textarea style="max-width:100%;" name="admin_notes_<?php echo $post_id ?>" id="admin_notes_<?php echo $post_id ?>" cols="30" rows="2"></textarea>
					</td>
				</tr>
			<?php } ?>
		<?php } ?>
		<tfoot>
		<tr>
			<th>Certification</th>
			<th>Status</th>
			<th>Date Enrolled</th>
			<th>Date Completed</th>
			<th>Renewal Date</th>
			<th>Admin Notes</th>
			<th>Add Notes</th>
		</tr>
		</tfoot>
	</table>
</form>
