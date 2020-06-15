<?php
/*
 * Plugin Name: Wiloke Advanced Export
 * Author: Wiloke
 * Author URI: http://wiloke.com
 * Plugin URI: http://wiloke.com
 * Version: 1.0
 * Description: It's better that WordPress Importer
 */

/**
 * WordPress Export Administration Screen
 *
 * @package WordPress
 * @subpackage Administration
 */

/** Load WordPress Bootstrap */

//require_once( ABSPATH . 'wp-admin/admin.php' );

add_action('admin_enqueue_scripts', 'wiloke_export_admin_scripts');
function wiloke_export_admin_scripts($page)
{
    if ( $page === 'toplevel_page_wiloke-export' )
    {
        wp_enqueue_style('semantic', plugin_dir_url(__FILE__) . 'vendor/semantic/semantic.css');
        wp_enqueue_style('wiloke_export_style', plugin_dir_url(__FILE__) . 'source/css/style.css');
    }
}

function wiloke_get_media_ids_in_shortcodes($content)
{
    preg_match_all('/\[gallery(?:.+)(?:ids=["\'])([^"\']*)/', $content, $aMatch);

    if ( isset($aMatch[1]) && !empty($aMatch[1]) )
    {
        return $aMatch[1][0];
    }

    return null;
}


/** Load WordPress export API */
require_once( plugin_dir_path(__FILE__) . 'wiloke-handle-export.php' );

add_action('admin_init', 'wiloke_create_xml_file');
function wiloke_create_xml_file()
{
    if ( isset($_GET['wiloke_download']) && !empty($_GET['wiloke_download']) )
    {
        $args = array();

        if ( ! isset( $_GET['content'] ) || 'all' == $_GET['content'] )
        {
            $args['content'] = 'all';
        }elseif( 'posts' == $_GET['content'] )
        {
            $args['content'] = 'post';

            if ( $_GET['cat'] )
            {
                $args['category'] = (int) $_GET['cat'];
            }

            if ( $_GET['post_author'] )
            {
                $args['author'] = (int) $_GET['post_author'];
            }

            if ( $_GET['post_start_date'] || $_GET['post_end_date'] )
            {
                $args['start_date'] = $_POST['post_start_date'];
                $args['end_date'] = $_POST['post_end_date'];
            }

            if ( $_GET['post_status'] )
            {
                $args['status'] = $_GET['post_status'];
            }

        }elseif( 'pages' == $_GET['content'] )
        {
            $args['content'] = 'page';

            if ( $_GET['page_author'] )
            {
                $args['author'] = (int) $_GET['page_author'];
            }

            if ( $_GET['page_start_date'] || $_GET['page_end_date'] )
            {
                $args['start_date'] = $_GET['page_start_date'];
                $args['end_date'] = $_GET['page_end_date'];
            }

            if ( $_GET['page_status'] )
            {
                $args['status'] = $_GET['page_status'];
            }

            if ( $_GET['specify_page'] )
            {
                $args['specify_page'] = $_GET['specify_page'];
            }

        }elseif( 'attachment' == $_GET['content'] )
        {
            $args['content'] = 'attachment';

            if ( $_GET['attachment_start_date'] || $_GET['attachment_end_date'] )
            {
                $args['start_date'] = $_GET['attachment_start_date'];
                $args['end_date'] = $_GET['attachment_end_date'];
            }
        }elseif('taxonomy' == $_GET['content']){
            $args['content'] = 'taxonomy';
	        $args['terms'] = $_GET['terms'];
        }else{
            $args['content'] = $_GET['content'];
            if(isset($_GET['navigation_taxonomy'])){
                $args['navigation_taxonomy'] = $_GET['navigation_taxonomy'];
            }
        }

        /**
         * Filter the export args.
         *
         * @since 3.5.0
         *
         * @param array $args The arguments to send to the exporter.
         */
        $args = apply_filters( 'export_args', $args );

        wiloke_export_wp( $args );
        die();
    }
}

/**
 * Display JavaScript on the page.
 *
 * @since 3.5.0
 */
function wiloke_export_add_js() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            var form = $('#wiloke-export-filters'),
                filters = form.find('.export-filters');
            filters.hide();
            form.find('input[name="content"]:radio').change(function() {
                filters.slideUp('fast');

                switch ( $(this).val() ) {
                    case 'attachment': $('#attachment-filters').slideDown(); break;
                    case 'posts': $('#post-filters').slideDown(); break;
                    case 'pages': $('#page-filters').slideDown(); break;
                    case 'taxonomy': $("#taxonomy-filters").slideDown(); break;
                }
            });
        });
    </script>
    <?php
}
add_action( 'admin_head', 'wiloke_export_add_js' );

/**
 * Create the date options fields for exporting a given post type.
 *
 * @global wpdb      $wpdb      WordPress database abstraction object.
 * @global WP_Locale $wp_locale Date and Time Locale object.
 *
 * @since 3.1.0
 *
 * @param string $post_type The post type. Default 'post'.
 */

if ( !function_exists('export_date_options') )
{
    function export_date_options( $post_type = 'post' )
    {
        global $wpdb, $wp_locale;

        $months = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
            FROM $wpdb->posts
            WHERE post_type = %s AND post_status != 'auto-draft'
            ORDER BY post_date DESC
        ", $post_type ) );

        $month_count = count( $months );
        if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
            return;

        foreach ( $months as $date ) {
            if ( 0 == $date->year )
                continue;

            $month = zeroise( $date->month, 2 );
            echo '<option value="' . $date->year . '-' . $month . '">' . $wp_locale->get_month( $month ) . ' ' . $date->year . '</option>';
        }
    }
}

if ( !function_exists('wiloke_export_menu') )
{
    function wiloke_export_menu()
    {
        add_menu_page('Wiloke Export', 'Wiloke Export', 'edit_theme_options', 'wiloke-export', 'wiloke_export_zone');
        add_submenu_page('wiloke-export', 'Taxonomy Options', 'Taxonomy Options', 'edit_theme_options', 'wiloke-taxonomy-options', 'wiloke_export_taxonomy_options');
        add_submenu_page('wiloke-export', 'Wilcity Options', 'Wilcity Options', 'edit_theme_options', 'wiloke-wilcity-options', 'wiloke_export_wilcity_options');
        add_submenu_page('wiloke-export', 'Parent And Children', 'Parent And Children', 'edit_theme_options', 'wiloke-term-parent-and-children', 'wiloke_export_taxonomy_parent_and_children');
        add_submenu_page('wiloke-export', 'Export Wiloke Tools', 'Export Wiloke Tools', 'edit_theme_options', 'wiloke-export-wiloke-tools', 'wilokeExportListingTools');
        add_submenu_page('wiloke-export', 'Export Tbl', 'Export Tbl', 'edit_theme_options', 'wiloke-export-tbl', 'wilokeExportTbl');
    }
    
    function wilokeExportTbl() {
        include plugin_dir_path(__FILE__) . 'export-tbl.php';
    }

    function wilokeExportListingTools(){
	    include plugin_dir_path(__FILE__) . 'export-wiloke-tools.php';
    }

    function wiloke_export_taxonomy_options(){
	    include plugin_dir_path(__FILE__) . 'taxonomy-export.php';
    }

    function wiloke_export_wilcity_options(){
	    include plugin_dir_path(__FILE__) . 'wilcity-options.php';
    }

	function wiloke_export_taxonomy_parent_and_children(){
		include plugin_dir_path(__FILE__) . 'taxonomy-parent-and-children.php';
	}

    function wiloke_export_zone() {
        include plugin_dir_path(__FILE__) . 'export.php';
    }
    add_action('admin_menu', 'wiloke_export_menu');
}

function wiloke_export_get_posts($postType, $atts=array())
{
    $args = array(
        'post_type'      => $postType,
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    );

    if ( !empty($atts) )
    {
        $args = wp_parse_args($args, $atts);
    }

    $query = new WP_Query($args);

    if ( $query->have_posts() ) : while($query->have_posts()) : $query->the_post();
        ?>
        <option value="<?php echo $query->post->ID ?>"><?php echo $query->post->post_title; ?></option>
        <?php
    endwhile; endif; wp_reset_postdata();
}

function wiloke_export_get_taxonomies()
{
    $aTaxonomies = get_taxonomies();
    unset($aTaxonomies['post_tag']);
    unset($aTaxonomies['nav_menu']);
    unset($aTaxonomies['link_category']);
    unset($aTaxonomies['post_format']);
    unset($aTaxonomies['post_format']);

    if ( !empty($aTaxonomies) && !is_wp_error($aTaxonomies) )
    {
        ?>
        <?php foreach ( $aTaxonomies as $taxonomy ) : ?>
        <li>
            <input type="radio" name="taxonomy" id="wiloke-export-<?php echo $taxonomy ?>" value="<?php echo $taxonomy; ?>" />
            <label for="wiloke-export-<?php echo $taxonomy ?>"><span class="label-responsive"><?php echo $taxonomy; ?></span></label>
            <?php wiloke_export_get_terms($taxonomy); ?>
        </li>
    <?php endforeach; ?>
        <?php
    }else{
        ?>
        <li>There are no taxonomies</li>
        <?php
    }
}

function wiloke_export_get_terms($taxonomy)
{
    $oTerms = get_terms($taxonomy);

    if ( !empty($oTerms) && !is_wp_error($oTerms) )
    {
        foreach ( $oTerms as $oTerm )
        {
            if ( !empty($oTerm->parent) )
            {
                $aChildrend[$oTerm->parent][$oTerm->term_id] = $oTerm;
            }else{
                $osTerms[$oTerm->term_id] = $oTerm;
            }
        }

        ?>
        <ul class="wiloke-group-of-terms">
            <?php
                if ( isset($osTerms) ) :
                    foreach ( $osTerms as $oTerm  ) :
            ?>
            <li>
                    <input id="wiloke-export-<?php echo $oTerm->term_id ?>" value="<?php echo $oTerm->term_id; ?>" name="terms[]" type="checkbox" />
                    <label for="wiloke-export-<?php echo $oTerm->term_id ?>"><span class="label-responsive"><?php echo $oTerm->name; ?></span></label>

                    <?php if ( isset($aChildrend[$oTerm->term_id]) ) : ?>
                            <ul class="is-child">
                                <?php foreach ($aChildrend[$oTerm->term_id] as $oTermChild) : ?>
                                <li>
                                    <input id="wiloke-export-<?php echo $oTermChild->term_id ?>" value="<?php echo $oTermChild->term_id; ?>" name="terms[]" type="checkbox" />
                                    <label for="wiloke-export-<?php echo $oTermChild->term_id ?>"><span class="label-responsive"><?php echo $oTermChild->name; ?></span></label>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                    <?php
                        endif;
                   endforeach;
                endif;
            ?>
            </li>
        </ul>
        <?php
    }
}

//add_action('admin_init', 'test');
function test()
{
    $aTaxonomies = get_taxonomies();
    echo '<pre>';
    var_dump($aTaxonomies);
    echo '</pre>';
    die();
}
