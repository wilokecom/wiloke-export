<?php
if ( !class_exists('Wiloke') ){
	return false;
}

if ( isset($_GET['term_name']) && !empty($_GET['term_name']) ){
	$termName = $_REQUEST['term_name'];
	$aTerms = get_terms(
		array(
			'taxonomy' => $termName
		)
	);

	if ( !empty($aTerms) && !is_wp_error($aTerms) ){
		$aParentAndChildren = array();
		foreach ($aTerms as $oTerm){
			$aTermChildren = get_term_children( $oTerm->term_id, $termName );
			if ( !empty($aTermChildren) ){
				foreach ($aTermChildren as $childID){
					$oChildDetail = get_term_by( 'id', $childID, $termName );
					if ( !empty($oChildDetail) && !is_wp_error($oChildDetail) ){
						$aParentAndChildren[$oTerm->slug][] = $oChildDetail->slug;
					}
				}
			}
		}
		$aFinalResult[$termName] = $aParentAndChildren;
		?>
		<div>
			<textarea name="" id="" cols="30" rows="10"><?php echo json_encode($aFinalResult); ?></textarea>
		</div>
		<?php
	}
}
?>
<form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="GET">
	<input type="hidden" name="page" value="wiloke-term-parent-and-children">
	<?php
	$args = array(
		'public'   => true,
		'_builtin' => false

	);
	$output = 'names'; // or objects
	$operator = 'and'; // 'and' or 'or'
	$aTermNames = array('listing_location'=>'Listing Location', 'listing_cat'=>'Listing Category');
	?>
	<select name="term_name">
		<?php
		foreach ( $aTermNames  as $key => $name ) {
			?>
			<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($name); ?></option>
			<?php
		}
		?>
	</select>
	<input type="submit" class="button button-primary" value="Get Data">
</form>