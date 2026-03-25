<?php
/**
 * Hotels list template
 */

if (!defined('ABSPATH')) {
    exit;
}

$message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
$error_detail = isset($_GET['error']) ? sanitize_text_field(wp_unslash($_GET['error'])) : '';
$messages = array(
    'added' => array('type' => 'success', 'text' => __('Hotel added successfully!', 'dhr-hotel-management')),
    'updated' => array('type' => 'success', 'text' => __('Hotel updated successfully!', 'dhr-hotel-management')),
    'deleted' => array('type' => 'success', 'text' => __('Hotel deleted successfully!', 'dhr-hotel-management')),
    'error' => array('type' => 'error', 'text' => $error_detail ? $error_detail : __('An error occurred. Please try again.', 'dhr-hotel-management'))
);
?>

<div class="wrap dhr-hotel-admin">
    <h1 class="wp-heading-inline"><?php _e('DHR Hotel Management', 'dhr-hotel-management'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=dhr-hotel-management&action=add')); ?>"
       class="page-title-action"><?php _e('Add New Hotel', 'dhr-hotel-management'); ?></a>
    <hr class="wp-header-end">
    
    <?php if ($message && isset($messages[$message])): ?>
        <div class="notice notice-<?php echo esc_attr($messages[$message]['type']); ?> is-dismissible">
            <p><?php echo esc_html($messages[$message]['text']); ?></p>
        </div>
    <?php endif; ?>
    
    <p style="margin-top: 15px; max-width: 600px;">
        <?php _e('Add a new hotel from an external CRS (such as SHR) by entering its hotel code in the sync field below.', 'dhr-hotel-management'); ?>
    </p>

    <form id="dhr-shr-sync-hotel-list-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="margin: 20px 0;">
        <?php wp_nonce_field('dhr_sync_shr_hotel_nonce'); ?>
        <input type="hidden" name="action" value="dhr_sync_shr_hotel">
        <table class="form-table">
            <tr>
                <th style="width: 160px;">
                    <label for="dhr_shr_sync_hotel_code"><?php _e('Sync New Hotel by Code', 'dhr-hotel-management'); ?></label>
                </th>
                <td>
                    <input type="text"
                           id="dhr_shr_sync_hotel_code"
                           name="hotel_code"
                           class="regular-text"
                           placeholder="<?php esc_attr_e('e.g. DRE013', 'dhr-hotel-management'); ?>">
                    <button type="submit"
                            class="button button-secondary"
                            id="dhr-shr-sync-hotel-list-btn"
                            style="margin-left: 10px;">
                        <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                        <?php _e('Sync & Add Hotel', 'dhr-hotel-management'); ?>
                    </button>
                </td>
            </tr>
        </table>
    </form>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID', 'dhr-hotel-management'); ?></th>
                <th><?php _e('Image', 'dhr-hotel-management'); ?></th>
                <th><?php _e('Hotel Code', 'dhr-hotel-management'); ?></th>
                <th><?php _e('Name', 'dhr-hotel-management'); ?></th>
                <th><?php _e('City', 'dhr-hotel-management'); ?></th>
                <th><?php _e('Province', 'dhr-hotel-management'); ?></th>
                <th><?php _e('Latitude', 'dhr-hotel-management'); ?></th>
                <th><?php _e('Longitude', 'dhr-hotel-management'); ?></th>
                <th><?php _e('Phone', 'dhr-hotel-management'); ?></th>
                <th><?php _e('Status', 'dhr-hotel-management'); ?></th>
                <th><?php _e('Actions', 'dhr-hotel-management'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($hotels)): ?>
                <tr>
                    <td colspan="11" style="text-align: center;">
                        <?php _e('No hotels found. Add your first hotel!', 'dhr-hotel-management'); ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($hotels as $hotel): ?>
                    <tr>
                        <td><?php echo esc_html($hotel->id); ?></td>
                        <td>
                            <?php
                            $img_url = !empty($hotel->image_url) ? esc_url($hotel->image_url) : '';
                            if ($img_url): ?>
                                <img src="<?php echo $img_url; ?>" alt="" class="dhr-hotel-list-thumb" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; display: block;">
                            <?php else: ?>
                                <span class="dhr-hotel-list-no-image" style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: #f0f0f1; color: #787c82; border-radius: 4px; font-size: 11px; text-align: center;"><?php _e('No image', 'dhr-hotel-management'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo !empty($hotel->hotel_code) ? esc_html($hotel->hotel_code) : '&ndash;'; ?></td>
                        <td><strong><?php echo esc_html($hotel->name); ?></strong></td>
                        <td><?php echo esc_html($hotel->city); ?></td>
                        <td><?php echo esc_html($hotel->province); ?></td>
                        <td><?php echo isset($hotel->latitude) ? esc_html($hotel->latitude) : '&ndash;'; ?></td>
                        <td><?php echo isset($hotel->longitude) ? esc_html($hotel->longitude) : '&ndash;'; ?></td>
                        <td><?php echo esc_html($hotel->phone); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($hotel->status); ?>">
                                <?php echo esc_html(ucfirst($hotel->status)); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $is_manual_hotel = isset($hotel->manual_entry) && (int) $hotel->manual_entry === 1;
                            if (!empty($hotel->hotel_code) && !$is_manual_hotel) :
                            ?>
                            <button type="button" class="button button-small dhr-row-sync-btn" data-hotel-code="<?php echo esc_attr($hotel->hotel_code); ?>" title="<?php esc_attr_e('Re-sync from SHR', 'dhr-hotel-management'); ?>">
                                <span class="dashicons dashicons-update" style="vertical-align: middle;"></span> <?php _e('Sync', 'dhr-hotel-management'); ?>
                            </button>
                            <?php endif; ?>
                            <a href="<?php echo admin_url('admin.php?page=dhr-hotel-management&action=edit&hotel_id=' . $hotel->id); ?>" 
                               class="button button-small">
                                <?php _e('Edit', 'dhr-hotel-management'); ?>
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=dhr_delete_hotel&hotel_id=' . $hotel->id), 'dhr_delete_hotel_nonce'); ?>" 
                               class="button button-small button-link-delete"
                               onclick="return confirm('<?php _e('Are you sure you want to delete this hotel?', 'dhr-hotel-management'); ?>');">
                                <?php _e('Delete', 'dhr-hotel-management'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
    $show_response = current_user_can('manage_options') && isset($_GET['dhr_show_response']) && $_GET['dhr_show_response'] === '1';
    $show_room_response = current_user_can('manage_options') && isset($_GET['dhr_show_room_response']) && $_GET['dhr_show_room_response'] === '1';
    $last_response = $show_response ? get_transient('dhr_last_shr_api_response') : false;
    $last_room_response = $show_room_response ? get_transient('dhr_last_shr_room_api_response') : false;
    ?>
    <p style="margin-top: 24px;">
        <a href="<?php echo esc_url(admin_url('admin.php?page=dhr-hotel-management&dhr_show_response=1')); ?>" class="button button-small">
            <?php _e('View last hotel API response', 'dhr-hotel-management'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=dhr-hotel-management&dhr_show_room_response=1')); ?>" class="button button-small" style="margin-left: 8px;">
            <?php _e('View last room API response', 'dhr-hotel-management'); ?>
        </a>
    </p>
    <?php if ($show_response && $last_response): ?>
        <div class="dhr-api-response-box" style="margin-top: 16px; border: 1px solid #c3c4c7; background: #f6f7f7; padding: 12px; max-height: 70vh; overflow: auto;">
            <h3 style="margin-top: 0;">
                <?php
                printf(
                    __('Last SHR API response (hotel: %s, at %s)', 'dhr-hotel-management'),
                    esc_html($last_response['hotel_code']),
                    esc_html($last_response['at'])
                );
                ?>
            </h3>
            <pre style="white-space: pre-wrap; word-break: break-all; margin: 0; font-size: 12px;"><?php echo esc_html(json_encode($last_response['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></pre>
        </div>
    <?php elseif ($show_response && !$last_response): ?>
        <p style="margin-top: 16px; color: #646970;"><?php _e('No API response stored yet. Sync a hotel to capture the response.', 'dhr-hotel-management'); ?></p>
    <?php endif; ?>

    <?php if ($show_room_response && $last_room_response): ?>
        <div class="dhr-api-response-box" style="margin-top: 16px; border: 1px solid #c3c4c7; background: #f6f7f7; padding: 12px; max-height: 70vh; overflow: auto;">
            <h3 style="margin-top: 0;">
                <?php
                printf(
                    __('Last SHR room API response (hotel: %s, at %s)', 'dhr-hotel-management'),
                    esc_html($last_room_response['hotel_code']),
                    esc_html($last_room_response['at'])
                );
                ?>
            </h3>
            <pre style="white-space: pre-wrap; word-break: break-all; margin: 0; font-size: 12px;"><?php echo esc_html(json_encode($last_room_response['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></pre>
        </div>
    <?php elseif ($show_room_response && !$last_room_response): ?>
        <p style="margin-top: 16px; color: #646970;"><?php _e('No room API response stored yet. Set the hotel code in Book Your Stay → Settings, then load a page with [hotel_rooms] or [hotel_rooms_second] to capture the response.', 'dhr-hotel-management'); ?></p>
    <?php endif; ?>
</div>


