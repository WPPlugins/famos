<?php
  /**
   * @package famos
   */
/*
Plugin Name: Famos
Plugin URI: http://www.famos.com/wordpress
Description: Famos functionality
Version: 1.4.0
Author: Famos, LLC
Author URI: http://famos.com
*/

class Famos {

  var $version;
  var $settings = array();
  var $defaultsettings = array();

  // Don't start this plugin until all other plugins have started up
  function Famos() {

    require_once(dirname(__FILE__).'/VERSION.php');
    $this->version = FAMOS_VERSION;
    $this->defaultsettings =
      apply_filters('famos_defaultsettings',
                    array('version'=>$this->version,
                          'content-server'=>'http://content.famos.com'));

    $this->settings = get_option('famos-options');
    if (!$this->settings) {
      $this->settings = $this->defaultsettings;
      update_option('famos-options', $this->settings);
    }

    // TODO: move this to the plugins settings dropdown
    add_action('admin_menu', array(&$this, 'addOptionsPage'));
    add_action('admin_init', array(&$this, 'registerSettings'));
    add_filter('plugin_action_links', array(&$this, 'addPluginActionLink'), 10, 2);

    //set the email cookie for this session only (we don't want to create too
    //much of a security risk
    global $current_user;
    get_currentuserinfo();

    // Loads our Javascript file everywhere
    // TODO: make enterprise ID configurable from WP
    $explore = $this->settings['content-server']
      . "/pg/conduit/famos-explore.js"
      . "?e=" . urlencode("e:".get_site_url())
      . "&u=" . urlencode("email:{$current_user->user_email}")
      . "&wp=1";
    wp_enqueue_script('famos', $explore);
    wp_enqueue_script('famos-wp', plugins_url('famos/famos.js'));

    // Add buttons for annotation
    $this->addbuttons();
  }

  function registerSettings() {
    register_setting('famos-options', 'famos-options', 
                     array(&$this, 'validateOptions'));
    add_settings_section('famos-main', 'Main Settings', 
                         array(&$this, 'settingsText'), 'famos');
    // Add one per options field
    add_settings_field('content-server', 'Content Server URL',
                       array(&$this, 'settingsInput'),
                       'famos', 'famos-main');
  }

  /** Adds a direct link to editing the plugin options. */
  function addPluginActionLink($links, $file) {
    static $this_plugin;
    
    if( empty($this_plugin) ) $this_plugin = plugin_basename(__FILE__);
    
    if ( $file == $this_plugin ) {
      $settings_link = '<a href="' . admin_url( 'options-general.php?page=famos' ) . '">' . __('Settings', 'famos') . '</a>';
      array_unshift( $links, $settings_link );
    }
    return $links;
  }

  function addOptionsPage() {
    $menu_label = 'Famos';
    $title = 'Famos Options Page';
    add_options_page($title, $menu_label, 'manage_options',
                     'famos', array(&$this, 'optionsPage'));
  }

  function optionsPage() {
?>
<div class='wrap'>
<h2>Famos Plugin</h2>
<form action="options.php" method="post">
      <?php settings_fields('famos-options'); ?>
      <?php do_settings_sections('famos'); ?>
<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>"/>
</form>
</div>
<?php
  }

  function validateOptions($input) {
    // TODO: make sure the content server exists and is valid
    return $input;
  }

  function settingsText() {
    echo "";
  }

  function settingsInput() {
    $options = get_option('famos-options');
    echo "<input name='famos-options[content-server]' size='64' type='text' value='{$options['content-server']}'/>";
  }

  function addbuttons() {
    // Don't bother doing this stuff if the current user lacks permissions
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
      return;
 
    // Add only in Rich Editor mode
    if ( get_user_option('rich_editing') == 'true') {
      add_filter("mce_external_plugins", array(&$this, 'addTinyMCEPlugin'));
      add_filter('mce_buttons', array(&$this, 'registerTinyMCEButtons'));
      add_filter("mce_external_plugins", array(&$this, 'addTinyMCENoEdit'));
    }
  }

  function addTinyMCEPlugin($plugin_array) {
    $plugin_array['famos'] = get_option('siteurl') . '/wp-content/plugins/famos/resources/tinymce3/editor_plugin.js';
    return $plugin_array;
  }

  function addTinyMCENoEdit($plugin_array) {
    $plugin_array['famosnonedit'] = get_option('siteurl') . '/wp-content/plugins/famos/resources/tinymce3/noneditable.js';
    return $plugin_array;
  }

  function registerTinyMCEButtons($buttons) {
    array_push($buttons, "separator", "famos_author_menu");
    return $buttons;
  }
}

add_action('init', 'Famos');
function Famos() {
  global $Famos;
  $Famos = new Famos();
}

?>
