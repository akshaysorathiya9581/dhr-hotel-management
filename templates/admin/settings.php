<?php
/**
 * Settings template
 */

if (!defined('ABSPATH')) {
    exit;
}

$message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
$api_key = get_option('dhr_hotel_google_maps_api_key', '');
$location_heading = get_option('dhr_hotel_location_heading', 'LOCATED IN THE WESTERN CAPE');
$main_heading = get_option('dhr_hotel_main_heading', 'Find Us');
$description_text = get_option('dhr_hotel_description_text', 'Discover our hotel locations across the Western Cape. Click on any marker to view hotel details and make a reservation.');
$reservation_label = get_option('dhr_hotel_reservation_label', 'RESERVATION BY PHONE');
$reservation_phone = get_option('dhr_hotel_reservation_phone', '+27 (0)21 876 8900');
?>

<div class="wrap dhr-hotel-admin">
    <h1><?php _e('DHR Hotel Management Settings', 'dhr-hotel-management'); ?></h1>
    
    <?php if ($message === 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Settings saved successfully!', 'dhr-hotel-management'); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="dhr-hotel-form">
        <?php wp_nonce_field('dhr_settings_nonce'); ?>
        <input type="hidden" name="action" value="dhr_save_settings">
        
        <table class="form-table">
            <tr>
                <th><label for="google_maps_api_key"><?php _e('Google Maps API Key', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="text" id="google_maps_api_key" name="google_maps_api_key" 
                           class="regular-text" value="<?php echo esc_attr($api_key); ?>" 
                           placeholder="Enter your Google Maps API Key">
                    <p class="description">
                        <?php _e('Get your API key from', 'dhr-hotel-management'); ?> 
                        <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>.
                        <?php _e('Enable "Maps JavaScript API" for your project.', 'dhr-hotel-management'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th colspan="2"><h2><?php _e('Map Display Settings', 'dhr-hotel-management'); ?></h2></th>
            </tr>
            <tr>
                <th><label for="location_heading"><?php _e('Location Heading', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="text" id="location_heading" name="location_heading" 
                           class="regular-text" value="<?php echo esc_attr($location_heading); ?>" 
                           placeholder="LOCATED IN THE WESTERN CAPE">
                    <p class="description">
                        <?php _e('The small heading text displayed above the main heading (e.g., "LOCATED IN THE WESTERN CAPE").', 'dhr-hotel-management'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="main_heading"><?php _e('Main Heading', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="text" id="main_heading" name="main_heading" 
                           class="regular-text" value="<?php echo esc_attr($main_heading); ?>" 
                           placeholder="Find Us">
                    <p class="description">
                        <?php _e('The main heading text displayed on the map section (e.g., "Find Us").', 'dhr-hotel-management'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="description_text"><?php _e('Description Text', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <textarea id="description_text" name="description_text" 
                              class="large-text" rows="3" 
                              placeholder="Discover our hotel locations across the Western Cape. Click on any marker to view hotel details and make a reservation."><?php echo esc_textarea($description_text); ?></textarea>
                    <p class="description">
                        <?php _e('The descriptive text displayed below the main heading.', 'dhr-hotel-management'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="reservation_label"><?php _e('Reservation Label', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="text" id="reservation_label" name="reservation_label" 
                           class="regular-text" value="<?php echo esc_attr($reservation_label); ?>" 
                           placeholder="RESERVATION BY PHONE">
                    <p class="description">
                        <?php _e('The label text displayed above the phone number (e.g., "RESERVATION BY PHONE").', 'dhr-hotel-management'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="reservation_phone"><?php _e('Reservation Phone Number', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="text" id="reservation_phone" name="reservation_phone" 
                           class="regular-text" value="<?php echo esc_attr($reservation_phone); ?>" 
                           placeholder="+27 (0)21 876 8900">
                    <p class="description">
                        <?php _e('The phone number displayed for reservations. If left empty, the first hotel\'s phone number will be used.', 'dhr-hotel-management'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label><?php _e('Shortcode', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <div class="dhr-shortcode-wrapper">
                        <input type="text" id="dhr-shortcode-input" 
                               class="regular-text dhr-shortcode-input" 
                               value="[dhr_hotel_map]" 
                               readonly>
                        <button type="button" id="dhr-copy-shortcode-btn" 
                                class="button dhr-copy-btn" 
                                data-shortcode="[dhr_hotel_map]">
                            <span class="dhr-copy-text"><?php _e('Copy', 'dhr-hotel-management'); ?></span>
                            <span class="dhr-copied-text" style="display: none;"><?php _e('Copied!', 'dhr-hotel-management'); ?></span>
                        </button>
                    </div>
                    <p class="description">
                        <?php _e('Use this shortcode to display the hotel map on any page or post. You can also use attributes:', 'dhr-hotel-management'); ?>
                        <code>[dhr_hotel_map province="Western Cape"]</code>, 
                        <code>[dhr_hotel_map city="Cape Town"]</code>, 
                        <code>[dhr_hotel_map height="800px"]</code>
                    </p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" class="button button-primary" value="<?php _e('Save Settings', 'dhr-hotel-management'); ?>">
        </p>
    </form>
</div>


