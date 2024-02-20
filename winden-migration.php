<?php
/*
 * Plugin Name:       Winden Migration from v1.x.x to v2.x.x
 * Plugin URI:        https://example.com/plugins/tailwind-cdn-for-gutenberg/
 * Description:       Handle the basics with this plugin.
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Marko Krstic
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/tailwind-cdn-for-gutenberg/
 * Text Domain:       tailwind-cdn-for-gutenberg
 * Domain Path:       /languages
 */

// Define constants for script handle and CDN URL
define('TAILWIND_CSS_HANDLE', 'tailwind-css-cdn');
define('TAILWIND_CDN_URL', 'https://cdn.tailwindcss.com');

// Enqueue Tailwind CSS
function enqueue_tailwind_cdn_assets()
{
  wp_enqueue_script(
    TAILWIND_CSS_HANDLE,
    TAILWIND_CDN_URL,
    array(),
    null,
    false
  );

  // Retrieve the Tailwind configuration from the database
  $inline_script_from_db_encoded = get_option('wakaloka_winden_tailwind_config_cdn', '');
  $inline_script_from_db = base64_decode($inline_script_from_db_encoded);
  $inline_script_from_db = str_replace('module.exports', 'tailwind.config', $inline_script_from_db);

  // Add inline script for Tailwind configuration
  $inline_script = sprintf(
    '<script id="tailwind-css-cdn-js-after">%s</script>',
    $inline_script_from_db
  );

  wp_add_inline_script(TAILWIND_CSS_HANDLE, $inline_script);
}
add_action('enqueue_block_assets', 'enqueue_tailwind_cdn_assets');

// Enqueue additional CSS from the database in Gutenberg editor
function enqueue_additional_css_from_db()
{
  // Get CSS from the database
  $css_from_db_encoded = get_option('wakaloka_winden_global_css', '');
  $css_from_db = base64_decode($css_from_db_encoded);

  // Check if there is any CSS to output
  if ($css_from_db) {
    echo '<style type="text/tailwindcss">' . esc_html($css_from_db) . '</style>';
  }
}
add_action('enqueue_block_editor_assets', 'enqueue_additional_css_from_db');




// Your previous constants and functions...

function winden_migration_settings_menu()
{
  add_menu_page(
    'Winden Migration',
    'Winden Migration',
    'manage_options',
    'winden-migration-settings',
    'winden_migration_settings_page',
    null,
    99
  );
}
add_action('admin_menu', 'winden_migration_settings_menu');

function winden_migration_settings_page()
{

  // Security check for permissions
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }

  // Handle the 'Migrate CSS' button click
  if (isset($_POST['migrate_css'])) {
    if (!wp_verify_nonce($_POST['winden_migration_nonce'], 'winden_migration_action')) {
      die('Security check failed.');
    }
    $css_value = get_option('wakaloka_winden_global_css', '');
    update_option('winden_global_css', $css_value);
    echo '<div class="notice notice-success is-dismissible"><p>CSS Migrated Successfully!</p></div>';
  }

  // Handle the 'Migrate Config' button click
  if (isset($_POST['migrate_config'])) {
    if (!wp_verify_nonce($_POST['winden_migration_nonce'], 'winden_migration_action')) {
      die('Security check failed.');
    }
    $config_value = get_option('wakaloka_winden_tailwind_config_cdn', '');
    update_option('winden_tailwind_config', $config_value);
    echo '<div class="notice notice-success is-dismissible"><p>Config Migrated Successfully!</p></div>';
  }

  // Render the settings page
?>
  <div class="wrap">
    <h1><?php _e('Winden Migration Settings', 'tailwind-cdn-for-gutenberg'); ?></h1>

    <form method="post">
      <?php wp_nonce_field('winden_migration_action', 'winden_migration_nonce'); ?>
      <input type="submit" name="migrate_css" value="Migrate CSS" class="button button-primary">
      <input type="submit" name="migrate_config" value="Migrate Config" class="button button-primary">
    </form>

    <p>This migration will transfer your CSS and configuration. Additionally, it will convert "module.exports" to "export default" to ensure compatibility with modern environments.</p>
    <p>If you are using custom plugins, please follow up here for <a target="_blank" href="https://docs.dplugins.com/winden/migration-to-20/">more information.</a></p>

  </div>
<?php
}