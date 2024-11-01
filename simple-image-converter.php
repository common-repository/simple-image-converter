<?php
/**
 * @package simple-image-Converter
 */
/*
Plugin Name: Simple Image Converter
Description: This plugin will compress images and convert them to WebP format automatically when uploaded to the media library.
Version: 1.0.0
Author: Guru Web Agency
Author URI: https://guruwebagency.se
License: GPLv2 or later
Text-domain: Simple Image Converter
 */

/*
Simple Image Converter is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Simple Image Converter is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Simple Image Converter. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/

defined( 'ABSPATH' ) or die( 'Hey silly, you cant access this file' );

// Add a filter to WordPress to run our function when an image is uploaded
add_filter('wp_handle_upload', 'compress_and_convert_images');

// Add a settings page to WordPress to allow the user to choose the image quality
add_action('admin_menu', 'add_image_Converter_settings_page');
add_action('admin_init', 'register_image_Converter_settings');

function add_image_Converter_settings_page() {
  add_options_page('Image Converter Settings', 'Image Converter', 'manage_options', 'image-Converter-settings', 'image_Converter_settings_page');
}

function register_image_Converter_settings() {
  add_settings_section('image_Converter_settings', 'Image Converter Settings', 'image_Converter_settings_section', 'image-Converter-settings');
  add_settings_field('image_Converter_quality', 'Image Quality', 'image_Converter_quality_field', 'image-Converter-settings', 'image_Converter_settings');
  register_setting('image_Converter_settings', 'image_Converter_quality');
}

function image_Converter_settings_page() {
?>
  <div class="wrap">
    <h1>Image Converter Settings</h1>
    <form method="post" action="options.php">
      <?php settings_fields('image_Converter_settings'); ?>
      <?php do_settings_sections('image-Converter-settings'); ?>
      <?php submit_button(); ?>
    </form>
  </div>
<?php
}

function image_Converter_settings_section() {
  echo '<p>Choose the quality of the compressed and converted images.</p>';
}

function image_Converter_quality_field() {
  $quality = get_option('image_Converter_quality', 80);
  echo '<input type="number" name="image_Converter_quality" min="1" max="100" value="' . esc_attr($quality) . '">';
}

function compress_and_convert_images($upload) {
  $image_path = $upload['file'];
  $image_mime = $upload['type'];
  $quality = get_option('image_Converter_quality', 80);

  // Open the uploaded image using the GD library
  if ($image_mime == 'image/jpeg') {
    $image = imagecreatefromjpeg($image_path);
  } elseif ($image_mime == 'image/png') {
    $image = imagecreatefrompng($image_path);
  } elseif ($image_mime == 'image/gif') {
    $image = imagecreatefromgif($image_path);
  } else {
    $image = false;
  }

  // If the image was successfully opened, compress and convert it to WebP format
  if ($image) {
    // Compress the image using the specified quality
    imagejpeg($image, $image_path, $quality);

    // Convert the image to WebP format
    imagewebp($image, $image_path . '.webp', $quality);

    // Remove the original uncompressed image file
    unlink($image_path);

    // Update the upload array to include the new WebP format image
    $upload['file'] = $image_path . '.webp';
    $upload['type'] = 'image/webp';
  }

  // Return the updated upload array
  return $upload;
}
?>