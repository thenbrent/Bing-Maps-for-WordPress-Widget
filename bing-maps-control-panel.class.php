<?php
class bingMapsForWordpressControlPanel
{
	/**
	 * PHP5 constructor - links to old style PHP4 constructor
	 * @param string $file
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function __construct($file)
	{
		$this->bingMapsForWordpressControlPanel($file);
	}
	
	/**
	 * Old style PHP4 constructor
	 * @param string $file
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function bingMapsForWordpressControlPanel($file)
	{
		// Add Settings link to plugin page
		add_filter("plugin_action_links_".$file, array($this, 'actlinks'));
		// Any settings to initialize
		add_action('admin_init', array($this, 'adminInit'));
		// Load menu page
		add_action('admin_menu', array($this, 'addAdminPage'));
		// Load admin CSS style sheet
		add_action('admin_head', array($this, 'registerHead'));
	}
	
	/**
	 * Add a setting link to the plugin settings page
	 * @param array $links
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function actlinks($links)
	{
		// Add a link to this plugins settings page
		$settings_link = '<a href="options-general.php?page=bing-maps-for-wordpress">Settings</a>'; 
		array_unshift($links, $settings_link); 
		return $links; 
	}
	
	/**
	 * Initialize admin
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function adminInit()
	{
		register_setting('bingMapsForWordpressOptions', 'bing_maps_for_wordpress');
		
		// Check if we have a setting
		$options = get_option('bing_maps_for_wordpress');
		
		if(!isset($options['api']) OR $options['api'] == '')
		{
			add_action('admin_head', array($this, 'initError'));
		}
	}
	
	/**
	 * Plugin initialization error - missing API key most likely
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function initError()
	{
		echo '<div class="updated"><p>No Bing API Key found! Enter one in the Bing Map for WordPress <a href="options-general.php?page=bing-maps-for-wordpress">Settings</a> page</p></div>';
	}
	
	/**
	 * Add an admin page to the general settings panel
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function addAdminPage()
	{
		add_options_page('Bing Maps for WordPress Options', 'Bing Maps for Wordpress', 'administrator', 'bing-maps-for-wordpress', array($this, 'admin'));
	}
	
	/**
	 * Admin page
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function admin()
	{
		
		echo '<div class="wrap">';
		echo '<div class="half1">';
		echo '<form method="post" action="options.php">';
		
		echo '<h2>Bing Maps for WordPress Settings</h2>';
		echo '<p><small>By: Rich Gubby</small></p>';
		echo '<table class="form-table" cellspacing="2" cellpadding="5">';
		
		settings_fields('bingMapsForWordpressOptions');
		$options = get_option('bing_maps_for_wordpress');
		
		echo '<tr>';
		
		echo '<th scope="row"><label>Enter your Bing Maps API Key</label></th>';
		echo '<td><input type="text" class="regular-text" name="bing_maps_for_wordpress[api]" value="'.$options['api'].'" />';
		echo '<br /><span class="description">You can get a Bing Maps API Key from here: <a href="http://msdn.microsoft.com/en-us/library/ff428642.aspx" target="_new">http://msdn.microsoft.com/en-us/library/ff428642.aspx</a></span>';
		echo '</td>';
		echo '</tr>';
		
		echo '<tr><td colspan="2">Once you\'ve entered your Bing Maps API key, create or edit a post and start adding [bingMap] shortcodes in them to display maps, here is one to start you off:<br />[bingMap location="Statue of Liberty"]</td></tr>';
		
		echo '</table><br /><p class="submit"><input class="button-primary" type="submit" value="'.__('Save Changes').'" /></p>';
		echo '</form><p>&nbsp;</p></div>
		
		<div class="half2">
			<h3>Donate</h3>
			<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=rgubby%40googlemail%2ecom&lc=GB&item_name=Richard%20Gubby%20%2d%20WordPress%20plugins&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted"><img class="floatright" src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/donate.png" /></a>
			<p>If you like this plugin, keep it Ad free and in a constant state of development by <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=rgubby%40googlemail%2ecom&lc=GB&item_name=Richard%20Gubby%20%2d%20WordPress%20plugins&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted">donating</a> to the cause!</p> 
			<h3>Follow me</h3>
			<p>
			<a href="http://twitter.com/zqxwzq"><img class="floatleft" src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/follow.png" /></a>
			<p>I\'m on Twitter - make sure you <a href="http://twitter.com/zqxwzq">follow me</a>!</p>
			
			<h3>Other plugins you might like...</h3>
			<h4>Wapple Architect Mobile Plugin</h4>
			<a href="plugin-install.php?tab=search&type=term&s=wapple"><img class="floatright" src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/WAMP.png" alt="Wapple Architect Mobile Plugin" title="Wapple Architect Mobile Plugin" /></a>
			<p>The Wapple Architect Mobile Plugin for WordPress mobilizes your blog so your visitors can read your posts whilst they are on their mobile phone!</p>
			<p>Head over to <a href="http://wordpress.org/extend/plugins/wapple-architect/">http://wordpress.org/extend/plugins/wapple-architect/</a> and install it now
			or jump straight to the <a href="plugin-install.php?tab=search&type=term&s=wapple">Plugin Install Page</a></p>
			
			<h4>WordPress Mobile Admin</h4>
			<a href="plugin-install.php?tab=search&type=term&s=wordpress+mobile+admin+wapple"><img class="title floatleft" src="'.WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/WMA.png" alt="WordPress Mobile Admin" title="WordPress Mobile Admin" /></a>
			<p>WordPress Mobile Admin allows you to create posts from your 
			mobile, upload photots, moderate comments and perform basic post/page management.</p>
			<p>Download it from <a href="http://wordpress.org/extend/plugins/wordpress-mobile-admin/">http://wordpress.org/extend/plugins/wordpress-mobile-admin/</a> or
			jump straight to the <a href="plugin-install.php?tab=search&type=term&s=wordpress+mobile+admin+wapple">Plugin Install Page</a>
		</div>
		</div>';
	}
	
	/**
	 * Add styles to admin header
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function registerHead()
	{
		$url = WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/bing-maps-for-wordpress.css';
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$url."\" />\n";	
	}
}


class Bing_Maps_Widget_Control_Panel extends bingMapsForWordpressControlPanel {
	
	/**
	 * Creates the control panel
	 */
	function __construct( $file ) {
		parent::__construct( $file );

		// Remove admin CSS style sheet
		remove_action('admin_head', array($this, 'registerHead'));
	}

	/**
	 * Add an admin page to the general settings panel
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function addAdminPage() {
		add_options_page( 'Bing Maps for WordPress Options', 'Bing Maps', 'administrator', 'bing-maps-for-wordpress', array( $this, 'admin' ) );
	}

	/**
	 * Admin page
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	function admin() { 
		$options = get_option( 'bing_maps_for_wordpress' );
?>		
<div class="wrap">
	<form method="post" action="options.php">
		<h2>Bing Maps Settings</h2>
		<table class="form-table" cellspacing="2" cellpadding="5">
		
		<?php settings_fields( 'bingMapsForWordpressOptions' ); ?>
		
		<tr>
			<th scope="row"><label>Enter your Bing Maps API Key</label></th>
			<td><input type="text" class="regular-text" name="bing_maps_for_wordpress[api]" value="<?php echo $options['api'] ?>" /></td>
		</tr>

		<tr>
			<td colspan="2"><span class="description">You can get a Bing Maps API Key from here: <a href="http://msdn.microsoft.com/en-us/library/ff428642.aspx" target="_new">http://msdn.microsoft.com/en-us/library/ff428642.aspx</a></span></td>
		</tr>

		<tr>
			<td colspan="2">Once you've entered your Bing Maps API key, add or edit your <a href="<?php echo admin_url( 'widgets.php' ); ?>">Bing Map widget</a>.</td>
		</tr>
		
		</table>
		<p class="submit">
			<input class="button-primary" type="submit" value="Save Changes" />
		</p>
	</form>
</div><?php
	}

}
