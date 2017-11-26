<?php
/*
Plugin Name: U7 Logger
Description: Логирование событий на сайте. Для добавления данных в лог используйте хук: do_action("u7logger", $var)
Author: WPCraft
Author URI: https://wpcraft.ru/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Version: 1.0
*/

class U7Logger
{

  function __construct()
  {
    add_action('admin_menu', function(){
      add_management_page(
        $page_title = 'Logger',
        $menu_title = 'Logger',
        $capability = 'manage_options',
        $menu_slug = 'u7logger',
        $func = array($this, 'display_page')
      );

    });

    add_action('u7logger', array($this, 'add'));
  }

  function display_page(){

    echo '<h1>Лог</h1>';
    echo '<p>Для добавления данных в лог используйте хук: <br><pre>do_action("u7logger", $var);</pre></p><hr>';

    $data = get_option('u7logger');
    if( ! is_array($data)){
      echo '<p>Нет данных в логе</p>';
      return;
    }

    $data = array_reverse($data);
    ?>
      <table>
        <tr>
          <th>Отметка времени</th>
          <th>Данные</th>
        </tr>
        <?php foreach($data as $item): ?>
          <tr>
            <td>
              <?php echo $item['timestamp']; ?>
            </td>
            <td>
              <pre>
              <?php var_dump($item['data']) ?>
              </pre>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php
  }

  function add($data = ''){
    $log = get_option('u7logger');
    if(empty($log)){
      $log = array();
    }

    $log[] = array(
      'timestamp' => date("Y-m-d H:i:s"),
      'data' => $data,
    );

    $log = array_slice($log, -33, 33);

    update_option('u7logger', $log, false);
  }
}

new U7Logger;
