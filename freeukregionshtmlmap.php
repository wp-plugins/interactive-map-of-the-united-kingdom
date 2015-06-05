<?php
/*
Plugin Name: Interactive Map of the United Kingdom
Plugin URI: http://www.fla-shop.com
Description: High-quality the United Kingdom map plugin for WordPress. The map depicts regions and features color, landing page and popup customization.
Version: 2.4
Author: Fla-shop.com
Author URI: http://www.fla-shop.com
License: GPLv2 or later
*/

if (isset($_REQUEST['action']) && $_REQUEST['action']=='freeukregions_map_export') { freeukregions_map_export(); }

add_action('admin_menu', 'freeukregions_map_plugin_menu');

function freeukregions_map_plugin_menu() {

    add_menu_page(__('UK Map Settings','freeukregions-html5-map'), __('UK Map Settings','freeukregions-html5-map'), 'manage_options', 'freeukregions-map-plugin-options', 'freeukregions_map_plugin_options' );

    add_submenu_page('freeukregions-map-plugin-options', __('Detailed settings','freeukregions-html5-map'), __('Detailed settings','freeukregions-html5-map'), 'manage_options', 'freeukregions-map-plugin-states', 'freeukregions_map_plugin_states');
    add_submenu_page('freeukregions-map-plugin-options', __('Map Preview','freeukregions-html5-map'), __('Map Preview','freeukregions-html5-map'), 'manage_options', 'freeukregions-map-plugin-view', 'freeukregions_map_plugin_view');

    add_submenu_page('freeukregions-map-plugin-options', __('Maps','freeukregions-html5-map'), __('Maps','freeukregions-html5-map'), 'manage_options', 'freeukregions-map-plugin-maps', 'freeukregions_map_plugin_maps');
}

function freeukregions_map_plugin_options() {
    include('editmainconfig.php');
}

function freeukregions_map_plugin_states() {
    include('editstatesconfig.php');
}

function freeukregions_map_plugin_maps() {
    include('mapslist.php');
}

function freeukregions_map_plugin_view() {
    
    $options = get_site_option('freeukregionshtml5map_options');
    $map_id  = (isset($_REQUEST['map_id'])) ? intval($_REQUEST['map_id']) : array_shift(array_keys($options)) ;
    
?>

    <div style="clear: both"></div>

    <h2>Map Preview</h2>
    
    <script type="text/javascript">
        jQuery(function(){

            jQuery('select[name=map_id]').change(function() {
                location.href='admin.php?page=freeukregions-map-plugin-view&map_id='+jQuery(this).val();
            });
    
        });
    </script>

    <span class="title">Map: </span>
    <select name="map_id" style="width: 185px;">
        <?php foreach($options as $id => $map_data) { ?>
            <option value="<?php echo $id; ?>" <?php echo ($id==$map_id)?'selected':'';?>><?php echo $map_data['name']; ?></option>
        <?php } ?>
    </select>

    <div style="clear: both; height: 30px;"></div>    
    
<?php

    echo '<p>Use shortcode <b>[freeukregionshtml5map id="0"]</b> for install this map</p>';

    echo do_shortcode('<div style="width: 99%">[freeukregionshtml5map id="'.$map_id.'"]</div>');
}

add_action('admin_init','freeukregions_map_plugin_scripts');

function freeukregions_map_plugin_scripts(){
    
    
    
    if ( is_admin() ){

        wp_register_style('jquery-tipsy', plugins_url('/static/css/tipsy.css', __FILE__));
        wp_enqueue_style('jquery-tipsy');
        wp_register_style('freeukregions-html5-mapadm', plugins_url('/static/css/mapadm.css', __FILE__));
        wp_enqueue_style('freeukregions-html5-mapadm');
        wp_enqueue_style('farbtastic');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('farbtastic');
        wp_enqueue_script('tiny_mce');
        wp_register_script('jquery-tipsy', plugins_url('/static/js/jquery.tipsy.js', __FILE__));
        wp_enqueue_script('jquery-tipsy');

    }
    else {

        $options = get_site_option('freeukregionshtml5map_options');
    
        wp_register_style('freeukregions-html5-map-style', plugins_url('/static/css/map.css', __FILE__));
        wp_enqueue_style('freeukregions-html5-map-style');
        wp_register_script('raphael', plugins_url('/static/js/raphael-min.js', __FILE__));
        wp_enqueue_script('raphael');
        
        
        $path = isset($options[0]['data_file']) ? $options[0]['data_file'] : $options[0]['defaultDataFile'];
        wp_register_script('freeukregions-html5-map-js', $path);
        wp_enqueue_script('freeukregions-html5-map-js');
        
        wp_enqueue_script('jquery');

    }
}

add_action('wp_enqueue_scripts', 'freeukregions_map_plugin_scripts_method');

function freeukregions_map_plugin_scripts_method() {
    wp_enqueue_script('jquery');
}


add_shortcode( 'freeukregionshtml5map', 'freeukregions_map_plugin_content' );

function freeukregions_map_plugin_content($atts, $content) {

    $dir               = WP_PLUGIN_URL.'/interactive-map-of-the-united-kingdom/static/';
    $siteURL           = get_site_url();
    $options           = get_site_option('freeukregionshtml5map_options');
    
    if (isset($atts['id'])) {
        $map_id  = intval($atts['id']);
        $options = $options[$map_id];
    } else {
        $map_id  = array_shift(array_keys($options));
        $options = array_shift($options);
    }
    
    $isResponsive      = $options['isResponsive'];
    $stateInfoArea     = $options['statesInfoArea'];
    $respInfo          = $isResponsive ? ' htmlMapResponsive' : '';
    $popupNameColor    = $options['popupNameColor'];
    $popupNameFontSize = $options['popupNameFontSize'].'px';

    $style             = (!empty($options['maxWidth']) && $isResponsive) ? 'max-width:'.intval($options['maxWidth']).'px' : '';
    
    $path_js           = ($options['df_type']==1) ? $options['data_file'] : $options['defaultDataFile'];
    
    $mapInit = "
        <!-- start Fla-shop.com HTML5 Map -->	
        <div class='freeukregionsHtmlMap$stateInfoArea$respInfo' style='$style'>
        <div id='map-container'></div>
            <link href='{$dir}css/map.css' rel='stylesheet'>
            <style>
                body .fm-tooltip {
                    color: $popupNameColor;
                    font-size: $popupNameFontSize;
                }
            </style>
            <script src='{$dir}js/raphael-min.js'></script>
            <script src='{$siteURL}/index.php?freeukregionsmap_js_data=true&map_id=$map_id&r=".rand(11111,99999)."'></script>
            <script src='$path_js'></script>
            <script>
				var map = new FlaMap(map_cfg);
				map.drawOnDomReady('map-container');
            </script>
            <script>
                function freeukregions_map_set_state_text(state) {
                    jQuery('#freeukregionsHtmlMapStateInfo').html('Loading...');
                    jQuery.ajax({
                        type: 'POST',
                        url: '{$siteURL}/index.php?freeukregionsmap_get_state_info='+state+'&map_id=$map_id',
                        success: function(data, textStatus, jqXHR){
                            jQuery('#freeukregionsHtmlMapStateInfo').html(data);
                        },
                        dataType: 'text'
                    });
                }
            </script>
            <div id='freeukregionsHtmlMapStateInfo'></div>
            </div>
            <div style='clear: both'></div>
			<!-- end HTML5 Map -->
    ";
    
    return $mapInit;
}


$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'freeukregions_map_plugin_settings_link' );

function freeukregions_map_plugin_settings_link($links) {
    $settings_link = '<a href="admin.php?page=freeukregions-map-plugin-options">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}


add_action( 'parse_request', 'freeukregions_map_plugin_wp_request' );

function freeukregions_map_plugin_wp_request( $wp ) {
    
    if (isset($_REQUEST['freeukregionsmap_js_data']) or isset($_REQUEST['freeukregionsmap_get_state_info'])) {
        $map_id  = intval($_REQUEST['map_id']);
        $options = get_site_option('freeukregionshtml5map_options');
        $options = $options[$map_id];
    }
    
    $options['map_data'] = htmlspecialchars_decode($options['map_data']);
    
    if( isset($_GET['freeukregionsmap_js_data']) ) {

        header( 'Content-Type: application/javascript' );
       ?>
    
        var	map_cfg = {
        
        <?php  if(!$options['isResponsive']) { ?>
        mapWidth		: <?php echo $options['mapWidth']; ?>,
        mapHeight		: <?php echo $options['mapHeight']; ?>,
        <?php }     else { ?>
			mapWidth		: 0,
			<?php } ?>
        
        shadowWidth		: <?php echo $options['shadowWidth']; ?>,
        shadowOpacity		: <?php echo $options['shadowOpacity']; ?>,
        shadowColor		: "<?php echo $options['shadowColor']; ?>",
        shadowX			: <?php echo $options['shadowX']; ?>,
        shadowY			: <?php echo $options['shadowY']; ?>,

        iPhoneLink		: <?php echo $options['iPhoneLink']; ?>,

        isNewWindow		: <?php echo $options['isNewWindow']; ?>,

        borderColor		: "<?php echo $options['borderColor']; ?>",
        borderColorOver		: "<?php echo $options['borderColorOver']; ?>",

        nameColor		: "<?php echo $options['nameColor']; ?>",
        popupNameColor		: "<?php echo $options['popupNameColor']; ?>",
        nameFontSize		: "<?php echo $options['nameFontSize'].'px'; ?>",
        popupNameFontSize	: "<?php echo $options['popupNameFontSize'].'px'; ?>",
        nameFontWeight		: "<?php echo $options['nameFontWeight']; ?>",

        overDelay		: <?php echo $options['overDelay']; ?>,
        nameStroke		: <?php echo $options['nameStroke']?'true':'false'; ?>,
        nameStrokeColor		: "<?php echo $options['nameStrokeColor']; ?>",
        map_data        : <?php echo $options['map_data']; ?>
		}

        <?php

        exit;
    }

    if(isset($_GET['freeukregionsmap_get_state_info'])) {
        $stateId = (int) $_GET['freeukregionsmap_get_state_info'];

        echo nl2br($options['state_info'][$stateId]);

        exit;
    }
}


function freeukregions_map_plugin_map_defaults($name='New map') {
    
    $initialStatesPath = dirname(__FILE__).'/static/settings_tpl.json';
    
    $defaults = array(
                        'name'              => $name,
                        'map_data'          => file_get_contents($initialStatesPath),
                        'mapWidth'          => 330,
                        'mapHeight'         => 450,
                        'maxWidth'          => 500,
                        'shadowWidth'       => 2,
                        'shadowOpacity'     => 0.3,
                        'shadowColor'       => "black",
                        'shadowX'           => 0,
                        'shadowY'           => 0,
                        'iPhoneLink'        => "true",
                        'isNewWindow'       => "false",
                        'borderColor'       => "#ffffff",
                        'borderColorOver'   => "#ffffff",
                        'nameColor'         => "#ffffff",
                        'popupNameColor'    => "#000000",
                        'nameFontSize'      => "9",
                        'popupNameFontSize' => "20",
                        'nameFontWeight'    => "bold",
                        'overDelay'         => 300,
                        'statesInfoArea'    => "bottom",
                        'isResponsive'      => "1",
                        'nameStroke'        => true,
                        'nameStrokeColor'   => "#000000",
                        'defaultDataFile'   => "http://cdn.html5maps.com/libs/locator/2.2.8/ukregions/map.js",
                    );
    
    for($i = 1; $i <= 14; $i++) {
        $defaults['state_info'][$i] = '';
    }
    
    return $defaults;
}


register_activation_hook( __FILE__, 'freeukregions_map_plugin_activation' );

function freeukregions_map_plugin_activation() {
    
    $options = array(0 => freeukregions_map_plugin_map_defaults());
    
    add_site_option('freeukregionshtml5map_options', $options);
    
}

register_deactivation_hook( __FILE__, 'freeukregions_map_plugin_deactivation' );

function freeukregions_map_plugin_deactivation() {

}

register_uninstall_hook( __FILE__, 'freeukregions_map_plugin_uninstall' );

function freeukregions_map_plugin_uninstall() {
    delete_site_option('freeukregionshtml5map_options');
}

add_filter('widget_text', 'do_shortcode');


function freeukregions_map_export() {
    $maps    = explode(',',sanitize_text_field($_REQUEST['maps']));
    $options = get_site_option('freeukregionshtml5map_options');
    
    foreach($options as $map_id => $option) {
        if (!in_array($map_id,$maps)) {
            unset($options[$map_id]);
        }
    }
    
    if (count($options)>0) {
        $options = json_encode($options);
        $options = htmlspecialchars_decode($options);
        
        header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK');
        header('Content-Type: text/json');
        header('Content-Length: ' . (strlen($options)));
        header('Connection: close');
        header('Content-Disposition: attachment; filename="maps.json";');
        echo $options;
        
        exit();
    }

}
