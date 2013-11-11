<?php
/* Setting Option Page*/
//display the actual content of option page.
function muki_tag_cloud_option_page() {
	
    $muki_tag_valid = 'muki_tag_valid';
    
    //get options
    $muki_tag_cloud_setting  = muki_tag_cloud_get_option();
    
    //update options
    if( !empty($_POST[ MUKI_TG_SETTING ]) && check_admin_referer($muki_tag_valid,'check-form') ) {
    	$muki_tag_cloud_setting = wp_parse_args($_POST[ MUKI_TG_SETTING ],$muki_tag_cloud_setting);
		update_option( MUKI_TG_SETTING, $muki_tag_cloud_setting );
		wp_cache_set(MUKI_TG_NAME.'_option', $muki_tag_cloud_setting, 'option');
        echo '<div class="updated"><p><strong>'.__('Settings saved.', MUKI_TG_NAME).'</strong></p></div>';  
    }
    //delete options
    if( isset($_POST[ $muki_tag_delete ]) && check_admin_referer($muki_tag_valid,'check-form') ) {
        //compatible with version older than FII 2.0
        delete_option( MUKI_TG_SETTING );
        echo '<div class="updated"><p><strong>'.__('Settings deleted.', MUKI_TG_NAME).'</strong></p></div>';  
    }
    echo '<div class="wrap">'."\n".
         '<div id="icon-options-general" class="icon32"><br /></div>'."\n".
         '<h2>'.__('Muki Tag Cloud - User Options',MUKI_TG_NAME).'</h2>'."\n".
         '<h3>'.__('Updates your settings here', MUKI_TG_NAME).'</h3>';
?>

<form name="faster-insert-option" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($muki_tag_valid, 'check-form'); ?>

    <table width="100%" cellspacing="2" cellpadding="5" class="form-table">
    	
        <tr valign="top">
            <th scope="row"><?php _e("Use jQuery For", MUKI_TG_NAME ); ?></th>
            <td>
	            <label for="muki_tag_cloud_setting_usewpjq">
	            <input type="radio" name="muki_tag_cloud_setting[usejq]" id="muki_tag_cloud_setting_usewpjq" <?php if($muki_tag_cloud_setting['usejq'] == 'wp') echo 'checked="checked"' ?> value="wp" />
	        	<?php _e("WordPress jQuery", MUKI_TG_NAME ); ?></label>
	        &nbsp;&nbsp;
	        	<label for="muki_tag_cloud_setting_usetpjq">
	        	<input type="radio" name="muki_tag_cloud_setting[usejq]" id="muki_tag_cloud_setting_usetpjq" <?php if($muki_tag_cloud_setting['usejq'] == 'tp') echo 'checked="checked"' ?> value="tp" />
	        	<?php _e("Template jQuery", MUKI_TG_NAME ); ?></label>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e("Font Size", MUKI_TG_NAME ); ?></th>
            <td>
            	<label for="muki_tag_cloud_setting_font_small"><?php _e("smallest", MUKI_TG_NAME );?>:&nbsp;<input type="text" name="muki_tag_cloud_setting[font_small]" id="muki_tag_cloud_setting_font_small" value="<?php echo $muki_tag_cloud_setting['font_small']; ?>" size="1" /> <?php _e("px; defaults is 12", MUKI_TG_NAME ); ?></label><br />
            	<label for="muki_tag_cloud_setting_font_large"><?php _e("largest", MUKI_TG_NAME);?>:&nbsp;<input type="text" name="muki_tag_cloud_setting[font_large]" id="muki_tag_cloud_setting_font_large" value="<?php echo $muki_tag_cloud_setting['font_large']; ?>" size="1" /> <?php _e("px; defaults is 36", MUKI_TG_NAME ); ?></label>
            </td>
        </tr>
        			<!--
        <tr valign="top">
            <th scope="row"><?php _e("Color Scheme", MUKI_TG_NAME ); ?></th>
            <td><label for="muki_tag_cloud_setting_color_scheme">
            	<select id="muki_tag_cloud_setting_color_scheme" name="muki_tag_cloud_setting[color_scheme]" onchange="document.getElementById('preview').src = '../wp-content/plugins/muki-tag-cloud/colorscheme-' + this.value + '.png'">
            <?
				foreach( array(
		"fresh" => 'fresh (default)',
		"light" => 'light',
		"dark"  => 'dark',
		"blue"  => 'single color (blue)',
		"red"   => 'single color (red)',
		"green" => 'single color (green)',
		"gray"  => 'single color (gray)'
    ) as $key => $val){
					$selected = ( $key == $muki_tag_cloud_setting['color_scheme'] ) ? ' selected="selected"':'';
					echo  '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
				}
            ?>
            	</select>
            	<br />
            	<img id="preview" width="100%" src="../wp-content/plugins/muki-tag-cloud/colorscheme-<?php echo $muki_tag_cloud_setting['color_scheme'];?>.png" border="0" />
            </td>
        </tr>
        		-->
    </table>

<p class="submit">
<input type="submit" name="<?php echo $muki_tag_update; ?>" class="button-primary" value="<?php esc_attr_e('Save Changes', MUKI_TG_NAME ) ?>" />
<input type="submit" name="<?php echo $muki_tag_delete; ?>" class="button" value="<?php esc_attr_e('Delete Setting', MUKI_TG_NAME ) ?>" />
</p>

</form>
<?php
    echo '</div>'."\n";
}


?>