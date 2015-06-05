<?php

$update   = false;
$options  = get_site_option('freeukregionshtml5map_options');

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'new':
            $name      = sanitize_text_field($_REQUEST['name']);
            $options[] = freeukregions_map_plugin_map_defaults($name);
            $update    = true;
            break;
    }
}

if ($update) update_site_option('freeukregionshtml5map_options',$options);

class Map_List_Table extends WP_List_Table {

    public function prepare_items()
    {
        $columns  = $this->get_columns();
        $hidden   = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data     = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
    
    public function get_columns()
    {
        $columns = array(
            'checkbox'  => '<input type="checkbox" class="maps_toggle" autocomplete="off" />',
            'name'      => __( 'Name', 'freeukregions-html5-map' ),
            'shortcode' => __( 'ShortCode', 'freeukregions-html5-map' ),
            'edit'      => __( 'Edit', 'freeukregions-html5-map' ),
        );

        return $columns;
    }
    
    public function get_hidden_columns()
    {
        return array();
    }
    
    public function get_sortable_columns()
    {
        return array('name' => array('name', false));
    }
    
    private function table_data()
    {
        
        $data      = array();
        $options   = get_site_option('freeukregionshtml5map_options');
        
        foreach ($options as $map_id => $map_data) {
            $data[] = array(
                            'id'        => $map_id,
                            'name'      => $map_data['name'],
                            'shortcode' => '[freeukregionshtml5map id="'.$map_id.'"]',
                            'edit'      => '<a href="admin.php?page=freeukregions-map-plugin-options&map_id='.$map_id.'">'.__( 'Map settings', 'freeukregions-html5-map' ).'</a><br />
                                            <a href="admin.php?page=freeukregions-map-plugin-states&map_id='.$map_id.'">'.__( 'Map detailed settings', 'freeukregions-html5-map' ).'</a><br />
                                            <a href="admin.php?page=freeukregions-map-plugin-view&map_id='.$map_id.'">'.__( 'Preview', 'freeukregions-html5-map' ).'</a><br /><br />
                                            ',
                            );
        }
        
        return $data;
    }
    
    public function column_default( $item, $column_name )
    {
        
        switch( $column_name ) {
            case 'checkbox':
                echo '&nbsp;<input type="checkbox" value="'.$item['id'].'" class="map_checkbox" autocomplete="off" />';
                break;
            case 'name':
            case 'shortcode':
            case 'edit':
                return $item[ $column_name ];
        }
    }
    
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'name';
        $order   = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
    
}


$listtable = new Map_List_Table();
$listtable->prepare_items();

?>

    <div class="wrap freeukregions-html5-map full">
        <div id="icon-users" class="icon32"></div>
        <h2><?php echo __( 'HTML5 Maps', 'freeukregions-html5-map' ); ?></h2>
        
        <div class="left-block">
            <?php $listtable->display(); ?>
            
            <form name="action_form" action="" method="POST" enctype="multipart/form-data" class="ukregions-html5-map full">
                <input type="hidden" name="action" value="new" />
                <input type="hidden" name="maps" value="" />
                
                <fieldset>
                    <legend>Map Settings</legend>
                    <span>New map name:</span>
                    <input type="text" name="name" value="New map" readonly />
                    <input type="submit" class="button button-primary" value="<?php echo __( 'Add new map', 'freeukregions-html5-map' ); ?>" disabled />
                
                    <p>
                        The free plugin allows you to create only one map. To create multiple maps you can upgrade to <a href="http://www.fla-shop.com" rel="nofollow">Premium version</a> 
                    </p>
                
                </fieldset>
                
                <fieldset>
                    <legend>Export/import</legend>   
                    <p><?php echo __( 'To export please select a checkbox of one or more maps, and press Export button', 'freeukregions-html5-map' ); ?></p>
                    <input type="button" class="button button-secondary export" value="<?php echo __( 'Export', 'freeukregions-html5-map' ); ?>" />
                    <input type="button" class="button button-secondary import" value="<?php echo __( 'Import', 'freeukregions-html5-map' ); ?>" disabled />
                
                    <p>
                        The Import function is only available in <a href="http://www.fla-shop.com" rel="nofollow">Premium version</a> 
                    </p>
                
                </fieldset>
                
            </form>
            
        </div>
        
        <div class="banner">
            &nbsp;
        </div>
        
        <div class="clear"></div>
        
    </div>
    
    
    <script type="text/javascript">
        jQuery(document).ready(function() {
            
            jQuery('a.delete').click(function() {
                if (confirm('<?php echo __( 'Remove the map?\nAttention! All settings for the map will be deleted permanently!', 'freeukregions-html5-map' ); ?>')) {
                    return true;
                } else {
                    return false;
                }
            });
            
            jQuery('.maps_toggle').click(function() {
                jQuery('.map_checkbox,.maps_toggle').not(jQuery(this)).each(function() {
                    jQuery(this).prop('checked', !(jQuery(this).is(':checked')));
                });
            });
            
            jQuery('input.export').click(function() {
                jQuery('input[name=action]').val('freeukregions_map_export');
                
                var maps = '';
                jQuery('.map_checkbox:checked').each(function() {
                    if (maps!='') maps+=',';
                    maps+=jQuery(this).val();
                });
                
                jQuery('input[name=maps]').val(maps);
                
                jQuery('form[name=action_form]').submit();
                return false; 
            });
            

        });
    </script>
    
<?php

?>