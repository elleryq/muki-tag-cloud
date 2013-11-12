<?php
/* Widget */
class MukiTagCloud_Widget extends WP_Widget {

	function __construct() {
/*
		parent::__construct(
			'MukiTagCloud', // Base ID
			__('Muki Tag Cloud', MUKI_TG_NAME), // Name
			array( 'description' =>,'width' => 420, 'height' => 510)
			// Args
		);
		*/

		$widget_ops = array('classname' => 'MukiTagCloud', 'description' =>  __( 'Tag Cloud By Muki & Mesak', MUKI_TG_NAME) );
		/* Widget control settings. */
		$control_ops = array('width' => 460, 'height' => 600);
		/* Create the widget. */
		parent::__construct('MukiTagCloud', __('Muki Tag Cloud', MUKI_TG_NAME), $widget_ops, $control_ops);
		//$this->alt_option_name = 'widget_ctc';
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
		
		if (isset($cache[$args['widget_id'].'_html']))
			return $cache[$args['widget_id'].'_html'];
		$defaults = muki_tag_cloud_get_option();
		$instance = wp_parse_args($instance, $defaults);
		ob_start();
		extract($args);
		extract($instance);
		$title = apply_filters('widget_title', empty($instance['title']) ? __( 'Tag Cloud', MUKI_TG_NAME ) : $instance['title']);
		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];
		echo '<div class="muki_tag_cloud '.$color_scheme.'" id="muki_tag_cloud_'.$args['widget_id'].'" style="width:'.$widget_width.';height:'.$widget_height.';">';
		//echo 
		//$tags_args = array('unit'=>"px;' rel='nofollow' '");
		
		$data = $this->get_tags($instance);
		//var_dump($this);
		$tags_html = wp_generate_tag_cloud($data['tags']);

		if($usenofollow)
	 		echo str_replace(' href='," rel='nofollow' href=",$tags_html);
	 	else
	 		echo $tags_html;
		echo '</div>';
		echo $args['after_widget'];
		$cache[$args['widget_id'].'_html'] = ob_get_flush();
		$cache[$args['widget_id'].'_data'] = $data['word_list'];
		//wp_cache_add('widget_muki_tag_cloud', $cache, 'widget');
		wp_cache_set('widget_muki_tag_cloud', $cache, 'widget');
	}
	public function getData($id){
		$cache = wp_cache_get('widget_muki_tag_cloud', 'widget');
		if (!is_array($cache))
			return array();
		if (isset($cache[$id.'_data']))
			return $cache[$id.'_data'];
	}
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$defaults = muki_tag_cloud_get_option();
		if ( empty($instance) )
			$instance = $defaults;
		
		$instance = wp_parse_args($instance, $defaults);
		$instance['title'] = isset($instance['title']) ? $instance['title'] :  _e( 'Tag Cloud', MUKI_TG_NAME );
		extract( $instance );
		?>
		
		<table width="100%">
			<tr>
        		<td colspan="2">
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title' , MUKI_TG_NAME); ?><?php _e( ':', MUKI_TG_NAME); ?></label>
			     </td>
			</tr>
			<tr>
        		<td colspan="2">
        	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        		</td>
        	</tr>
			<tr>
        		<td colspan="2">
					<p><input type="checkbox" name="<?php echo $this->get_field_name( 'usenofollow' ); ?>" id="<?php echo $this->get_field_id( 'usenofollow' ); ?>" <?php if($usenofollow) echo 'checked="checked"' ?> value="1" />
					<label for="<?php echo $this->get_field_id( 'usenofollow' ); ?>"><?php _e( 'Use nofollow' , MUKI_TG_NAME); ?></label>
			     </td>
			</tr>
			<tr>
        		<td colspan="2">
					<p><input type="checkbox" name="<?php echo $this->get_field_name( 'article_order' ); ?>" id="<?php echo $this->get_field_id( 'article_order' ); ?>" <?php if($article_order == 'usenofollow') echo 'checked="checked"' ?> value="random" />
					<label for="<?php echo $this->get_field_id( 'article_order' ); ?>"><?php _e("Tag Cloud Random Order", MUKI_TG_NAME ); ?></label>
			     </td>
			</tr>
			<tr>
				<td>
		<p><label for="<?php echo $this->get_field_id( 'widget_width' ); ?>"><?php _e( 'Widget Width' , MUKI_TG_NAME); ?><?php _e( ':', MUKI_TG_NAME ); ?></label>
        	<input class="widefat" id="<?php echo $this->get_field_id( 'widget_width' ); ?>" name="<?php echo $this->get_field_name( 'widget_width' ); ?>" type="text" value="<?php echo esc_attr( $widget_width ); ?>" /></p>
        		</td>
        		<td>
		<p><label for="<?php echo $this->get_field_id( 'widget_height' ); ?>"><?php _e( 'Widget Height' , MUKI_TG_NAME); ?><?php _e( ':', MUKI_TG_NAME ); ?></label>
        	<input class="widefat" id="<?php echo $this->get_field_id( 'widget_height' ); ?>" name="<?php echo $this->get_field_name( 'widget_height' ); ?>" type="text" value="<?php echo esc_attr( $widget_height ); ?>" /></p>
        		</td>
        	</tr>
			<tr>
				<td>
		<p><label for="<?php echo $this->get_field_id( 'minnum' ); ?>"><?php _e( 'Min Num' , MUKI_TG_NAME); ?><?php _e( ':', MUKI_TG_NAME ); ?></label>
        	<input class="widefat" id="<?php echo $this->get_field_id( 'minnum' ); ?>" name="<?php echo $this->get_field_name( 'minnum' ); ?>" type="text" value="<?php echo esc_attr( $minnum ); ?>" /></p>
        		</td>
        		<td>
		<p><label for="<?php echo $this->get_field_id( 'maxnum' ); ?>"><?php _e( 'Max Num' , MUKI_TG_NAME); ?><?php _e( ':', MUKI_TG_NAME ); ?></label>
        	<input class="widefat" id="<?php echo $this->get_field_id( 'maxnum' ); ?>" name="<?php echo $this->get_field_name( 'maxnum' ); ?>" type="text" value="<?php echo esc_attr( $maxnum ); ?>" /></p>
        		</td>
        	</tr>
        </table>
		<p><label for="<?php echo $this->get_field_id( 'color_scheme' ); ?>"><?php _e("Color Scheme", MUKI_TG_NAME ); ?><?php _e( ':', MUKI_TG_NAME ); ?></label>
            <select id="<?php echo $this->get_field_id( 'color_scheme' ); ?>" name="<?php echo $this->get_field_name( 'color_scheme' ); ?>" onchange="document.getElementById('preview-<?php echo $this->get_field_id( 'color_scheme' ); ?>').src = '../wp-content/plugins/muki-tag-cloud/colorscheme-' + this.value + '.png'">
            <?php
				foreach( array(
		"fresh" => 'fresh (default)',
		"light" => 'light',
		"dark"  => 'dark',
		"blue"  => 'single color (blue)',
		"red"   => 'single color (red)',
		"green" => 'single color (green)',
		"gray"  => 'single color (gray)'
    ) as $key => $val){
					$selected = ( $key == $color_scheme ) ? ' selected="selected"':'';
					echo  '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
				}
            ?>
            </select></p>
            <p><img id="preview-<?php echo $this->get_field_id( 'color_scheme' ); ?>" src="../wp-content/plugins/muki-tag-cloud/colorscheme-<?php echo $color_scheme;?>.png" border="0" width="460" /></p>
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
		$instance['article_order'] = ( ! empty( $new_instance['article_order'] ) ) ? $new_instance['article_order']  : 'article';
		$instance['widget_width'] = ( ! empty( $new_instance['widget_width'] ) ) ? $new_instance['widget_width']  : '100%';
		$instance['widget_height'] = ( ! empty( $new_instance['widget_height'] ) ) ? $new_instance['widget_height']  : '300px';
		$instance['minnum'] = ( ! empty( $new_instance['minnum'] ) ) ? $new_instance['minnum']  : '0';
		$instance['maxnum'] = ( ! empty( $new_instance['maxnum'] ) ) ? $new_instance['maxnum']  : '100';
		$instance['color_scheme'] = ( ! empty( $new_instance['color_scheme'] ) ) ? $new_instance['color_scheme']  : 'fresh';
		$instance['usenofollow'] = ( ! empty( $new_instance['usenofollow'] ) ) ? $new_instance['usenofollow']  : 0;
		
		return $instance;
	}
	public function get_tags($instance){
		
		$minnum = isset($instance['minnum']) ? $instance['minnum'] : 0;
		$maxnum = isset($instance['maxnum']) ? $instance['maxnum'] : 100;
		$number = isset($instance['number']) ? $instance['number'] : 0;
		$tags = muki_tag_cloud_get_terms($instance);
		$word_list = array();
		$all_tags = array();
		$article_order = isset($instance['article_order']) ? $instance['article_order'] : muki_tag_cloud_get_option('article_order');
		foreach($tags as $k => $tag)
		{
			if( $minnum <= (int)$tag->count  && (int)$tag->count <= $maxnum)
			{
				$tags[$k]->link = get_term_link( intval($tag->term_id), $tag->taxonomy );
				$weight = $article_order == 'random' ? rand(1, 100) : $tag->count;
				$word_list[] = array(
					'text' => $tag->name,
					'link' => urldecode($tag->link),
					'weight' => $weight,
				);
				$all_tags[] = $tags[$k];
			}
		}
    	return array('tags'=>$all_tags ,'word_list'=>$word_list );
	}
}
/*
add_filter('wp_generate_tag_cloud', 'muki_generate_tag_cloud',10,2);

function muki_generate_tag_cloud($return, $tags)
{
	return str_replace(' href='," rel='nofollow' href=",$return);
}
*/

/* get wordpress terms */
function muki_tag_cloud_get_terms($args=array())
{
	$defaults = array(
		'number' => '0' , 'offset' => '0'
	);
	$args = wp_parse_args($args, $defaults);
	$tags = get_terms('post_tag', $args);
	return $tags;
}
/* footer scripts */
add_action( 'wp_footer','muki_tag_cloud_footer_scripts',30);
function muki_tag_cloud_footer_scripts() {
	global $wp_registered_widgets;
	$word_list = array();
	if( is_array($wp_registered_widgets) && count($wp_registered_widgets) )
	{
		foreach( $wp_registered_widgets as $id => $widget )
		{
			$oWidget = & $widget['callback'][0];
			if( is_object($oWidget) && $oWidget->id_base =='mukitagcloud'){
				$word_list[$id] = $oWidget->getData($id);
			}
		}
	}
	
	if(is_array($word_list) && count($word_list) )
	{
echo '<script type="text/javascript">'."\n";
if(muki_tag_cloud_get_option('usejq') =='wp')
{
echo 'jQuery(function($){'."\n";
}else{
echo '$(function(){'."\n";
}
echo 'var word_list = '.json_encode($word_list).';'."\n";
foreach( $word_list as $id => $data)
{
	echo "\t".'$(\'#muki_tag_cloud_'.$id.'\').empty().jQCloud(word_list["'.$id.'"]);' ."\n";
}
echo '})'."\n";
echo '</script>';
	}
}


/* head style include */
add_action( 'wp_head','muki_tag_cloud_head_scripts',20);
function muki_tag_cloud_head_scripts() {
?>
<style type="text/css">
<?php
	$muki_tag_cloug_font_small = intval( muki_tag_cloud_get_option('font_small') );
	$muki_tag_cloug_font_large = intval( muki_tag_cloud_get_option('font_large') );
	$muki_tag_cloug_font_step = ($muki_tag_cloug_font_large - $muki_tag_cloug_font_small) / 10;
	$index = 1;
	for($size = $muki_tag_cloug_font_small; $size <= $muki_tag_cloug_font_large; $size += $muki_tag_cloug_font_step){
		echo '.muki_tag_cloud.jqcloud span.w'.$index . '{font-size:' . ($size/12) * 100 .'%;}';
		$index++;
	}
?>
</style>
<?php
}
/* head script prefix add*/
function muki_tag_cloud_script_prefix_add() {
	$jqcloud = muki_tag_cloud_get_option('usejq') =='wp' ? 'jqcloud-1.0.4.wp.min.js' : 'jqcloud-1.0.4.min.js';
	wp_enqueue_script( 'muki-jqcloud-script', plugins_url(MUKI_TG_NAME.'/' . $jqcloud), array( 'jquery') );
	/* Enqueue Styles */
	wp_register_style( 'muki-jqcloud-stylesheet', plugins_url(MUKI_TG_NAME.'/jqcloud.css') );
	wp_enqueue_style( 'muki-jqcloud-stylesheet' );
}
add_action( 'wp_enqueue_scripts', 'muki_tag_cloud_script_prefix_add' );
?>