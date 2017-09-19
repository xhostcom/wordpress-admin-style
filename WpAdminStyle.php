<?php
/**
 * Plugin Name:   WordPress Admin Style
 * Plugin URI:    https://github.com/bueltge/wordpress-admin-style
 * GitHub URI:    bueltge/wordpress-admin-style
 * Text Domain:   wp_admin_style
 * Domain Path:   /languages
 * Description:   Shows the WordPress admin styles on one page to help you to develop WordPress compliant
 * Author:        Frank Bültge
 * Version:       1.5.1
 * Licence:       GPLv3+
 * Author URI:    https://bueltge.de
 * Last Change:   2017-05-24
 */

! defined( 'ABSPATH' ) and exit;

/**
 * Include the Github Updater Lite.
 *
 * @see https://github.com/FacetWP/github-updater-lite
 */
include_once __DIR__ . '/inc/github-updater.php';

add_action(
	'plugins_loaded',
	array( WpAdminStyle::get_instance(), 'plugin_setup' )
);

/**
 * Class WpAdminStyle
 */
class WpAdminStyle {

	protected $patterns_dir = '';

	protected static $file_replace = array( '.php', '_', '-', ' ' );

	/**
	 * Constructor
	 *
	 * @since  0.0.1
	 */
	public function __construct() {}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook  plugins_loaded
	 * @since    05/02/2013
	 * @return   void
	 */
	public function plugin_setup() {

		$this->load_classes();

		if ( ! is_admin() ) {
			return NULL;
		}

		$this->patterns_dir = plugin_dir_path( __FILE__ ) . 'patterns';

		// add menu item incl. the example source
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		// Plugin love
		add_filter( 'plugin_row_meta', array( $this, 'donate_link' ), 10, 2 );
	}

	/**
	 * points the class
	 *
	 * @access public
	 * @since  0.0.1
	 */
	public static function get_instance() {

		static $instance;

		if ( NULL === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Scans the plugins subfolder and include files
	 *
	 * @since   05/02/2013
	 * @return  void
	 */
	protected function load_classes() {

		// load required classes
		foreach ( glob( __DIR__ . '/inc/*.php' ) as $path ) {
			require_once $path;
		}
	}

	/**
	 * return plugin comment data
	 *
	 * @uses   get_plugin_data
	 * @access public
	 * @since  0.0.1
	 *
	 * @param  $value string, default = 'Version'
	 *                Name, PluginURI, Version, Description, Author, AuthorURI, TextDomain, DomainPath, Network, Title
	 *
	 * @return string
	 */
	private function get_plugin_data( $value = 'Version' ) {

		static $plugin_data = array();

		// fetch the data just once.
		if ( isset( $plugin_data[ $value ] ) ) {
			return $plugin_data[ $value ];
		}

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugin_data = get_plugin_data( __FILE__ );

		return empty ( $plugin_data[ $value ] ) ? '' : $plugin_data[ $value ];
	}

	/**
	 * Add Menu item on WP Backend
	 *
	 * @uses   add_menu_page
	 * @access public
	 * @since  0.0.1
	 * @return void
	 */
	public function add_menu_page() {

		$page_hook_suffix = add_menu_page(
			esc_html__( 'WordPress Admin Style', 'WpAdminStyle' ),
			esc_html__( 'Admin Style', 'WpAdminStyle' ),
			'read',
			'WordPress_Admin_Style',
			array( $this, 'get_style_examples' )
		);
		add_action( 'admin_print_scripts-' . $page_hook_suffix, array( $this, 'add_highlight_js' ) );
	}

	/**
	 * Return list of pattern files or name of files
	 *
	 * @since 2015-03-25
	 *
	 * @param string $type
	 *
	 * @param bool   $sort
	 *
	 * @return array|mixed
	 */
	public function get_patterns( $type = '', $sort = TRUE ) {

		$files              = array();
		$this->patterns_dir = plugin_dir_path( __FILE__ ) . 'patterns';
		$handle             = opendir( $this->patterns_dir );

		while ( FALSE !== ( $file = readdir( $handle ) ) ) {
			if ( FALSE !== stripos( $file, '.php' ) ) {
				$files[ ] = $file;
			}
		}

		if ( $sort ) {
			sort( $files );
		}

		$files_h = str_replace( self::$file_replace, ' ', $files );

		if ( 'headers' === $type ) {
			return $files_h;
		}

		return $files;
	}

	/**
	 * Echo Markup examples
	 *
	 * @uses
	 * @access public
	 * @since  0.0.1
	 * @return void
	 */
	public function get_style_examples() {

		?>

		<div class="wrap">

			<h1><?php echo $this->get_plugin_data( 'Name' ) ?></h1>

			<?php
			$this->get_mini_menu();
			$files = $this->get_patterns();

			// Load files and get data for view and list source
			foreach ( $files as $file ) {
				$anker = str_replace( self::$file_replace, '', $file );
				$patterns = $this->patterns_dir . '/' . $file;

				echo '<section class="pattern" id="' . $anker . '">';
				include_once $patterns;
				echo '<details class="primer">';
				echo '<summary title="Show markup and usage">&#8226;&#8226;&#8226; ' . esc_attr__(
						'Show markup and usage', 'WpAdminStyle'
					) . '</summary>';
				echo '<section>';
				echo '<pre><code class="language-php-extras">' . htmlspecialchars(
						file_get_contents( $patterns )
					) . '</code></pre>';
				echo '</section>';
				echo '</details><!--/.primer-->';
				echo '<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);" style="margin:3px 0 0 30px;">' . esc_attr__(
						'scroll to top', 'WpAdminStyle'
					) . '</a><br class="clear" /></p>';
				echo '</section><!--/.pattern-->';
				echo '<hr>';
			}
			?>

		</div> <!-- .wrap -->
	<?php
	}

	public function get_mini_menu() {

		$patterns = $this->get_patterns( 'headers' );
		?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<!-- main content -->
				<div id="post-body-content">

					<div class="meta-box-sortables ui-sortable">

						<div class="postbox">

							<h2><span><?php _e( 'MiniMenu', 'WpAdminStyle' ); ?></span></h2>

							<div class="inside">

								<table class="widefat" cellspacing="0">
									<?php
									$class = '';
									foreach ( $patterns as $pattern ) {
										$class = '' === $class ? $class = ' class="alternate"' : '';
										$anker = str_replace( self::$file_replace, '', $pattern );

										?>
										<tr<?php echo $class; ?>>
											<td class="row-title">
												<a href="#<?php echo $anker ?>">
													<?php echo ucfirst( $pattern ); ?>
												</a>
											</td>
										</tr>
									<?php
									} // end foreach patterns
									?>
								</table>

							</div>
							<!-- .inside -->

						</div>
						<!-- .postbox -->

					</div>
					<!-- .meta-box-sortables .ui-sortable -->

				</div>
				<!-- post-body-content -->

				<!-- sidebar -->
				<div id="postbox-container-1" class="postbox-container">

					<div class="meta-box-sortables">

						<div class="postbox">

							<h2><span><?php _e( 'About the plugin', 'WpAdminStyle' ); ?></span></h2>

							<div class="inside">
								<p><?php _e(
										'Please read more about this small plugin on <a href="https://github.com/bueltge/WordPress-Admin-Style">github</a> or in <a href="http://wpengineer.com/2226/new-plugin-to-style-your-plugin-on-wordpress-admin-with-default-styles/">this post</a> on the blog of WP Engineer.',
										'WpAdminStyle'
									); ?></p>

								<p>&copy; Copyright 2008 - <?php echo date( 'Y' ); ?>
									<a href="http://bueltge.de">Frank B&uuml;ltge</a></p>
							</div>

						</div>
						<!-- .postbox -->

						<div class="postbox">

							<h2><span><?php _e( 'Resources & Reference', 'WpAdminStyle' ); ?></span></h2>

							<div class="inside">
								<ul>
									<li>
										<a href="http://dotorgstyleguide.wordpress.com/">WordPress.org UI Style Guide</a>
									</li>
									<li>
										<a href="https://make.wordpress.org/core/handbook/best-practices/coding-standards/html/">HTML Coding Standards</a>
									</li>
									<li>
										<a href="https://make.wordpress.org/core/handbook/best-practices/coding-standards/css/">CSS Coding Standards</a>
									</li>
									<li>
										<a href="https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/">PHP Coding Standards</a>
									</li>
									<li>
										<a href="https://make.wordpress.org/core/handbook/best-practices/coding-standards/javascript/">JavaScript Coding Standards</a>
									</li>
									<li><a href="https://make.wordpress.org/design/">WordPress UI Group</a></li>
								</ul>
							</div>

						</div>
						<!-- .postbox -->

					</div>
					<!-- .meta-box-sortables -->

				</div>
				<!-- #postbox-container-1 .postbox-container -->

			</div>
			<br class="clear">
		</div>
	<?php
	}

	/**
	 * Add donate link to plugin description in /wp-admin/plugins.php
	 *
	 * @param  array  $plugin_meta
	 * @param  string $plugin_file
	 *
	 * @return array
	 */
	public function donate_link( $plugin_meta, $plugin_file ) {

		if ( plugin_basename( __FILE__ ) === $plugin_file ) {
			$plugin_meta[ ] = sprintf(
				'&hearts; <a href="%s">%s</a>',
				'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6069955',
				esc_html__( 'Donate', 'WpAdminStyle' )
			);
		}

		return $plugin_meta;
	}

	/**
	 * Register and enqueue script and styles for highlighting source.
	 *
	 * @since  2016-05-20
	 */
	public function add_highlight_js() {

		wp_register_style(
			'prism',
			plugins_url( 'css/prism.css', __FILE__ ),
			'',
			'2016-05-20',
			'screen'
		);
		wp_enqueue_style( 'prism' );

		wp_register_script(
			'prism',
			plugins_url( 'js/prism.js', __FILE__ ),
			array(),
			'2016-05-20',
			TRUE
		);
		wp_register_script(
			'wpast_prism',
			plugins_url( 'js/wpast-prism.js', __FILE__ ),
			array( 'prism' ),
			'2016-05-20',
			TRUE
		);
		wp_enqueue_script( 'wpast_prism' );
	}
} // end class
