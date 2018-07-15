<?php
/*
Plugin Name: Logger by U7
Description: Logging and debug events and vars on site. For adding var in log use hook: <br><code>do_action("logger_u7", $var);</code>
Author: WPCraft
Author URI: https://wpcraft.ru/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Version: 1.3
*/
register_uninstall_hook( __FILE__, 'logger_u7_uninstall' );
function logger_u7_uninstall() {
	delete_option( 'logger_u7' );
}

class Logger_U7 {
	public $url;
	
	/**
	 * Logger_U7 constructor.
	 */
	public function __construct() {
		$this->url = $_SERVER['REQUEST_URI'];
		add_action( 'admin_menu', function () {
			add_management_page( $page_title = 'Logger', $menu_title = 'Logger', $capability = 'manage_options', $menu_slug = 'logger_u7', $func = array(
				$this,
				'display_page',
			) );
		} );
		add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) );
		add_action( 'tool_btns', array( $this, 'manual_clear' ), 15 );
		add_action( 'tool_actions_logger_u7_manual_clear', array( $this, 'action_clear' ) );
		add_action( 'logger_u7', array( $this, 'add' ) );
		add_action( 'cl', array( $this, 'add' ) );
	}
	
	/**
	 *
	 */
	public function display_page() {
		
		echo '<h1>Log</h1>';
		if ( empty( $_GET['a'] ) ) {
			
			do_action( 'tool_btns' );
		} else {
			do_action( 'tool_actions_' . $_GET['a'] );
		}
		echo '<p>For adding data to log use hook: <br><pre>do_action("logger_u7", $var);</pre></p><hr>';
		$data = get_option( 'logger_u7' );
		if ( ! is_array( $data ) ) {
			echo '<p>No data in log</p>';
			
			return;
		}
		$data = array_reverse( $data );
		?>
		<table border="1" width="100%">
			<tr>
				<th>NN</th>
				<th>Timestamp</th>
				<th>Data</th>
			</tr>
			<?php
			$i = 0;
			foreach ( $data as $item ): ?>
				<tr>
					<td valign="top" width="20px">
						<span><?php echo $i; ?></span>
					</td>
					<td valign="top" width="100px">
						<span><?php echo $item['timestamp']; ?></span>
					</td>
					<td>
						<?php $this->vd( $item['data'] ); ?>
					</td>
				</tr>
				<?php $i ++; endforeach; ?>
		</table>
		<?php
	}
	
	public function vd( $var ) {
		echo '<pre>';
		if ( ! empty( $var ) ) {
			print_r( $var );
		} else {
			var_dump( $var );
		}
		echo '</pre>';
	}
	
	/**
	 * @param string $data <pre><?php print_r( $item['data'] ) ?></pre>
	 */
	public function add( $data = '' ) {
		$log = get_option( 'logger_u7' );
		if ( empty( $log ) ) {
			$log = array();
		}
		$log[] = array(
			'timestamp' => date_i18n( "d.m.Y H:i:s" ),
			'data'      => $data,
		);
		$log   = array_slice( $log, - 99, 99 );
		update_option( 'logger_u7', $log, false );
	}
	
	/**
	 * Add fast link in plugins list
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="tools.php?page=logger_u7">Logger</a>';
		array_unshift( $links, $settings_link );
		
		return $links;
	}
	
	/**
	 *
	 */
	public function manual_clear() {
		?>
		<a href="<?php echo add_query_arg( 'a', 'logger_u7_manual_clear', admin_url( 'tools.php?page=logger_u7' ) ) ?>"
			class="button">Очистить</a>
		<?php
	}
	
	/**
	 *
	 */
	public function action_clear() {
		
		$data = $this->delete();
		if ( $data != false ) {
			echo '<p>Очищено</p>';
			wp_redirect( remove_query_arg( 'a', $this->url ) );
		}
	}
	
	/**
	 * @return bool
	 */
	public function delete() {
		delete_option( 'logger_u7' );
		
		return true;
	}
}

new Logger_U7;
