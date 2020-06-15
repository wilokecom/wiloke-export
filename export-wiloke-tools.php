<?php
use \WilokeListingTools\Framework\Helpers\General as WilokeListingToolsGeneral;
use WilokeListingTools\Framework\Helpers\GetSettings;
if ( !class_exists('Wiloke') ){
	return false;
}

if ( isset($_GET['action']) && !empty($_GET['action']=='export_wiloke_tools') ){
	$aDirectoryTypes = WilokeListingToolsGeneral::getPostTypeKeys(false, false);

	$aDirectoryTypesData = array();
	if ( !empty($aDirectoryTypes) ){
		$aData = array();
		foreach ($aDirectoryTypes as $directoryType){
			$aData['heroSearchFields'] = GetSettings::getOptions(WilokeListingToolsGeneral::getHeroSearchFieldsKey($directoryType));
			$aDirectoryTypesData[$directoryType] = $aData;
		}
	}

	?>
	<div>
		<textarea name="" id="" cols="30" rows="10"><?php echo json_encode($aDirectoryTypesData); ?></textarea>
	</div>
	<?php
}

$adminUrl = admin_url('admin.php');
$adminUrl = add_query_arg(
	array(
		'page' => 'wiloke-export-wiloke-tools'
	),
	$adminUrl
);

?>
<form action="<?php echo esc_url($adminUrl); ?>" method="GET">
	<input type="hidden" name="page" value="wiloke-export-wiloke-tools">
	<input type="hidden" name="action" value="export_wiloke_tools">
	<input type="submit" class="button button-primary" value="Export Wiloke Tools">
</form>