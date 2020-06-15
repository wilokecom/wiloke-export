<?php
	if ( !current_user_can('export') )
	{
		wp_die(__('You do not have sufficient permissions to export the content of this site.'));
	}


	global $wpdb, $post_type;
	$title = __('Export');

	// If the 'download' URL parameter is set, a WXR export file is baked and returned.
?>
	<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>

	<p><?php _e('When you click the button below WordPress will create an XML file for you to save to your computer.'); ?></p>
	<p><?php _e('This format, which we call WordPress eXtended RSS or WXR, will contain your posts, pages, comments, custom fields, categories, and tags.'); ?></p>
	<p><?php _e('Once you&#8217;ve saved the download file, you can use the Import function in another WordPress installation to import the content from this site.'); ?></p>

	<h2><?php _e( 'Choose what to export' ); ?></h2>
	<form method="get" id="wiloke-export-filters">
		<fieldset>
			<legend class="screen-reader-text"><?php _e( 'Content to export' ); ?></legend>
<!--			<input type="hidden" name="page" value="wiloke-export" />-->
			<input type="hidden" name="wiloke_download" value="true" />
			<p><label><input type="radio" name="content" value="all" checked="checked" aria-describedby="all-content-desc" /> <?php _e( 'All content' ); ?></label></p>
			<p class="description" id="all-content-desc"><?php _e( 'This will contain all of your posts, pages, comments, custom fields, terms, navigation menus, and custom posts.' ); ?></p>

			<p><label><input type="radio" name="content" value="posts" /> <?php _e( 'Posts' ); ?></label></p>

			<ul id="post-filters" class="export-filters">
				<li>
					<label><span class="label-responsive"><?php _e( 'Categories:' ); ?></span>
					<?php wp_dropdown_categories( array( 'show_option_all' => __('All') ) ); ?>
					</label>
				</li>
				<li>
					<label><span class="label-responsive"><?php _e( 'Authors:' ); ?></span>
					<?php
					$authors = $wpdb->get_col( "SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE post_type = 'post'" );
					wp_dropdown_users( array(
						'include' => $authors,
						'name' => 'post_author',
						'multi' => true,
						'show_option_all' => __( 'All' ),
						'show' => 'display_name_with_login',
					) ); ?>
					</label>
				</li>
				<li>
					<fieldset>
					<legend class="screen-reader-text"><?php _e( 'Date range:' ); ?></legend>
					<label for="post-start-date" class="label-responsive"><?php _e( 'Start date:' ); ?></label>
					<select name="post_start_date" id="post-start-date">
						<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
						<?php export_date_options(); ?>
					</select>
					<label for="post-end-date" class="label-responsive"><?php _e( 'End date:' ); ?></label>
					<select name="post_end_date" id="post-end-date">
						<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
						<?php export_date_options(); ?>
					</select>
					</fieldset>
				</li>
				<li>
					<label for="post-status" class="label-responsive"><?php _e( 'Status:' ); ?></label>
					<select name="post_status" id="post-status">
						<option value="0"><?php _e( 'All' ); ?></option>
						<?php $post_stati = get_post_stati( array( 'internal' => false ), 'objects' );
						foreach ( $post_stati as $status ) : ?>
						<option value="<?php echo esc_attr( $status->name ); ?>"><?php echo esc_html( $status->label ); ?></option>
						<?php endforeach; ?>
					</select>
				</li>
			</ul>

			<p><label><input type="radio" name="content" value="pages" /> <?php _e( 'Pages' ); ?></label></p>
			<ul id="page-filters" class="export-filters">
				<li>
					<label><span class="label-responsive"><?php _e( 'Authors:' ); ?></span>
					<?php
					$authors = $wpdb->get_col( "SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE post_type = 'page'" );
					wp_dropdown_users( array(
						'include' => $authors,
						'name' => 'page_author',
						'multi' => true,
						'show_option_all' => __( 'All' ),
						'show' => 'display_name_with_login',
					) ); ?>
					</label>
				</li>
				<li>
					<fieldset>
					<legend class="screen-reader-text"><?php _e( 'Date range:' ); ?></legend>
					<label for="page-start-date" class="label-responsive"><?php _e( 'Start date:' ); ?></label>
					<select name="page_start_date" id="page-start-date">
						<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
						<?php export_date_options( 'page' ); ?>
					</select>
					<label for="page-end-date" class="label-responsive"><?php _e( 'End date:' ); ?></label>
					<select name="page_end_date" id="page-end-date">
						<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
						<?php export_date_options( 'page' ); ?>
					</select>
					</fieldset>
				</li>
				<li>
					<label for="page-status" class="label-responsive"><?php _e( 'Status:' ); ?></label>
					<select name="page_status" id="page-status">
						<option value="0"><?php _e( 'All' ); ?></option>
						<?php foreach ( $post_stati as $status ) : ?>
						<option value="<?php echo esc_attr( $status->name ); ?>"><?php echo esc_html( $status->label ); ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
				<label for="specify-page" class="label-responsive"><?php _e( 'Specify Page:' ); ?></label>
				<select name="specify_page" id="specify-page">
					<option value="0"><?php _e( 'All' ); ?></option>
					<?php wiloke_export_get_posts('page'); ?>
				</select>
			</li>
			</ul>

			<p><label><input type="radio" name="content" value="taxonomy" /> <?php _e( 'Taxonomy' ); ?></label></p>
			<ul id="taxonomy-filters" class="export-filters">
				<?php
					wiloke_export_get_taxonomies();
				?>
			</ul>

			<?php foreach ( get_post_types( array( '_builtin' => false, 'can_export' => true ), 'objects' ) as $post_type ) : ?>
			<p><label><input type="radio" name="content" value="<?php echo esc_attr( $post_type->name ); ?>" /> <?php echo esc_html( $post_type->label ); ?></label></p>
			<?php endforeach; ?>

			<p><label><input type="radio" name="content" value="attachment" /> <?php _e( 'Media' ); ?></label></p>
			<ul id="attachment-filters" class="export-filters">
				<li>
					<fieldset>
					<legend class="screen-reader-text"><?php _e( 'Date range:' ); ?></legend>
					<label for="attachment-start-date" class="label-responsive"><?php _e( 'Start date:' ); ?></label>
					<select name="attachment_start_date" id="attachment-start-date">
						<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
						<?php export_date_options( 'attachment' ); ?>
					</select>
					<label for="attachment-end-date" class="label-responsive"><?php _e( 'End date:' ); ?></label>
					<select name="attachment_end_date" id="attachment-end-date">
						<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
						<?php export_date_options( 'attachment' ); ?>
					</select>
					</fieldset>
				</li>
			</ul>

            <p><label><input type="radio" name="content" value="nav_menu_item" /> <?php _e( 'Nav Menu Item' ); ?></label></p>
            <p><label><input type="radio" name="content" value="nav_menu_tax" /> <?php _e( 'Nav Menu Taxonomy' ); ?></label></p>
			</fieldset>
			<?php
			/**
			 * Fires at the end of the export filters form.
			 *
			 * @since 3.5.0
			 */
			do_action( 'export_filters' );
			?>

		<?php submit_button( __('Download Export File') ); ?>
	</form>
</div>
