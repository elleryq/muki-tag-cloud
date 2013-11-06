<?php
/*
Plugin Name: Muki Tag Cloud
Plugin URI: http://www.mukispace.com/wordpress-shape-tag-cloud/
Description: Another wordpress tag cloud plugin based on jQCloud, which is creative, beauty and colorful. Design by <a href="http://www.mukispace.com">Muki</a>,Code by <a href="http://mesak.tw">Mesak</a>
Author: Muki
Author URI: http://www.mukispace.com
Version: 1.0
*/

function MukiTagCloud_init() {
  load_plugin_textdomain( 'muki-tag-cloud', false, dirname( plugin_basename( __FILE__ ) ). '/languages/'  ); 
}
add_action('plugins_loaded', 'MukiTagCloud_init');
add_action('widgets_init', 'MukiTagCloud_load');
function MukiTagCloud_load() {
	register_widget('MukiTagCloud_Widget');
}
class MukiTagCloud_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'MukiTagCloud', // Base ID
			__('Muki Tag Cloud', 'muki-tag-cloud'), // Name
			array( 'description' => __( 'Tag Cloud By Muki & Mesak', 'muki-tag-cloud' ), ) // Args
		);
	}
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$cache = wp_cache_get('widget_muki_tag_cloud', 'widget');

		if (!is_array($cache))
			$cache = array();
		if (isset($cache[$args['widget_id']]))
			return $cache[$args['widget_id']];
		ob_start();
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Tags Cloud') : $instance['title']);
		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];
		echo '<div class="muki_tag_cloud" style="width:'.muki_tag_cloud_get_option('widget_width').';height:'.muki_tag_cloud_get_option('widget_height').';">';
		echo muki_tag_cloud_create();
		echo '</div>';
		echo $args['after_widget'];
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_add('widget_muki_tag_cloud', $cache, 'widget');
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Tag Cloud', 'text_domain' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}

function muki_tag_cloud_action_links($links, $file) {
        static $this_plugin;
        if (!$this_plugin) {
                $this_plugin = plugin_basename(__FILE__);
        }
        if ($file == $this_plugin) {
                $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=muki-tag-cloud/muki-tag-cloud.php">Settings</a>';
                array_unshift($links, $settings_link);
        }
        return $links;
}
add_filter('plugin_action_links', 'muki_tag_cloud_action_links', 10, 2);

function muki_tag_cloud_create(){
	$tags = muki_tag_cloud_get();
	echo wp_generate_tag_cloud($tags['tags'], $args);
}
function muki_tag_cloud_get($args = '')
{
	$tags = get_terms('post_tag', array('minnum' => $minnum, 'maxnum' => $maxnum, 'orderby' => 'count', 'order' => 'DESC')); // Always query top tags
	/*
	echo '<pre>';
	print_r($tags);
	echo '</pre>';
	*/
	$alltags = array();
	$article_order = muki_tag_cloud_get_option('article_order');
	foreach($tags as $k => $tag)
	{
		$tags[$k]->link = get_term_link( intval($tag->term_id), $tag->taxonomy );
		$weight = $article_order == 'random' ? rand(1, 100) : $tag->count;
		$alltags[] = array(
			'text' => $tag->name,
			'link' => urldecode($tag->link),
			'weight' => $weight,
		);
	}
	//echo '<script type="text/javascript">var word_list = ' . json_encode($alltags) .'</script>';
	//muki_tag_cloud_script_content_add('var word_list = ' . json_encode($alltags) );
    //wp_localize_script('muki-jqcloud-script', 'word_list', $alltags );
    return array('tags'=>$tags ,'alltags'=>$alltags );
	//echo wp_generate_tag_cloud($tags, $args); // Here's where those top tags get sorted according to $args
}

function muki_tag_cloud_script_prefix_add() {
	wp_enqueue_script( 'muki-jqcloud-script', plugins_url('/muki-tag-cloud/jqcloud-1.0.4.min.js'), array( 'jquery') );
	$tag = muki_tag_cloud_get();
	wp_localize_script('muki-jqcloud-script', 'word_list', $tag['alltags'] );
	
	//wp_footer_scripts( 'muki-jqcloud-footer-script');
	
	/* Enqueue Styles */
	wp_register_style( 'muki-jqcloud-stylesheet', plugins_url('/muki-tag-cloud/jqcloud.css') );
	wp_enqueue_style( 'muki-jqcloud-stylesheet' );
}
add_action( 'wp_enqueue_scripts', 'muki_tag_cloud_script_prefix_add' );
add_action( 'wp_head','muki_tag_cloud_head_scripts',20);
function muki_tag_cloud_head_scripts() {
?>
<style type="text/css">
<?php
	$muki_tag_cloug_font_small = intval( muki_tag_cloud_get_option('font_small') );
	$muki_tag_cloug_font_large = intval( muki_tag_cloud_get_option('font_large'));
	$muki_tag_cloug_font_step = ($muki_tag_cloug_font_large - $muki_tag_cloug_font_small) / 10;
	$index = 1;
	echo '/*'. "\n";
	echo 'muki_tag_cloug_font_small = ' .$muki_tag_cloug_font_small . "\n";
	echo 'muki_tag_cloug_font_large = ' .$muki_tag_cloug_font_large . "\n";
	echo '*/'. "\n";
	for($size = $muki_tag_cloug_font_small; $size <= $muki_tag_cloug_font_large; $size += $muki_tag_cloug_font_step){
		echo '.muki_tag_cloud.jqcloud span.w'.$index . '{font-size:' . ($size/12) * 100 .'%;}' ."\n";
		$index++;
	}
	
	$muki_tag_cloug_get_color_scheme = muki_tag_cloug_get_color_scheme( muki_tag_cloud_get_option('color_scheme') );
	foreach( $muki_tag_cloug_get_color_scheme as $key => $val){
		echo '.muki_tag_cloud.jqcloud '.$key . '{color:' .$val .';}';
	}
?>
</style>
<?php
}
add_action( 'wp_footer','muki_tag_cloud_footer_scripts',20);
function muki_tag_cloud_footer_scripts() {
?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
    		$('.muki_tag_cloud').empty().jQCloud(word_list);
    	})
	</script>
	<?php
}
function muki_tag_cloug_get_color_scheme( $name = 'fresh'){
	$color_scheme_opt = array(
	'fresh'  => array('a:hover' => '#e94252','span.w10' => '#3aadb9','span.w9' => '#e68d15','span.w8' => '#843014','span.w7' => '#62693d','span.w6' => '#a8a852','span.w5' => '#785430','span.w4' => '#8db9aa','span.w3' => '#cc99cc','span.w2' => '#525a4b','span.w1' => '#9d88d6'),
	'light'  => array('a:hover' => '#eced87','span.w10' => '#fdaeaa','span.w9' => '#f0b48b','span.w8' => '#f0b48b','span.w7' => '#bade83','span.w6' => '#98dee0','span.w5' => '#bade83','span.w4' => '#fdaeaa','span.w3' => '#90aff8','span.w2' => '#98dee0','span.w1' => '#90aff8'),
	'dark'   => array('a:hover' => '#6b51ae','span.w10' => '#6a7a52','span.w9' => '#6b51ae','span.w8' => '#934c74','span.w7' => '#dc001a','span.w6' => '#55a9f2','span.w5' => '#45ce5e','span.w4' => '#cc99cc','span.w3' => '#99b333','span.w2' => '#6a7a52','span.w1' => '#c6560c'),
	'blue'   => array('a:hover' => '#d7f1f9','span.w10' => '#0055a5','span.w9' => '#76c6f0','span.w8' => '#2461aa','span.w7' => '#a6cae1','span.w6' => '#1a4aec','span.w5' => '#2ac3cf','span.w4' => '#2461aa','span.w3' => '#1a4aec','span.w2' => '#86d7d1','span.w1' => '#00d7f8'),
	'red'    => array('a:hover' => '#ffa001','span.w10' => '#d64e12','span.w9' => '#ff660a','span.w8' => '#972f38','span.w7' => '#a11c48','span.w6' => '#c24b37','span.w5' => '#c50747','span.w4' => '#e85a08','span.w3' => '#ff0000','span.w2' => '#ff3333','span.w1' => '#c50747'),
	'green'  => array('a:hover' => '#41532e','span.w10' => '#233a0a','span.w9' => '#4d7423','span.w8' => '#63674c','span.w7' => '#6e783e','span.w6' => '#72c39a','span.w5' => '#99ac3a','span.w4' => '#89bd89','span.w3' => '#68a14a','span.w2' => '#70ca41','span.w1' => '#9eca9e'),
	'gray'   => array('a:hover' => '#ccc','span.w10' => '#2c2c2c','span.w9' => '#3e3d3d','span.w8' => '#646464','span.w7' => '#828e82','span.w6' => '#99aa99','span.w5' => '#a7a7a7','span.w4' => '#b9b9b2','span.w3' => '#c1c1c1','span.w2' => '#000','span.w1' => '#adadad')
	);
	return isset($color_scheme_opt[$name]) ? $color_scheme_opt[$name] : $color_scheme_opt['fresh'];
}
add_action('admin_menu', 'muki_tag_cloud_option');
function muki_tag_cloud_get_option($name = '',$reload = FALSE)
{
    //add options
    $muki_tag_cloud_setting_name = 'muki_tag_cloud_setting';
    static $muki_tag_cloud_setting_load = FALSE;
    static $muki_tag_cloud_setting = array();
    if( $muki_tag_cloud_setting_load === FALSE || $reload)
    {
	    $muki_tag_cloud_setting = array(
	    	'widget_height'  => '400px',
	    	'widget_width'   => '100%',
	    	'article_order'  => 'random', // article , random
	    	'font_small'     => 12,
	    	'font_large'     => 36,
	    	'color_scheme'   => 'fresh'
		);
	    $muki_tag_cloud_setting_db = get_option( $muki_tag_cloud_setting_name );
	    if( is_array($muki_tag_cloud_setting_db) && count($muki_tag_cloud_setting_db) )
	    {
		    foreach($muki_tag_cloud_setting_db as $key => $val )
		    {
		    	$muki_tag_cloud_setting[$key] = $val;
		    }
	    }
	    $muki_tag_cloud_setting_load = TRUE;
    }
    return isset($muki_tag_cloud_setting[$name]) ? $muki_tag_cloud_setting[$name] : $muki_tag_cloud_setting;
}
//adds a new submenu for options
function muki_tag_cloud_option() {
        add_options_page(__('Muki Tag Cloud - User Options','muki-tag-cloud'), 'Muki Tag Cloud', 'activate_plugins', __FILE__, 'muki_tag_cloud_option_detail');
}
//display the actual content of option page.
function muki_tag_cloud_option_detail() {  
    $muki_tag_valid = 'muki_tag_valid';
    $muki_tag_cloud_setting_name = 'muki_tag_cloud_setting';
    $muki_tag_cloud_setting   = muki_tag_cloud_get_option();
    $color_scheme_opt = array(
		"fresh" => 'fresh (default)',
		"light" => 'light',
		"dark"  => 'dark',
		"blue"  => 'single color (blue)',
		"red"   => 'single color (red)',
		"green" => 'single color (green)',
		"gray"  => 'single color (gray)'
    );
    //update options
    if( !empty($_POST[ $muki_tag_cloud_setting_name ]) && check_admin_referer($muki_tag_valid,'check-form') ) {
		update_option( $muki_tag_cloud_setting_name, $_POST[ $muki_tag_cloud_setting_name ] );
    	$muki_tag_cloud_setting   = muki_tag_cloud_get_option(null,TRUE);
        echo '<div class="updated"><p><strong>'.__('Settings saved.', 'muki-tag-cloud').'</strong></p></div>';  
    }
    //delete options
    if( isset($_POST[ $muki_tag_delete ]) && check_admin_referer($muki_tag_valid,'check-form') ) {
        //compatible with version older than FII 2.0
        delete_option( $muki_tag_cloud_setting_name );
        echo '<div class="updated"><p><strong>'.__('Settings deleted.', 'muki-tag-cloud').'</strong></p></div>';  
    }
    echo '<div class="wrap">'."\n".
         '<div id="icon-options-general" class="icon32"><br /></div>'."\n".
         '<h2>'.__('Muki Tag Cloud - User Options','muki-tag-cloud').'</h2>'."\n".
         '<h3>'.__('Updates your settings here', 'muki-tag-cloud').'</h3>';
?>

<form name="faster-insert-option" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($muki_tag_valid, 'check-form'); ?>

    <table width="100%" cellspacing="2" cellpadding="5" class="form-table">

        <tr valign="top">
            <th scope="row"><?php _e("Widget Height", 'muki-tag-cloud' ); ?></th>
            <td><label for="muki_tag_cloud_setting_widget_height"><input type="text" name="muki_tag_cloud_setting[widget_height]" id="muki_tag_cloud_setting_widget_height" value="<?php echo $muki_tag_cloud_setting['widget_height']; ?>" size="3" /><?php _e("px or %; default is 400px", 'muki-tag-cloud' ); ?></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e("Widget Width", 'muki-tag-cloud' ); ?></th>
            <td><label for="muki_tag_cloud_setting_widget_width"><input type="text" name="muki_tag_cloud_setting[widget_width]" id="muki_tag_cloud_setting_widget_width" value="<?php echo $muki_tag_cloud_setting['widget_width']; ?>" size="3" /><?php _e("px or %; default is 100%", 'muki-tag-cloud' ); ?></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e("Tag Cloud order by", 'muki-tag-cloud' ); ?></th>
            <td><label for="muki_tag_cloud_setting_article_order"><input type="radio" name="muki_tag_cloud_setting[article_order]" id="muki_tag_cloud_setting_article_order" <?php if($muki_tag_cloud_setting['article_order'] == 'article') echo 'checked="checked"' ?> value="article" />
        		<?php _e("article", 'muki-tag-cloud' ); ?></label>
        &nbsp;&nbsp;<label for="muki_tag_cloud_setting_random_order">
        <input type="radio" name="muki_tag_cloud_setting[article_order]" id="muki_tag_cloud_setting_random_order" <?php if($muki_tag_cloud_setting['article_order'] == 'random') echo 'checked="checked"' ?> value="random" /> <?php _e("random", 'muki-tag-cloud' ); ?></label></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Font Size", 'muki-tag-cloud' ); ?></th>
            <td>
            	<label for="muki_tag_cloud_setting_font_small"><?php _e("smallest", 'muki-tag-cloud' );?>:&nbsp;<input type="text" name="muki_tag_cloud_setting[font_small]" id="muki_tag_cloud_setting_font_small" value="<?php echo $muki_tag_cloud_setting['font_small']; ?>" size="1" /> <?php _e("px; defaults is 12", 'muki-tag-cloud' ); ?></label><br />
            	<label for="muki_tag_cloud_setting_font_large"><?php _e("largest", 'muki-tag-cloud');?>:&nbsp;<input type="text" name="muki_tag_cloud_setting[font_large]" id="muki_tag_cloud_setting_font_large" value="<?php echo $muki_tag_cloud_setting['font_large']; ?>" size="1" /> <?php _e("px; defaults is 36", 'muki-tag-cloud' ); ?></label>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Color Scheme", 'muki-tag-cloud' ); ?></th>
            <td><label for="muki_tag_cloud_setting_color_scheme">
            	<select id="muki_tag_cloud_setting_color_scheme" name="muki_tag_cloud_setting[color_scheme]" onchange="document.getElementById('preview').src = '../wp-content/plugins/muki-tag-cloud/colorscheme-' + this.value + '.png'">
            <?
				foreach( $color_scheme_opt as $key => $val){
					$selected = ( $key == $muki_tag_cloud_setting['color_scheme'] ) ? ' selected="selected"':'';
					echo  '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
				}
            ?>
            	</select>
            	<br />
            	<img id="preview" src="../wp-content/plugins/muki-tag-cloud/colorscheme-<?php echo $muki_tag_cloud_setting['color_scheme'];?>.png" border="0" />
            </td>
        </tr>
    </table>

<p class="submit">
<input type="submit" name="<?php echo $muki_tag_update; ?>" class="button-primary" value="<?php esc_attr_e('Save Changes', 'muki-tag-cloud' ) ?>" />
<input type="submit" name="<?php echo $muki_tag_delete; ?>" class="button" value="<?php esc_attr_e('Delete Setting', 'muki-tag-cloud' ) ?>" />
</p>

</form>

<?php     
    echo '</div>'."\n";
}
?>