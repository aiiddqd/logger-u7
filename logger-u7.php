<?php
/*
Plugin Name: Logger by @uptimizt
Description: Logging and debug events and vars on site. For adding var in log use hook: <br><code>do_action("lu/option", $var);</code>
Author: uptimizt
Author URI: https://github.com/uptimizt
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Version: 1.7
*/

namespace uptimizt;

/**
 * Logger data for debug
 */
class Logger {

  /**
   * table name for save log
   */
  public static $table_name = 'logger_u7';

  /**
   * file path for save log
   */
  public static $file_path = WP_CONTENT_DIR . '/wplu.log';

  /**
   * The Init
   */
  public static function init() {

    add_action('lu/file', array(__CLASS__, 'add_log_file'));
    add_action('lu/table', array(__CLASS__, 'add_log_to_table'));


    add_action( 'admin_menu', function () {
      add_management_page(
        $page_title = 'Logger',
        $menu_title = 'Logger',
        $capability = 'manage_options',
        $menu_slug = 'logger_u7',
        $func = array( __CLASS__, 'display_page') );
    });

    add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( __CLASS__, 'add_settings_link' ) );
    add_action( 'tool_btns', array( __CLASS__, 'manual_clear' ), 15 );
    add_action( 'tool_actions_logger_u7_manual_clear', array( __CLASS__, 'action_clear' ) );

    add_action( 'logger_u7', array( __CLASS__, 'add_log' ) );
    add_action( 'cl', array( __CLASS__, 'add_log' ) );
    add_action( 'u7/el', array(__CLASS__, 'add_log_file'));


    register_activation_hook( __FILE__, array(__CLASS__, 'activation') );
    register_uninstall_hook( __FILE__, array(__CLASS__, 'uninstall') );

  }

  /**
   * activation
   */
  public static function activation() {

      self::create_table();
  }

  /**
   * add_log_to_table
   */
  public static function add_log_to_table($data = ''){

     global $wpdb;
     //check if table exists

     $table_name = $wpdb->base_prefix . self::$table_name;
     if($table_name != $wpdb->get_var("SHOW TABLES LIKE '$table_name'")){
       self::create_table();
       error_log('Logger by @uptimizt: no table');
       return;
     }

     $backtrace = debug_backtrace();
     $backtrace = $backtrace[3]; //added line and file where the hook is turned on

     $data = print_r($data, true);

     $data = sprintf('%s:%s' . PHP_EOL . '%s', $backtrace['file'], $backtrace['line'], $data);


     $data = sprintf('### %s' . PHP_EOL . '%s', date_i18n( "d.m.Y H:i:s" ), $data);

     $wpdb->insert("{$table_name}", array(
         'data' => $data,
         'created_at' => gmdate('Y-m-d H:i:s'),
     ));
  }

  /**
   * create_table
   */
  public static function create_table(){
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->base_prefix . self::$table_name;

    $sql = "CREATE TABLE `{$table_name}` (
      id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      data longtext,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
      PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $success = empty( $wpdb->last_error );
  }

  /**
   * Add log to file /var/log/wplu.log
   */
  public static function add_log_file($data = ''){

    /**
     * Hook for chg path log
     */
    $path = apply_filters('logger_u7_path_file', self::$file_path);

    $backtrace = debug_backtrace();
    $backtrace = $backtrace[3]; //added line and file where the hook is turned on

    $data = array(
      'file' => $backtrace['file'],
      'line' => $backtrace['line'],
      'data' => $data
    );

    $data = print_r($data, true);
    $data = date_i18n( "d.m.Y H:i:s" ) . PHP_EOL . $data;
    $data = PHP_EOL . '###' . PHP_EOL . $data;

    error_log($data, 3, $path);
  }

  /**
   * Display Logger UI
   */
  public static function display_page()
  {
    _e('<h1>Log</h1>', 'logger_u7');

    if ( empty( $_GET['a'] ) ) {
        do_action( 'tool_btns' );
    } else {
      do_action( 'tool_actions_' . $_GET['a'] );
    }

    printf('<p>%s: <br><pre>do_action("lu/table", $var);</pre></p><hr>', __('For adding data to log use hook', 'logger_u7'));

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
                <?php self::vd( $item['data'] ); ?>
              </td>
            </tr>
          <?php $i ++; endforeach; ?>
      </table>
  <?php
  }

  /**
   * vardump wrapper
   */
  public static function vd( $var ) {
    echo '<pre>';
    if ( ! empty( $var ) ) {
      print_r( $var );
    } else {
      var_dump( $var );
    }
    echo '</pre>';
  }

  /**
   * Add var to log
   */
  public static function add_log( $data = '' ) {

    $log = get_option( 'logger_u7' );
      if ( empty( $log ) ) {
      $log = array();
    }

    $log[] = array(
      'timestamp' => date_i18n( "d.m.Y H:i:s" ),
      'data'      => $data,
    );

    $log = array_slice( $log, - 99, 99 );

    delete_option('logger_u7');
    add_option( 'logger_u7', $log, '', $autoload = 'no' );
  }

  /**
   * Add fast link in plugins list
   */
  public static function add_settings_link( $links ) {
    $settings_link = '<a href="tools.php?page=logger_u7">Logger</a>';
    array_unshift( $links, $settings_link );

    return $links;
  }

  /**
   * Manual clear log
   */
  public static function manual_clear() {
    ?>
    <a href="<?php echo add_query_arg( 'a', 'logger_u7_manual_clear', admin_url( 'tools.php?page=logger_u7' ) ) ?>"
    class="button">Очистить</a>
    <?php
  }

  /**
   * Clear log
   */
  public static function action_clear() {

    $url = $_SERVER['REQUEST_URI'];
    delete_option( 'logger_u7' );

    if ( $data != false ) {
      echo '<p>Очищено</p>';
      wp_redirect( remove_query_arg( 'a', $url ) );
    }
  }

  /**
   * Remove option if uninstall plugin
   */
  public static function uninstall() {
    delete_option( 'logger_u7' );
  }
}

Logger::init();
