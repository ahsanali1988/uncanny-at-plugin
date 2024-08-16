<?php

namespace uncanny_advance_trainings;
global $wpdb;
$user_id = absint( $_GET['user_id'] );
?>
	<hr/>
	<h3>Tally</h3>
	<div id="save_msg_tally" class="table-msg" style="display: none;"></div>
	<form>
		<div class="uo-at-table uo-at-table-fixed">
			<table class="tftable_tally wp-list-table widefat fixed striped" id="tftable_tally" border="0" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Certification and category</th>
					<th>Units Earned</th>
					<th>Units Required</th>
					<th>Mod by/dt</th>
					<th>Admin Notes</th>
					<th>Add Notes</th>
				</tr>
				</thead>
				<?php
				$certifications = get_posts( array(
					'post_type'      => 'certification',
					'posts_per_page' => 999,
					'orderby'        => 'title',
					'order'          => 'ASC',
				) );
				if ( $certifications ) {
					foreach ( $certifications as $k => $row ) {
						$post_id    = $row->ID;
						$terms      = wp_get_post_terms( $post_id, 'certification_category', array() );
						$sort_terms = array();
						if ( $terms ) {
							foreach ( $terms as $key => $term ) {
								$order = get_term_meta( $term->term_id, 'order', true );
								if ( empty( $order ) ) {
									$order = 0;
								}
								$sort_terms[ $order ] = $terms[ $key ];
							}
							ksort( $sort_terms );
						}

						if ( ! empty( $sort_terms ) ) {
							foreach ( $sort_terms as $term ) {
								$term_id          = $term->term_id;
								if($term_id == 98 || $term_id == 129)
								{
								continue;
								}
								$term_name        = $term->name;
								$units_required   = get_term_meta( $term_id, 'certification-requirement', true );
								$units_earned     = absint(get_user_meta( $user_id, 'certification_units_earned_' . $post_id . '_' . $term_id, true ));
								if($term_id == 95){
									$camt1_earned = absint( get_user_meta( $user_id, "certification_units_earned_" . $post_id . "_" . 98, true ) );	
									$units_earned = $units_earned + $camt1_earned;
								}
								if($term_id == 128){
									$camt2_earned = absint( get_user_meta( $user_id, "certification_units_earned_" . $post_id . "_" . 129, true ) );	
									$units_earned = $units_earned + $camt2_earned;
								}
								$last_modified_by = get_user_meta( $user_id, 'certification_last_modified_' . $post_id . '_' . $term_id, true );
								$admin_notes      = get_user_meta( $user_id, 'admin_notes_' . $post_id . '_' . $term_id, true );
								?>
								<tr>
									<td>
										<input type="hidden" value="<?php echo $post_id ?>" name="post_id"/>
										<input type="hidden" value="<?php echo $term_id ?>" name="term_id"/>
										<b><?php echo $row->post_title ?></b> &mdash; <?php echo $term_name; ?>
									</td>
									<td>
										<input style="max-width:100%;" type="text" data-certificate="<?php echo $post_id ?>" data-term="<?php echo $term_id ?>" name="certification_units_earned_<?php echo $post_id ?>_<?php echo $term_id; ?>" value="<?php echo $units_earned; ?>"/>
									</td>
									<td>
										<?php echo $units_required; ?>
									</td>
									<td>
										<?php echo $last_modified_by; ?>
									</td>
									<td id="admin_notes_<?php echo $post_id ?>_<?php echo $term_id; ?>_html">
										<p><?php echo $admin_notes; ?></p>
									</td>
									<td>
										<textarea style="max-width:100%;" name="admin_notes_<?php echo $post_id ?>_<?php echo $term_id; ?>" data-certificate="<?php echo $post_id ?>" data-term="<?php echo $term_id ?>" id="admin_notes_<?php echo $post_id ?>_<?php echo $term_id; ?>" cols="30" rows="2"></textarea>
									</td>
								</tr>
							<?php } ?>
							<tr>
								<td colspan="6">&nbsp;</td>
							</tr>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				<tfoot>
				<tr>
					<th>Certification and category</th>
					<th>Units Earned</th>
					<th>Units Required</th>
					<th>Mod by/dt</th>
					<th>Admin Notes</th>
					<th>Add Notes</th>
				</tr>
				</tfoot>
			</table>
		</div>
	</form>


<?php
