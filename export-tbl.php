<?php
if (!class_exists('Wiloke')) {
    return false;
}

if (isset($_GET['tbl']) && !empty($_GET['tbl'])) {
    global $wpdb;
    $tblName = $wpdb->prefix.$wpdb->_real_escape($_GET['tbl']);
    
    $aResults = $wpdb->get_results(
      "SELECT * FROM {$tblName} ORDER BY ID DESC LIMIT 1000",
            ARRAY_A
    );
    
    if (!empty($aResults) && !is_wp_error($aResults)) {
        ?>
        <div>
            <h3><?php echo $_GET['tbl']; ?></h3>
            <textarea name="" id="" cols="30" rows="10"><?php echo json_encode($aResults); ?></textarea>
        </div>
        <?php
    }
}
?>
<form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="GET">
    <input type="hidden" name="page" value="wiloke-export-tbl">
    <label>
        Export Tbl Name
        <input type="text" name="tbl">
    </label>
    <input type="submit" class="button button-primary" value="Get Data">
</form>
