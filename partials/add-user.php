<?php

/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin/partials
 */
?>
<?php ?>
<div class="pf-opt-group span6">
	<div class="rss-box postbox">
		<div class="handlediv" title="Click to toggle"><br></div>
		<h3 class="hndle"><span><?php _e( 'Add Pocket Account', \ReadSync::$plugin_slug ); ?></span></h3>
		<div class="inside">
			<div class="pf_feeder_input_box">
				<a class="button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=pf-feeder&pocket_login=true' ) ); ?>"><?php _e( 'Login to Pocket', \ReadSync::$plugin_slug ); ?></a>
			</div>
		</div>
	</div>
</div>
