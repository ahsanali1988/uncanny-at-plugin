<?php

namespace uncanny_advance_trainings;
$user_id = absint( $_GET['user_id'] );
?>
	<script>
      function returnConfirmation () {
        confirm('Are you sure to delete this record?')
      }
	</script>
	<p>&nbsp;</p>
	<hr/>
	<h3 id="course-records-headings">Course Records</h3>
	<?php include( 'course-records-select.php' ) ?>
	<div id="save_msg_records" class="table-msg" style="display: none;"></div>
	<form>
		<input type="hidden" value="<?php echo $user_id ?>" name="user_id" id="user_id"/>
		<div class="uo-at-table uo-at-table-fixed">
			<table class="tftable-records wp-list-table widefat fixed striped" id="tftable-records" border="0" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<?php
					if ( ! isset( $_GET['order'] ) ) {
						$sort = 'asc';
					} elseif ( isset( $_GET['order'] ) && 'asc' === $_GET['order'] ) {
						$sort = 'desc';
					} elseif ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
						$sort = 'asc';
					}
					$user_id = (int) $_GET['user_id'];
					$page    = $_GET['page'];
					if ( isset( $_GET['ld-category'] ) ) {
						$category = '&ld-category=' . $_GET['ld-category'];
					} else {
						$category = '';
					}
					?>
					<th style="width:100px;">
						<a href="<?php admin_url( 'admin.php' ) ?>?page=<?php echo $page ?>&user_id=<?php echo $user_id ?>&orderby=course_id&order=<?php echo $sort; ?><?php echo $category ?>#tftable-records">
							Course ID
						</a>
					</th>
					<th>
						<a href="<?php admin_url( 'admin.php' ) ?>?page=<?php echo $page ?>&user_id=<?php echo $user_id ?>&orderby=short_course_name&order=<?php echo $sort; ?><?php echo $category ?>#tftable-records">
							Short Course Name
						</a>
					</th>
					<th>
						<a href="<?php admin_url( 'admin.php' ) ?>?page=<?php echo $page ?>&user_id=<?php echo $user_id ?>&orderby=enrolled&order=<?php echo $sort; ?><?php echo $category ?>#tftable-records">
							Enrolled
						</a>
					</th>
					<th>
						<a href="<?php admin_url( 'admin.php' ) ?>?page=<?php echo $page ?>&user_id=<?php echo $user_id ?>&orderby=completed&order=<?php echo $sort; ?><?php echo $category ?>#tftable-records">
							Compl. Date
						</a>
					</th>
					<th style="width:200px;">Applied To</th>
					<th style="width:100px;">
						<a href="<?php admin_url( 'admin.php' ) ?>?page=<?php echo $page ?>&user_id=<?php echo $user_id ?>&orderby=ncb&order=<?php echo $sort; ?><?php echo $category ?>#tftable-records">
							NCB Credits
						</a>
					</th>
					<th style="width:100px;">
						<a href="<?php admin_url( 'admin.php' ) ?>?page=<?php echo $page ?>&user_id=<?php echo $user_id ?>&orderby=camt&order=<?php echo $sort; ?><?php echo $category ?>#tftable-records">
							CAMT Units
						</a>
					</th>
					<th>Mod by/dt</th>
					<th>Admin Notes</th>
					<th>Add Notes</th>
					<th>Actions</th>
				</tr>
				</thead>
				<?php
				if ( $final_data ) {
					// if($_SERVER['REMOTE_ADDR'] == "39.46.134.197"){
					// 	echo "<pre>";
					// 	print_r($final_data);
					// 	exit;
					// 	}
					foreach ( $final_data as $data ) {
						foreach ( $data as $row ) {
							$post_id          = $row['post_id'];
							$short            = $row['short'];
							$class_id         = $row['class_id'];
							$enrolled         = $row['enrolled'];
							$lapsed           = $row['lapsed'];
							$notes            = $row['notes'];
							$last_modified_by = $row['last_modified_by'];
							$course_completed = $row['course_completed'];
							$ncb              = $row['ncb'];
							$camt             = $row['camt'];
							$applied_to       = $row['applied_to'];
							?>
							<tr style="<?php echo $row['background']; ?>">
								<td style="width:100px; font-size:11px;">
									<?php echo $class_id; ?>
								</td>
								<td>
									<input type="hidden" value="<?php echo $post_id ?>" name="post_id"/>
									<?php echo $short ?>
								</td>
								<td>
									<?php echo ! empty( $enrolled ) ? date( 'm/d/Y', $enrolled ) : '' ?>
								</td>
								<td>
									<?php echo ! empty( $course_completed ) ? date( 'm/d/Y', $course_completed ) : '' ?>
								</td>
								<td style="width:200px;">
									<select name="certification_applied_to_<?php echo $post_id; ?>_<?php echo $course_completed; ?>" data-course="<?php echo $post_id; ?>" style="max-width:100%;">
										<option value="0">Select Certification</option>
										<option value="888888"
											<?php if ( 888888 === (int) $applied_to ) {
												echo 'selected="selected"';
											} ?>>Unapplied
										</option>
										<option value="911911911"
											<?php if ( 911911911 === (int) $applied_to ) {
												echo 'selected="selected"';
											} ?>>Ignore This Completion
										</option>
										<option value="999999"<?php if ( 999999 === (int) $applied_to ) {
											echo 'selected="selected"';
										} ?>>In The Bank
										</option>
										<?php $certifications = get_posts( array(
											'post_type'      => 'certification',
											'posts_per_page' => 999,
											'orderby'        => 'title',
											'order'          => 'ASC',
										) );
										if ( $certifications ) {
											foreach ( $certifications as $row ) {
												$certificate_id = $row->ID;
												$terms          = wp_get_post_terms( $certificate_id, 'certification_category', array() );
												$sort_terms     = array();
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
													?>
													<optgroup label="<?php echo $row->post_title ?>">
														<?php
														foreach ( $sort_terms as $term ) {
															$term_id   = $term->term_id;
															if($term_id == 98 || $term_id == 129)
															{
															continue;
															}
															$term_name = $term->name;
															$val       = $certificate_id . '_' . $term_id;
															?>
															<option <?php if ( $val === $applied_to ) {
																echo 'selected="selected"';
															} ?> value="<?php echo $val ?>"><?php echo $row->post_title ?>
																&mdash; <?php echo $term_name; ?></option>
															<?php
														}
														?>
													</optgroup>
													<?php
												}
											}
										}
										?>
									</select>
								</td>
								<td style="width:100px;">
									<input style="max-width:100%;" type="text" data-course="<?php echo $post_id ?>" name="ncb-credits-<?php echo $post_id ?>" value="<?php echo $ncb; ?>">
								</td>
								<td style="width:100px;">
									<input style="max-width:100%;" type="text" data-course="<?php echo $post_id ?>" name="camt-credits-<?php echo $post_id ?>" value="<?php echo $camt; ?>">
								</td>
								<td><?php echo $last_modified_by; ?></td>
								<td id="admin_record_notes_<?php echo $post_id ?>_html">
									<p><?php echo $notes; ?></p>
								</td>
								<td>
								<textarea style="max-width:100%;"
								          data-course="<?php echo $post_id; ?>"
								          name="admin_record_notes_<?php echo $post_id ?>"
								          id="admin_record_notes_<?php echo $post_id ?>" cols="30" rows="2"></textarea>
								</td>
								<td style="text-align: right;">
									<?php if ( ! empty( $course_completed ) ) { ?>
										<a class="delete-ceu-record" data-user="<?php echo $user_id ?>" data-course="<?php echo $post_id ?>" data-time="<?php echo $course_completed ?>" href="javascript:;" onclick="return returnConfirmation()">
											Delete Record
										</a>
									<?php } ?>
								</td>
							</tr>
						<?php } ?>
					<?php }
				} ?>
				<tfoot>
				<tr>
					<th style="width:100px;">Course ID</th>
					<th>Short Course Name</th>
					<th>Enrolled</th>
					<th>Compl. Date</th>
					<th>Applied To</th>
					<th style="width:200px;">NCB Credits</th>
					<th style="width:100px;">CAMT Units</th>
					<th style="width:100px;">Mod by/dt</th>
					<th>Admin Notes</th>
					<th>Add Notes</th>
					<th>Actions</th>
				</tr>
				</tfoot>
			</table>
		</div>
	</form>
	<?php if ( isset( $_GET['ld-category'] ) ) { ?>
	<script>
      //jQuery(document).ready(function ($) {
      var t = setTimeout('scrollingTo()', 1000)

      //})
      function scrollingTo () {
        jQuery('html, body').animate({
          scrollTop: jQuery('#tftable-records').offset().top - 200
        }, 1500)
      }
	</script>
<?php }
