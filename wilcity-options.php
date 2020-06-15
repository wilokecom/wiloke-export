<?php
if ( !class_exists('Wiloke') ){
	return false;
}

if ( isset($_GET['prefix']) && !empty($_GET['prefix']) ){
	global $wpdb;
	$wpoption = $wpdb->options;

	$aOptions = $wpdb->get_results("SELECT * FROM $wpoption WHERE option_name LIKE '%wilcity_%'");

	$aData = array();
	if ( !empty($aOptions) && !is_wp_error($aOptions) ){
		foreach ($aOptions as $oOption){
			$aData[$oOption->option_name] = $oOption->option_value;
		}
		?>
		<div>
			<h3>Wilcity Options</h3>
			<textarea name="" id="" cols="30" rows="10"><?php echo json_encode($aData); ?></textarea>
		</div>
		<?php
	}
}
?>
<form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="GET">
	<input type="hidden" name="page" value="wiloke-wilcity-options">
	<input name="prefix" placeholder="prefix" value="wilcity_">
	<input type="submit" class="button button-primary" value="Get Data">
</form>