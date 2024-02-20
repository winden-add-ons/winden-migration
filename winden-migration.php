<?php
/*
 * Plugin Name:       Winden Migration from v1.x.x to v2.x.x
 * Plugin URI:        https://example.com/plugins/tailwind-cdn-for-gutenberg/
 * Description:       Simplified version to handle data migration in the database.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            DPlugins
 * Author URI:        https://dplugins.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/tailwind-cdn-for-gutenberg/
 * Text Domain:       tailwind-cdn-for-gutenberg
 * Domain Path:       /languages
 */

// Function to add the menu for Winden Migration
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

// Function to display the settings page for migration
function winden_migration_settings_page()
{
  // Handle the 'Migrate' button click within the settings page
  if (isset($_POST['migrate'])) {
    if (!wp_verify_nonce($_POST['winden_migration_nonce'], 'winden_migration_action')) {
      die('Security check failed.');
    }

    // Fetch existing CSS and Config data from options
    $css_value_encoded = get_option('wakaloka_winden_global_css', '');
    $config_value_encoded = get_option('wakaloka_winden_tailwind_config_cdn', '');
    $license_key = get_option('wakaloka_winden_license_key', '');

    // Decode the values if they're stored in base64 or another encoding format
    $css_value = base64_decode($css_value_encoded);
    $config_value = base64_decode($config_value_encoded);

    $config_value = str_replace('module.exports =', 'export default', $config_value);

    // Initialize or retrieve the existing 'winden_editor' option
    $winden_editor = get_option('winden_editor', array());

    // Update the 'winden_editor' option with new CSS and Config data under specified indexes
    $winden_editor[0] = [
      'name' => 'input.css',
      'content' => $css_value,
      'language' => 'css',
      'supportLanguages' => ['css', 'scss'],
    ];

    $winden_editor[1] = [
      'name' => 'tailwind.config.js',
      'content' => $config_value,
      'language' => 'javascript',
      'supportLanguages' => ['javascript'],
    ];

    // Save the updated 'winden_editor' option back to the database
    update_option('winden_editor', $winden_editor);

    // Prepare and save the license information
    $winden_license = [
      'key' => $license_key,
      'status' => 'activated', // Assuming the status is activated. Adjust as necessary.
      'checkedAt' => current_time('c') // Use the current time in ISO 8601 format
    ];

    // Save the updated 'winden_license' option back to the database
    update_option('winden_license', $winden_license);

    echo '<div class="notice notice-success is-dismissible"><p>Data migrated successfully to CSS Tab, Config Tab, and Winden License.</p></div>';
  }

  // Render the settings page
  ?>
  <div class="wrap">
    <h1><?php _e('Winden Migration Settings', 'tailwind-cdn-for-gutenberg'); ?></h1>

    <form method="post">
      <?php wp_nonce_field('winden_migration_action', 'winden_migration_nonce'); ?>
      <input type="submit" name="migrate" value="Migrate Data" class="button button-primary">
    </form>

    <p>This migration will transfer your CSS, configuration data, and license key from old options to new options for better compatibility and organization.</p>
  </div>
  <?php
}