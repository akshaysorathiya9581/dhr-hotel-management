<?php
/**
 * Hotel form template (add/edit)
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = $hotel !== null;
$title = $is_edit ? __('Edit Hotel', 'dhr-hotel-management') : __('Add New Hotel', 'dhr-hotel-management');
?>

<div class="wrap dhr-hotel-admin">
    <h1><?php echo $title; ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="dhr-hotel-form">
        <?php wp_nonce_field('dhr_hotel_nonce'); ?>
        <input type="hidden" name="action" value="dhr_save_hotel">
        <?php if ($is_edit): ?>
            <input type="hidden" name="hotel_id" value="<?php echo esc_attr($hotel->id); ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <th><label for="hotel_code"><?php _e('Hotel Code', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="text" id="hotel_code" name="hotel_code" class="regular-text"
                           value="<?php echo $is_edit ? esc_attr($hotel->hotel_code) : ''; ?>"
                           placeholder="<?php esc_attr_e('e.g. DRE013', 'dhr-hotel-management'); ?>"
                           <?php echo $is_edit ? ' readonly' : ''; ?>>
                    <?php if ($is_edit): ?>
                    <p class="description"><?php
                    if (!empty($hotel->manual_entry)) {
                        _e('Hotel code cannot be changed when editing. This hotel was added manually; SHR re-sync is not available for this row.', 'dhr-hotel-management');
                    } else {
                        _e('Hotel code cannot be changed when editing. Re-sync from the hotel list to update data from SHR.', 'dhr-hotel-management');
                    }
                    ?></p>
                    <?php else: ?>
                    <p class="description">
                        <?php _e('Optional code from the external CRS (e.g. SHR). This can be used for API-based sync.', 'dhr-hotel-management'); ?>
                    </p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="name"><?php _e('Hotel Name', 'dhr-hotel-management'); ?> <span class="required">*</span></label></th>
                <td>
                    <input type="text" id="name" name="name" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr(wp_unslash((string) ($hotel->name ?? ''))) : ''; ?>" required>
                </td>
            </tr>
            
            <tr>
                <th><label for="description"><?php _e('Description', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <textarea id="description" name="description" rows="5" class="large-text"><?php echo $is_edit ? esc_textarea(wp_unslash((string) ($hotel->description ?? ''))) : ''; ?></textarea>
                </td>
            </tr>
            
            <tr>
                <th><label for="address"><?php _e('Address', 'dhr-hotel-management'); ?> <span class="required">*</span></label></th>
                <td>
                    <input type="text" id="address" name="address" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr(wp_unslash((string) ($hotel->address ?? ''))) : ''; ?>" required>
                </td>
            </tr>
            
            <tr>
                <th><label for="city"><?php _e('City', 'dhr-hotel-management'); ?> <span class="required">*</span></label></th>
                <td>
                    <input type="text" id="city" name="city" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr(wp_unslash((string) ($hotel->city ?? ''))) : ''; ?>" required>
                </td>
            </tr>
            
            <tr>
                <th><label for="province"><?php _e('Province', 'dhr-hotel-management'); ?> <span class="required">*</span></label></th>
                <td>
                    <input type="text" id="province" name="province" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr(wp_unslash((string) ($hotel->province ?? ''))) : ''; ?>" required>
                </td>
            </tr>
            
            <tr>
                <th><label for="country"><?php _e('Country', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="text" id="country" name="country" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr(wp_unslash((string) ($hotel->country ?? 'South Africa'))) : 'South Africa'; ?>">
                </td>
            </tr>
            
            <tr>
                <th><label for="latitude"><?php _e('Latitude', 'dhr-hotel-management'); ?> <span class="required">*</span></label></th>
                <td>
                    <input type="text" id="latitude" name="latitude" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr($hotel->latitude) : ''; ?>" required>
                    <p class="description"><?php _e('Use Google Maps to find coordinates', 'dhr-hotel-management'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th><label for="longitude"><?php _e('Longitude', 'dhr-hotel-management'); ?> <span class="required">*</span></label></th>
                <td>
                    <input type="text" id="longitude" name="longitude" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr($hotel->longitude) : ''; ?>" required>
                </td>
            </tr>
            
            <tr>
                <th><label for="phone"><?php _e('Phone', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="text" id="phone" name="phone" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr($hotel->phone) : ''; ?>">
                </td>
            </tr>
            
            <tr>
                <th><label for="email"><?php _e('Email', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="email" id="email" name="email" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr($hotel->email) : ''; ?>">
                </td>
            </tr>
            
            <tr>
                <th><label for="website"><?php _e('Website', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="url" id="website" name="website" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr($hotel->website) : ''; ?>">
                </td>
            </tr>
            
            <tr>
                <th>
                    <label for="image_url">
                        <?php _e('Hotel Image (main photo)', 'dhr-hotel-management'); ?>
                    </label>
                </th>
                <td>
                    <input type="url"
                           id="image_url"
                           name="image_url"
                           class="regular-text"
                           value="<?php echo $is_edit ? esc_attr($hotel->image_url) : ''; ?>">
                    <button type="button" class="button" id="upload-image-btn">
                        <?php _e('Upload Image', 'dhr-hotel-management'); ?>
                    </button>
                    <button type="button" class="button" id="remove-image-btn">
                        <?php _e('Remove Image', 'dhr-hotel-management'); ?>
                    </button>
                    <?php
                    $image_preview_url = ($is_edit && !empty($hotel->image_url)) ? $hotel->image_url : '';
                    ?>
                    <div class="dhr-hotel-media-preview dhr-hotel-image-preview" id="image_url_preview_wrap"<?php echo $image_preview_url ? '' : ' hidden'; ?>>
                        <span class="dhr-hotel-media-preview-label"><?php _e('Preview', 'dhr-hotel-management'); ?></span>
                        <img id="image_url_preview"
                             src="<?php echo $image_preview_url ? esc_url($image_preview_url) : ''; ?>"
                             alt=""
                             decoding="async"
                             loading="lazy">
                    </div>
                    <p class="description">
                        <?php _e('Used as the large photo/background for this hotel (cards, panels, etc.).', 'dhr-hotel-management'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th>
                    <label for="logo_url">
                        <?php _e('Hotel Logo (small)', 'dhr-hotel-management'); ?>
                    </label>
                </th>
                <td>
                    <input type="url"
                           id="logo_url"
                           name="logo_url"
                           class="regular-text"
                           value="<?php echo $is_edit && !empty($hotel->logo_url) ? esc_attr($hotel->logo_url) : ''; ?>">
                    <button type="button" class="button" id="upload-logo-btn">
                        <?php _e('Upload Logo', 'dhr-hotel-management'); ?>
                    </button>
                    <button type="button" class="button" id="remove-logo-btn">
                        <?php _e('Remove Logo', 'dhr-hotel-management'); ?>
                    </button>
                    <?php
                    $logo_preview_url = ($is_edit && !empty($hotel->logo_url)) ? $hotel->logo_url : '';
                    ?>
                    <div class="dhr-hotel-media-preview dhr-hotel-logo-preview" id="logo_url_preview_wrap"<?php echo $logo_preview_url ? '' : ' hidden'; ?>>
                        <span class="dhr-hotel-media-preview-label"><?php _e('Preview', 'dhr-hotel-management'); ?></span>
                        <img id="logo_url_preview"
                             src="<?php echo $logo_preview_url ? esc_url($logo_preview_url) : ''; ?>"
                             alt=""
                             decoding="async"
                             loading="lazy">
                    </div>
                    <p class="description">
                        <?php _e('Used as the small logo on maps (for example, in the Where To Find Us logos bar). If empty, the main image may be used instead.', 'dhr-hotel-management'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th><label for="google_maps_url"><?php _e('Google Maps URL', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <input type="url" id="google_maps_url" name="google_maps_url" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr($hotel->google_maps_url) : ''; ?>">
                </td>
            </tr>
            
            <tr>
                <th><label for="status"><?php _e('Status', 'dhr-hotel-management'); ?></label></th>
                <td>
                    <select id="status" name="status">
                        <option value="active" <?php echo (!$is_edit || (isset($hotel->status) && $hotel->status === 'active')) ? 'selected' : ''; ?>>
                            <?php _e('Active', 'dhr-hotel-management'); ?>
                        </option>
                        <option value="inactive" <?php echo ($is_edit && isset($hotel->status) && $hotel->status === 'inactive') ? 'selected' : ''; ?>>
                            <?php _e('Inactive', 'dhr-hotel-management'); ?>
                        </option>
                    </select>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" class="button button-primary" value="<?php echo $is_edit ? __('Update Hotel', 'dhr-hotel-management') : __('Add Hotel', 'dhr-hotel-management'); ?>">
            <a href="<?php echo admin_url('admin.php?page=dhr-hotel-management'); ?>" class="button">
                <?php _e('Cancel', 'dhr-hotel-management'); ?>
            </a>
        </p>
    </form>
</div>


