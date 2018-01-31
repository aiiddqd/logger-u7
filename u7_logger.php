<?php
/*
Plugin Name: Logger by U7
Description: Logging and debug events and vars on site. For adding var in log use hook: <br><code>do_action("logger_u7", $var);</code>
Author: WPCraft
Author URI: https://wpcraft.ru/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Version: 1.2
*/

class Logger_U7
{

  function __construct()
  {
    add_action('admin_menu', function(){
      add_management_page(
        $page_title = 'Logger',
        $menu_title = 'Logger',
        $capability = 'manage_options',
        $menu_slug = 'logger_u7',
        $func = array($this, 'display_page')
      );

    });

    add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array($this, 'add_settings_link') );
	
	
	add_action( 'wp_ajax_logger_u7_clear_history', array($this, 'clear_history') );
    add_action('logger_u7', array($this, 'add'));
  }

  function display_page(){

    echo '<h1>Log</h1>';
    echo '<p>For adding data to log use hook: <br><pre>do_action("logger_u7", $var);</pre></p>';
    echo '<a class="button clear_history" href="">Clear history</a><hr>';

    $data = get_option('logger_u7');
    if( ! is_array($data)){
      echo '<p>No data in log</p>';
      return;
    }

    $data = array_reverse($data);
    ?>
      <table border="1" width="100%" class="logger_u7_table">
        <tr>
          <th>Timestamp</th>
          <th>Data</th>
        </tr>
        <?php foreach($data as $item): ?>
          <tr class="data">
            <td valign="top" width="100px">
              <span><?php echo $item['timestamp']; ?></span>
            </td>
            <td>
              <pre><?php var_dump($item['data']) ?></pre>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>

      <script>
          jQuery( '.clear_history' ).click(
            function( eventObject ){
				eventObject.preventDefault();
				jQuery.post( 
					'<?php echo get_admin_url() ?>/admin-ajax.php',
					{
						action : 'logger_u7_clear_history'
					},
					function( response ){
						response = JSON.parse( response );
						if ( response.done == true ){
							jQuery( '.logger_u7_table tr.data' ).remove();
						}
					}
				);
            }
          )
      </script>
<?php
  }

  function add($data = ''){
    $log = get_option('logger_u7');
    if(empty($log)){
      $log = array();
    }

    $log[] = array(
      'timestamp' => date("Y-m-d H:i:s"),
      'data' => $data,
    );

    $log = array_slice($log, -99, 99);

    update_option('logger_u7', $log, false);
  }

  /**
  * Add fast link in plugins list
  */
  function add_settings_link($links)
  {
    $settings_link = '<a href="tools.php?page=logger_u7">Logger</a>';
    array_unshift( $links, $settings_link );
    return $links;
  }
  
  function clear_history()
  {
    update_option('logger_u7', array(), false);
    wp_die( json_encode( array( 'done' => true ) ) );
  }
}

new Logger_U7;
