<?php
if (!class_exists('Wiloke')) {
    return false;
}

if (isset($_GET['taxonomy_target']) && !empty($_GET['taxonomy_target'])) {
    $aTerms = get_terms(
      [
        'taxonomy' => $_GET['taxonomy_target']
      ]
    );
    
    global $wpdb;
    $termsTbl    = $wpdb->terms;
    $termMetaTbl = $wpdb->termmeta;
    
    if (!empty($aTerms) && !is_wp_error($aTerms)) {
        foreach ($aTerms as $oTerm) {
            $oTermOption = $wpdb->get_results(
              $wpdb->prepare(
                "SELECT $termMetaTbl.*, $termsTbl.slug FROM $termMetaTbl LEFT JOIN $termsTbl ON ( $termMetaTbl.term_id = $termsTbl.term_id ) WHERE $termsTbl.slug=%s",
                $oTerm->slug
              )
            );
            
            $aData['data'][$oTerm->slug] = $oTermOption;
        }
        $aData['taxonomy'] = $_GET['taxonomy_target'];
        ?>
        <div>
            <h3><?php echo $_GET['taxonomy_target']; ?></h3>
            <textarea name="" id="" cols="30" rows="10"><?php echo json_encode($aData); ?></textarea>
        </div>
        <?php
    }
}
?>
<form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="GET">
    <input type="hidden" name="page" value="wiloke-taxonomy-options">
    <?php
    $args       = [
      'public'   => true,
      '_builtin' => false
    
    ];
    $output     = 'names'; // or objects
    $operator   = 'and'; // 'and' or 'or'
    $taxonomies = get_taxonomies($args, $output, $operator);
    ?>
    <select name="taxonomy_target">
        <?php
        foreach ($taxonomies as $taxonomy) {
            ?>
            <option value="<?php echo esc_attr($taxonomy); ?>"><?php echo esc_html($taxonomy); ?></option>
            <?php
        }
        ?>
    </select>
    <input type="submit" class="button button-primary" value="Get Data">
</form>
