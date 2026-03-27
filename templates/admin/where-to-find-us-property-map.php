<?php
/**
 * Property-wise Where To Find Us Map settings.
 */

if (!defined('ABSPATH')) {
    exit;
}

$property_image = isset($saved_data['property_image']) ? $saved_data['property_image'] : '';
$property_logo_image = isset($saved_data['property_logo_image']) ? $saved_data['property_logo_image'] : '';
$latitude = isset($saved_data['latitude']) ? $saved_data['latitude'] : '';
$longitude = isset($saved_data['longitude']) ? $saved_data['longitude'] : '';
$main_heading = isset($saved_data['main_heading']) ? $saved_data['main_heading'] : 'Where To Find Us';
$address_text = isset($saved_data['address_text']) ? $saved_data['address_text'] : '';
$phone_label = isset($saved_data['phone_label']) ? $saved_data['phone_label'] : '';
$phone_number = isset($saved_data['phone_number']) ? $saved_data['phone_number'] : '';
$email_address = isset($saved_data['email_address']) ? $saved_data['email_address'] : '';
$enquire_text = isset($saved_data['enquire_text']) ? $saved_data['enquire_text'] : 'Enquire now';
$enquire_url = isset($saved_data['enquire_url']) ? $saved_data['enquire_url'] : '';
$google_maps_url = isset($saved_data['google_maps_url']) ? $saved_data['google_maps_url'] : '';
$google_maps_button_text = isset($saved_data['google_maps_button_text']) ? $saved_data['google_maps_button_text'] : 'Google Maps';
?>

<div class="wrap dhr-hotel-admin">
    <h1><?php esc_html_e('Where To Find Us Property Map', 'dhr-hotel-management'); ?></h1>
    <p class="description"><?php esc_html_e('Save map/contact details for each property (post type: properties).', 'dhr-hotel-management'); ?></p>
    <div class="dhr-shortcode-wrapper" style="margin: 12px 0 18px;">
        <input type="text" class="dhr-shortcode-input" value="[dhr_where_to_find_us_map]" readonly>
        <button type="button" class="button dhr-copy-btn" data-shortcode="[dhr_where_to_find_us_map]">
            <span class="dhr-copy-text"><?php esc_html_e('Copy', 'dhr-hotel-management'); ?></span>
            <span class="dhr-copied-text" style="display:none;"><?php esc_html_e('Copied!', 'dhr-hotel-management'); ?></span>
        </button>
    </div>

    <?php if ($message === 'saved'): ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Property map settings saved.', 'dhr-hotel-management'); ?></p></div>
    <?php elseif ($message === 'deleted'): ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Property map settings removed.', 'dhr-hotel-management'); ?></p></div>
    <?php elseif ($message === 'error'): ?>
        <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Please select a property.', 'dhr-hotel-management'); ?></p></div>
    <?php endif; ?>

    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="margin:16px 0;">
        <input type="hidden" name="page" value="dhr-where-to-find-us-property-map">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="dhr-property-id"><?php esc_html_e('Property List', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <select name="property_id" id="dhr-property-id" class="regular-text" onchange="this.form.submit()">
                        <option value=""><?php esc_html_e('Select Property', 'dhr-hotel-management'); ?></option>
                        <?php foreach ((array) $properties as $property): ?>
                            <option value="<?php echo esc_attr((int) $property->ID); ?>" <?php selected((int) $selected_property_id, (int) $property->ID); ?>>
                                <?php echo esc_html(get_the_title($property->ID)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
    </form>

    <?php if ((int) $selected_property_id > 0): ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('dhr_where_to_find_us_property_map_nonce'); ?>
            <input type="hidden" name="action" value="dhr_save_where_to_find_us_property_map">
            <input type="hidden" name="property_id" value="<?php echo esc_attr((int) $selected_property_id); ?>">

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="property_image"><?php esc_html_e('Property Image', 'dhr-hotel-management'); ?></label></th>
                    <td>
                        <input type="url" id="property_image" name="property_image" class="regular-text" value="<?php echo esc_attr($property_image); ?>">
                        <button type="button" class="button" id="upload-property-image-btn" style="margin-left: 6px;"><?php esc_html_e('Choose Image', 'dhr-hotel-management'); ?></button>
                        <button type="button" class="button" id="remove-property-image-btn"><?php esc_html_e('Remove', 'dhr-hotel-management'); ?></button>
                        <div id="property_image_preview_wrap" style="margin-top:10px;<?php echo empty($property_image) ? 'display:none;' : ''; ?>">
                            <img id="property_image_preview" src="<?php echo esc_url($property_image); ?>" alt="" style="max-width:240px;height:auto;border:1px solid #ccd0d4;padding:4px;background:#fff;">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="property_logo_image"><?php esc_html_e('Property Logo Image', 'dhr-hotel-management'); ?></label></th>
                    <td>
                        <input type="url" id="property_logo_image" name="property_logo_image" class="regular-text" value="<?php echo esc_attr($property_logo_image); ?>">
                        <button type="button" class="button" id="upload-property-logo-image-btn" style="margin-left: 6px;"><?php esc_html_e('Choose Image', 'dhr-hotel-management'); ?></button>
                        <button type="button" class="button" id="remove-property-logo-image-btn"><?php esc_html_e('Remove', 'dhr-hotel-management'); ?></button>
                        <div id="property_logo_image_preview_wrap" style="margin-top:10px;<?php echo empty($property_logo_image) ? 'display:none;' : ''; ?>">
                            <img id="property_logo_image_preview" src="<?php echo esc_url($property_logo_image); ?>" alt="" style="max-width:180px;height:auto;border:1px solid #ccd0d4;padding:4px;background:#fff;">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="latitude"><?php esc_html_e('Latitude', 'dhr-hotel-management'); ?></label></th>
                    <td><input type="text" id="latitude" name="latitude" class="regular-text" value="<?php echo esc_attr($latitude); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="longitude"><?php esc_html_e('Longitude', 'dhr-hotel-management'); ?></label></th>
                    <td><input type="text" id="longitude" name="longitude" class="regular-text" value="<?php echo esc_attr($longitude); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="google_maps_url"><?php esc_html_e('Google Maps URL', 'dhr-hotel-management'); ?></label></th>
                    <td>
                        <input type="url" id="google_maps_url" name="google_maps_url" class="large-text" value="<?php echo esc_attr($google_maps_url); ?>" placeholder="https://maps.google.com/...">
                        <p class="description"><?php esc_html_e('Optional. Overrides the default link built from latitude and longitude.', 'dhr-hotel-management'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="google_maps_button_text"><?php esc_html_e('Google Maps Button Text', 'dhr-hotel-management'); ?></label></th>
                    <td><input type="text" id="google_maps_button_text" name="google_maps_button_text" class="regular-text" value="<?php echo esc_attr($google_maps_button_text); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="main_heading"><?php esc_html_e('Main Heading', 'dhr-hotel-management'); ?></label></th>
                    <td><input type="text" id="main_heading" name="main_heading" class="regular-text" value="<?php echo esc_attr($main_heading); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="address_text"><?php esc_html_e('Address Text', 'dhr-hotel-management'); ?></label></th>
                    <td><textarea id="address_text" name="address_text" class="large-text" rows="3"><?php echo esc_textarea($address_text); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="phone_label"><?php esc_html_e('Phone Label', 'dhr-hotel-management'); ?></label></th>
                    <td><input type="text" id="phone_label" name="phone_label" class="regular-text" value="<?php echo esc_attr($phone_label); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="phone_number"><?php esc_html_e('Phone Number', 'dhr-hotel-management'); ?></label></th>
                    <td><input type="text" id="phone_number" name="phone_number" class="regular-text" value="<?php echo esc_attr($phone_number); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="email_address"><?php esc_html_e('Email Address', 'dhr-hotel-management'); ?></label></th>
                    <td><input type="email" id="email_address" name="email_address" class="regular-text" value="<?php echo esc_attr($email_address); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="enquire_text"><?php esc_html_e('Enquire Text', 'dhr-hotel-management'); ?></label></th>
                    <td><input type="text" id="enquire_text" name="enquire_text" class="regular-text" value="<?php echo esc_attr($enquire_text); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="enquire_url"><?php esc_html_e('Enquire Button URL', 'dhr-hotel-management'); ?></label></th>
                    <td>
                        <input type="text" id="enquire_url" name="enquire_url" class="large-text" value="<?php echo esc_attr($enquire_url); ?>" placeholder="https://... or mailto:...">
                        <p class="description"><?php esc_html_e('URL for the Enquire button (e.g. contact page, booking form, or mailto link).', 'dhr-hotel-management'); ?></p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'dhr-hotel-management'); ?></button>
            </p>
        </form>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to remove saved settings for this property?', 'dhr-hotel-management')); ?>');" style="margin-top: 8px;">
            <?php wp_nonce_field('dhr_where_to_find_us_property_map_delete_nonce'); ?>
            <input type="hidden" name="action" value="dhr_delete_where_to_find_us_property_map">
            <input type="hidden" name="property_id" value="<?php echo esc_attr((int) $selected_property_id); ?>">
            <button type="submit" class="button button-secondary"><?php esc_html_e('Remove Settings', 'dhr-hotel-management'); ?></button>
        </form>
    <?php endif; ?>
</div>
