<?php	if( isset( $_REQUEST['list-id'] ) ) {			$list_id = $_REQUEST['list-id'];		// run our API call, to get list data..		$MailChimp	= new Mailchimp( get_option( 'yikes-mc-api-key' , '' ) );		$api_key = get_option( 'yikes-mc-api-key' , '' );		// get this lists data		$list_data = $MailChimp->call( 'lists/list' , array( 'apikey' => $api_key, 'filters' => array( 'list_id' => $list_id ) ) );		// reset our data so we can easily use it		$list_data = $list_data['data'][0];				// get the merge_variables		$merge_variables = $MailChimp->call( 'lists/merge-vars' , array( 'apikey' => $api_key , 'id' => array( $list_id ) ) );		// re-store our data		$merge_variables = $merge_variables['data'][0]['merge_vars'];				// get the interest group data		try {			$interest_groupings = $MailChimp->call( 'lists/interest-groupings' , array( 'apikey' => $api_key , 'id' => $list_id , 'counts' => true ) );		} catch( Exception $error ) {			$no_interest_groupings = $error->getMessage();		}				$no_segments = __( 'No segments set up for this list.' , $this->text_domain );		// get the segment data		try {			$segments = $MailChimp->call( 'lists/segments' , array( 'apikey' => $api_key , 'id' => $list_id , 'type' => 'saved' ) );		} catch( Exception $segment_error ) {			$no_segments = $error->getMessage();		}				// setup pagination variables		$paged = isset( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : '0';				$limit = apply_filters( 'yikes_admin_list_subscriber_limit' , '20' );				$sort_dir = isset( $_REQUEST['sort'] ) ? $_REQUEST['sort'] : 'DESC';		$column = isset( $_REQUEST['column'] ) ? $_REQUEST['column'] : 'optin_time';				if( $sort_dir == 'DESC' ) {			$opposite_sort_dir = 'ASC';			$icon = '<span class="dashicons dashicons-arrow-down"></span>';		} else {			$opposite_sort_dir = 'DESC';			$icon = '<span class="dashicons dashicons-arrow-up"></span>';		}				if( !isset( $_REQUEST['sort'] ) ) {				$icon = '';		}				// get all subscribed members		$subscribers_list = $MailChimp->call('lists/members', 			array(				'id'	=>	$list_id,				'opts'	=>	array(								'start' => $paged,					'limit'	=>	$limit,					'sort_field'	=>	$column,					'sort_dir'	=>	$sort_dir				)				)			);				$total_pages = ceil( $subscribers_list['total'] / $limit );		if( $total_pages == 0 ) {			$total_pages = '1';		}			}	?><div class="wrap">	<!-- Freddie Logo -->	<img src="<?php echo YIKES_MC_URL . 'includes/images/MailChimp_Assets/Freddie_60px.png'; ?>" alt="Freddie - MailChimp Mascot" style="float:left;margin-right:10px;" />			<h2>Easy MailChimp by Yikes Inc. | <?php echo $list_data['name']; ?></h2>							<!-- Settings Page Description -->	<p class="yikes-easy-mc-about-text about-text"><?php _e( 'View all subscribers below. View additional subscriber info, or add additional fields to this list.' , $this->text_domain ); ?></p>	<p style="display:block;margin-top:2em;"><a href="#" onclick="jQuery(this).parent().next().slideToggle();" class="add-new-h2"><?php _e( 'New Subscriber' , $this->text_domain ); ?></a></p>		<?php		/* Display our admin notices here */		// Unsubscribe user confirmation message		if( isset( $_REQUEST['user-unsubscribed'] ) && $_REQUEST['user-unsubscribed'] == 'true' ) {			?>			<div class="updated manage-form-admin-notice">				<p><?php _e( 'User successfully unsubscribed.', $this->text_domain ); ?></p>			</div>			<?php		}		if( isset( $_REQUEST['user-unsubscribed'] ) && $_REQUEST['user-unsubscribed'] == 'false' ) {			?>			<div class="error manage-form-admin-notice">				<p><?php _e( "We've encountered an error trying to remove the subscriber. Please try again. If the error persists please get in contact with the Yikes Inc. support staff.", $this->text_domain ); ?></p>			</div>			<?php		}	?>		<section style="display:none;padding-top:1em;">		<h4 style="margin-top:0;"><?php _e( 'Add New Subscriber' , $this->text_domain ); ?></h4>		<form id="add-new-subcscriber">			<input type="text" class="regular-text" placeholder="<?php _e( 'User Email Address' , $this->text_domain ); ?>" /></p>			<p><?php echo submit_button( 'Add Subscriber' ); ?></p>		</form>	</section>		<!-- entire body content -->		<div id="poststuff">				<div id="post-body" class="metabox-holder columns-2">							<!-- main content -->				<div id="post-body-content">										<div class="meta-box-sortables ui-sortable">												<div class="postbox yikes-easy-mc-postbox">																						<table class="wp-list-table widefat fixed posts" cellspacing="0" id="yikes-easy-mc-manage-forms-table">											<!-- TABLE HEAD -->									<thead>										<tr>											<th id="cb" class="manage-column column-cb check-column" scope="col"><input type="checkbox" /></th>											<th id="user-email columnname" class="manage-column column-columnname" scope="col"><a id="user-email-sort" href="<?php echo esc_url( add_query_arg( array( 'column' => 'email' , 'sort' => $opposite_sort_dir ) ) ); ?>"><?php _e( 'User Email' , $this->text_domain ); echo $icon;?></a></th>											<th id="columnname" class="manage-column column-columnname num" scope="col"><?php _e( 'Email Client' , $this->text_domain ); ?></th>										</tr>									</thead>									<!-- end header -->																		<!-- FOOTER -->									<tfoot>										<tr>											<th class="manage-column column-cb check-column" scope="col"><input type="checkbox" /></th>											<th class="manage-column column-columnname" scope="col"><?php _e( 'User Email' , $this->text_domain ); ?></th>											<th class="manage-column column-columnname num" scope="col"><?php _e( 'Email Client' , $this->text_domain ); ?></th>										</tr>									</tfoot>									<!-- end footer -->																		<!-- TABLE BODY -->									<tbody>										<?php if( $subscribers_list['total'] > 0 ) {												$i = 1;												foreach( $subscribers_list['data'] as $subscriber ) { 													$user_id = $subscriber['leid'];													// setup the email client name and icon													if( !empty( $subscriber['clients'] ) ) {														$user_email_client_name = $subscriber['clients']['name'];														$user_email_client_icon = "<img src='" . $subscriber['clients']['icon_url'] . "' alt=" . $user_email_client_name . " title=" . $user_email_client_name . ">";													} else {														$path = YIKES_MC_URL . "includes/images/na.png";														$user_email_client_icon = "<img width='35' src='" . $path . "' alt='" . __( 'not set' , $this->text_domain ) . "' title='" .  __( 'not set' , $this->text_domain ) . "'>";													}																							?>											<tr class="<?php if( $i % 2 == 0 ) { echo 'alternate'; } ?>">												<th class="check-column" scope="row"><input type="checkbox" /></th>												<td class="column-columnname"><a class="user-email" href="mailto:<?php echo sanitize_email( $subscriber['email'] ); ?>"><?php echo sanitize_email( $subscriber['email'] ); ?></a>													<div class="row-actions">														<span><a href="#"><?php _e( "View Info." , $this->text_domain ); ?></a> |</span>														<?php $url = esc_url( add_query_arg( array( 'action' => 'yikes-easy-mc-unsubscribe-user', 'mailchimp-list' => $list_id , 'nonce' => wp_create_nonce( 'unsubscribe-user-'.$user_id ), 'email_id' => $user_id ) ) ); ?>														<span><a href="<?php echo $url; ?>" onclick="return confirm('<?php _e( "Are you sure you want to unsubscribe" , $this->text_domain ); ?> ' + jQuery(this).parents('.row-actions').parent().find('.user-email').text() + ' <?php _e( 'from this mailing list?' , $this->text_domain ); ?>');" class="yikes-delete-subscriber"><?php _e( "Unsubscribe" , $this->text_domain ); ?></a>																										</div>												</td>												<td class="column-columnname num"><?php echo $user_email_client_icon; ?></td>											</tr>										<?php 													$i++;												}											} else { ?>											<tr class="no-items">												<td class="colspanchange" colspan="3" style="padding:25px 0 25px 25px;"><em><?php _e( 'No one is currently subscribed to this list.' , $this->text_domain ); ?></em></td>											</tr>										<?php } ?>									</tbody>								</table> 								<!-- end table -->																			</div> <!-- .postbox -->												<!-- pagination -->						<div class="tablenav">							<div class="tablenav-pages">								<a class='first-page <?php if( $paged == 0 ) { echo 'disabled'; } ?>' title='<?php _e( "Go to the first page" , $this->text_domain ); ?>' href='<?php echo esc_url( add_query_arg( array( "paged" => 0 ) ) ); ?>'>&laquo;</a>								<a class='prev-page <?php if( $paged == 0 ) { echo 'disabled'; } ?>' title='<?php _e( "Go to the previous page" , $this->text_domain ); ?>' href='<?php echo esc_url( add_query_arg( array( "paged" => intval( $paged - 1 ) ) ) ); ?>'>&lsaquo;</a>								<span class="paging-input"><input class='current-page' title='<?php _e( "Current page" , $this->text_domain ); ?>' type='text' name='paged' value='<?php if( $paged == 0 ) { echo '1'; } else { echo intval( $paged + 1 ); } ?>' size='1' /> <?php _e( 'of', $this->text_domain ); ?> <span class='total-pages'><?php echo $total_pages; ?></span></span>								<a class='next-page <?php if( $paged == intval( $total_pages - 1 ) ) { echo 'disabled'; } ?>' title='<?php _e( "Go to the next page" , $this->text_domain ); ?>' href='<?php echo esc_url( add_query_arg( array( "paged" => intval( $paged + 1 ) ) ) ); ?>'>&rsaquo;</a>								<a class='last-page <?php if( $paged == intval( $total_pages - 1 ) ) { echo 'disabled'; } ?>' title='<?php _e( "Go to the last page" , $this->text_domain ); ?>' href='<?php echo esc_url( add_query_arg( array( "paged" => intval( $total_pages - 1 ) ) ) ); ?>'>&raquo;</a>							</div>						</div>											</div> <!-- .meta-box-sortables .ui-sortable -->									</div> <!-- post-body-content -->								<!-- sidebar -->				<div id="postbox-container-1" class="postbox-container">															<div class="meta-box-sortables">												<div class="postbox yikes-easy-mc-postbox">																									<h3><?php _e( 'List Overview' , $this->text_domain ); ?></h3>														<?php 								// store list rating								$list_rating = $list_data['list_rating'];								if( $list_rating > 0 ) {									$list_rating_explosion = explode( '.' , $list_rating );									$star_array = array();									$x = 1; 									while( $list_rating_explosion[0] >= $x ) {										$star_array[] = '<span class="dashicons dashicons-star-filled list-rating-star"></span>';										$x++;									}									if( $list_rating_explosion[1] == '5' ) {										$star_array[] = '<span class="dashicons dashicons-star-half list-rating-star"></span>';									}								} else {									$star_array = array( 'n/a' );								}							?>							<table class="form-table">								<tr valign="top">									<td scope="row"><label for="tablecell"><strong><?php  _e( 'List Rating' , $this->text_domain ); ?></strong></label></td>									<td><?php echo implode( ' ' , $star_array ); ?></td>								</tr>								<tr valign="top">									<td scope="row"><label for="tablecell"><strong><?php  _e( 'Average Subscribers' , $this->text_domain ); ?></strong></label></td>									<td><?php echo $list_data['stats']['avg_sub_rate']; ?><small> / <?php  _e( 'month' , $this->text_domain ); ?></small></td>								</tr>								<tr valign="top">									<td scope="row"><label for="tablecell"><strong><?php  _e( 'Subscriber Count' , $this->text_domain ); ?></strong></label></td>									<td><?php echo intval( $list_data['stats']['member_count'] ); ?></td>								</tr>								<tr valign="top">									<td scope="row"><label for="tablecell"><strong><?php  _e( 'New Since Last Campaign' , $this->text_domain ); ?></strong></label></td>									<td><?php echo intval( $list_data['stats']['member_count_since_send'] ); ?></td>								</tr>								<tr valign="top">									<td scope="row"><label for="tablecell"><strong><?php  _e( 'Created' , $this->text_domain ); ?></strong></label></td>									<td><?php echo date( get_option('date_format') , strtotime( $list_data['date_created'] ) ); ?></td>								</tr>								<tr valign="top">									<td scope="row"><label for="tablecell"><strong><?php  _e( 'List Fields' , $this->text_domain ); ?></strong></label></td>									<td><?php echo intval( $list_data['stats']['merge_var_count'] + 1 ); // add 1 for our email field.. ?></td>								</tr>								<tr valign="top">									<td scope="row"><label for="tablecell"><strong><?php  _e( 'Short Signup URL' , $this->text_domain ); ?></strong></label></td>									<td><input style="color:#333;" type="text" class="widefat" value="<?php echo esc_url( $list_data['subscribe_url_short'] ); ?>" disabled="disabled"></td>								</tr>								<tr valign="top">									<td scope="row"><label for="tablecell"><strong><?php  _e( 'Default From Email' , $this->text_domain ); ?></strong></label></td>									<td><input style="color:#333;" type="text" class="widefat" value="<?php echo sanitize_email( $list_data['default_from_email'] ); ?>" disabled="disabled"></td>								</tr>								<tr valign="top">									<td scope="row"><label for="tablecell"><strong><?php  _e( 'Default From Name' , $this->text_domain ); ?></strong></label></td>									<td><?php echo $list_data['default_from_name']; ?></td>								</tr>							</table>											</div> <!-- .postbox -->																		<!-- Merge Field Info -->						<div class="postbox yikes-easy-mc-postbox">																									<h3><?php _e( 'Form Fields' , $this->text_domain ); ?></h3>							<?php								if( count( $merge_variables ) >= 1 ) {									?><ul style="padding-left:15px;font-size:14px;"><?php										echo '<li style="text-decoration:underline;margin-bottom:.75em;padding-left:7px;">' . intval( count( $merge_variables ) ) . ' ' . __( "fields" , $this->text_domain ) . '</li>';									foreach( $merge_variables as $merge_variable ) {										echo '<li><span class="dashicons dashicons-arrow-right" style="line-height:.7;font-size:19px;"></span>' . $merge_variable['name'] . '</li>';									}									?></ul><?php								}							?>							<a style="margin: 0 0 10px 15px;" href="#" onclick="return false;" class="button-primary" disabled="disabled" title="<?php _e( 'Edit Fields (Pro Only)' , $this->text_domain ); ?>"><?php _e( 'Edit Fields (pro only)' , $this->text_domain ); ?></a>													</div>												<!-- Interest Group Field Info -->						<div class="postbox yikes-easy-mc-postbox">																					<h3><?php _e( 'Interest Groups Overview' , $this->text_domain ); ?></h3>							<?php								if( isset( $interest_groupings ) && count( $interest_groupings ) >= 1 ) {									?><ul style="padding-left:15px;font-size:14px;"><?php										echo '<li style="text-decoration:underline;margin-bottom:.75em;padding-left:7px;">' . intval( count( $interest_groupings ) ) . ' ' . __( "Merge Variables" , $this->text_domain ) . '</li>';									foreach( $interest_groupings as $interest_group ) {										echo '<li><span class="dashicons dashicons-arrow-right" style="line-height:.7;font-size:19px;"></span>' . $interest_group['name'] . '<span style="padding-left:5px;"></span><small title="' . $interest_group['groups'][0]['subscribers'] . ' ' . __( "subscribers assigned to this group" , $this->text_domain ) . '">(' . $interest_group['groups'][0]['subscribers'] . ')</small></li>';									}									?></ul><?php								} else {									?>									<ul style="padding-left:15px;font-size:14px;">										<li><?php echo $no_interest_groupings . '.'; ?></li>									</ul>									<?php								}							?>							<a style="margin: 0 0 10px 15px;" href="#" onclick="return false;" class="button-primary" disabled="disabled" title="<?php _e( 'Edit Interest Groups (Pro Only)' , $this->text_domain ); ?>"><?php _e( 'Edit Interest Groups (pro only)' , $this->text_domain ); ?></a>													</div>												<!-- Segments Info -->						<div class="postbox yikes-easy-mc-postbox">																					<h3><?php _e( 'Segments Overview' , $this->text_domain ); ?></h3>							<?php								if( isset( $segments['saved'] ) && count( $segments['saved'] ) >= 1 ) {									$i = 1;									?><ul style="padding-left:15px;font-size:14px;"><?php										echo '<li style="text-decoration:underline;margin-bottom:.75em;padding-left:7px;">' . intval( count( $segments['saved'] ) ) . ' ' . __( "Segments" , $this->text_domain ) . '</li>';									foreach( $segments['saved'] as $segment ) {										echo '<li><span class="dashicons dashicons-arrow-right" style="line-height:.7;font-size:19px;"></span>' . $segment['name'] . ' <small><a href="#" onclick="jQuery(this).parent().parent().next().slideToggle();jQuery(this).toggleText();return false;" data-alt-text="' . __( 'hide conditions' , $this->text_domain ) . '">' . __( "view conditions" , $this->text_domain ) . '</a></small></li>';										?><div style="display:none;" class="conditionals"><?php										foreach( $segment['segment_opts']['conditions'] as $condition ) {											echo '<li><small>- ' . __( "condition #" , $this->text_domain ) . $i .  ': ' . __( "If" , $this->text_domain ) . ' ' . $condition['field'] . ' ' . $condition['op'] . ' ' . $condition['value'] . '</small></li>';											$i++;										}										?></div><?php									}									?></ul><?php								} else {									?>									<ul style="padding-left:15px;font-size:14px;">										<li><?php echo $no_segments; ?></li>									</ul>									<?php								}							?>							<!--								<a style="margin: 0 0 10px 15px;" href="#" onclick="return false;" class="button-primary"><?php _e( 'Edit Segments' , $this->text_domain ); ?></a>							-->							<p class="description" style="padding:0 0 10px 15px;"><?php _e( 'To edit this lists segments, head over to' , $this->text_domain ); ?> <a href="http://www.MailChimp.com" target="_blank">MailChimp</a></p>													</div>																	</div> <!-- .meta-box-sortables -->									</div> <!-- #postbox-container-1 .postbox-container -->							</div> <!-- #post-body .metabox-holder .columns-2 -->						<br class="clear">		</div> <!-- #poststuff --></div><!-- JS --><script type="text/javascript">	 /* Toggle Text - Stats/Shortcode (manage-forms.php)*/	jQuery.fn.toggleText = function() {		var altText = this.data("alt-text");		if (altText) {			this.data("alt-text", this.html());			this.html('<small>'+altText+'</small>');		}	};</script>