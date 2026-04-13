<?php
/**
 * Admin functionality for DHR Hotel Management
 */

if (!defined('ABSPATH')) {
    exit;
}

class DHR_Hotel_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_post_dhr_save_hotel', array($this, 'save_hotel'));
        add_action('admin_post_dhr_delete_hotel', array($this, 'delete_hotel'));
        add_action('admin_post_dhr_save_settings', array($this, 'save_settings'));
        add_action('admin_post_dhr_save_map_config', array($this, 'save_map_config'));
        add_action('admin_post_dhr_create_default_maps', array($this, 'create_default_maps'));
        add_action('admin_post_dhr_save_category', array($this, 'save_category'));
        add_action('admin_post_dhr_delete_category', array($this, 'delete_category'));
        add_action('admin_post_dhr_save_package', array($this, 'save_package'));
        add_action('admin_post_dhr_delete_package', array($this, 'delete_package'));
        add_action('admin_post_dhr_save_where_to_find_us_property_map', array($this, 'save_where_to_find_us_property_map'));
        add_action('admin_post_dhr_delete_where_to_find_us_property_map', array($this, 'delete_where_to_find_us_property_map'));
        add_action('admin_post_dhr_save_room_settings', array($this, 'save_room_settings'));

        // SHR WS Shop API (REST) sync actions
        add_action('admin_post_dhr_sync_shr_hotel', array($this, 'sync_shr_hotel'));
        add_action('wp_ajax_dhr_sync_shr_hotel_ajax', array($this, 'sync_shr_hotel_ajax'));

        add_filter('upload_mimes', array($this, 'allow_svg_upload'));
        add_filter('wp_check_filetype_and_ext', array($this, 'fix_svg_filetype'), 10, 5);
    }

    public function allow_svg_upload($mimes) {
        $mimes['svg']  = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        return $mimes;
    }

    public function fix_svg_filetype($data, $file, $filename, $mimes, $real_mime = '') {
        if (!empty($data['ext']) && !empty($data['type'])) {
            return $data;
        }
        $filetype = wp_check_filetype($filename, array(
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
        ));
        if ($filetype['ext']) {
            $data['ext']             = $filetype['ext'];
            $data['type']            = $filetype['type'];
            $data['proper_filename'] = $filename;
        }
        return $data;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('DHR Hotel Management', 'dhr-hotel-management'),
            __('DHR Hotel Management', 'dhr-hotel-management'),
            'manage_options',
            'dhr-hotel-management',
            array($this, 'display_hotels_list'),
            'dashicons-location-alt',
            30
        );
        
        add_submenu_page(
            'dhr-hotel-management',
            __('All Hotels', 'dhr-hotel-management'),
            __('All Hotels', 'dhr-hotel-management'),
            'manage_options',
            'dhr-hotel-management',
            array($this, 'display_hotels_list')
        );
        
        add_submenu_page(
            'dhr-hotel-management',
            __('Settings', 'dhr-hotel-management'),
            __('Settings', 'dhr-hotel-management'),
            'manage_options',
            'dhr-hotel-settings',
            array($this, 'display_settings')
        );
        
        add_submenu_page(
            'dhr-hotel-management',
            __('Map Management', 'dhr-hotel-management'),
            __('Map Management', 'dhr-hotel-management'),
            'manage_options',
            'dhr-hotel-map-management',
            array($this, 'display_map_management')
        );
        add_submenu_page(
            'dhr-hotel-management',
            __('Category List', 'dhr-hotel-management'),
            __('Category List', 'dhr-hotel-management'),
            'manage_options',
            'dhr-hotel-categories',
            array($this, 'display_category_list')
        );
        add_submenu_page(
            'dhr-hotel-management',
            __('Package List', 'dhr-hotel-management'),
            __('Package List', 'dhr-hotel-management'),
            'manage_options',
            'dhr-hotel-packages',
            array($this, 'display_package_list')
        );
        add_submenu_page(
            'dhr-hotel-management',
            __('Package Settings', 'dhr-hotel-management'),
            __('Package Settings', 'dhr-hotel-management'),
            'manage_options',
            'dhr-hotel-package-settings',
            array($this, 'display_package_settings')
        );
        add_submenu_page(
            'dhr-hotel-management',
            __('Room Settings', 'dhr-hotel-management'),
            __('Room Settings', 'dhr-hotel-management'),
            'manage_options',
            'dhr-hotel-room-settings',
            array($this, 'display_room_settings')
        );
        add_submenu_page(
            'dhr-hotel-management',
            __('Where To Find Us Property Map', 'dhr-hotel-management'),
            __('Where To Find Us Property Map', 'dhr-hotel-management'),
            'manage_options',
            'dhr-where-to-find-us-property-map',
            array($this, 'display_where_to_find_us_property_map')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'dhr-hotel') === false) {
            return;
        }
        
        wp_enqueue_style(
            'dhr-hotel-admin-style',
            DHR_HOTEL_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            DHR_HOTEL_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'dhr-hotel-admin-script',
            DHR_HOTEL_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery'),
            DHR_HOTEL_PLUGIN_VERSION,
            true
        );
        
        // Localize script for AJAX and redirect URLs (for WordPress-style notices)
        wp_localize_script('dhr-hotel-admin-script', 'dhrHotelAdmin', array(
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'listUrl'   => admin_url('admin.php?page=dhr-hotel-management'),
            'shrSyncNonce' => wp_create_nonce('dhr_sync_shr_hotel_ajax_nonce')
        ));
        
        // Enqueue media uploader
        wp_enqueue_media();
    }
    
    /**
     * Display hotels list
     */
    public function display_hotels_list() {
        $hotels = DHR_Hotel_Database::get_all_hotels();
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
        
        if ($action === 'add') {
            $this->display_hotel_form(0);
            return;
        }
        
        if ($action === 'edit' && $hotel_id > 0) {
            $this->display_hotel_form($hotel_id);
            return;
        }
        
        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/hotels-list.php';
    }
    
    /**
     * Display hotel form (add or edit).
     *
     * @param int $hotel_id 0 = add new hotel; positive = edit existing.
     */
    public function display_hotel_form($hotel_id = 0) {
        if ($hotel_id <= 0) {
            $hotel = null;
            include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/hotel-form.php';
            return;
        }
        $hotel = DHR_Hotel_Database::get_hotel($hotel_id);
        if (!$hotel) {
            wp_die(__('Hotel not found.', 'dhr-hotel-management'));
        }
        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/hotel-form.php';
    }
    
    /**
     * Save hotel (add or update)
     */
    public function save_hotel() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        check_admin_referer('dhr_hotel_nonce');
        
        $hotel_id = isset($_POST['hotel_id']) ? intval($_POST['hotel_id']) : 0;
        
        $data = array(
            'hotel_code'      => isset($_POST['hotel_code']) ? sanitize_text_field(wp_unslash($_POST['hotel_code'])) : '',
            'name'            => isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '',
            'description'     => isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '',
            'address'         => isset($_POST['address']) ? sanitize_text_field(wp_unslash($_POST['address'])) : '',
            'city'            => isset($_POST['city']) ? sanitize_text_field(wp_unslash($_POST['city'])) : '',
            'province'        => isset($_POST['province']) ? sanitize_text_field(wp_unslash($_POST['province'])) : '',
            'country'         => isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : 'South Africa',
            'latitude'        => isset($_POST['latitude']) ? sanitize_text_field(wp_unslash($_POST['latitude'])) : '',
            'longitude'       => isset($_POST['longitude']) ? sanitize_text_field(wp_unslash($_POST['longitude'])) : '',
            'phone'           => isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '',
            'email'           => isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '',
            'website'         => isset($_POST['website']) ? esc_url_raw(wp_unslash($_POST['website'])) : '',
            'image_url'       => isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '',
            'logo_url'        => isset($_POST['logo_url']) ? esc_url_raw($_POST['logo_url']) : '',
            'google_maps_url' => isset($_POST['google_maps_url']) ? esc_url_raw($_POST['google_maps_url']) : '',
            'status'          => isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : 'active'
        );
        
        if ($hotel_id <= 0) {
            if (!empty($data['hotel_code']) && DHR_Hotel_Database::get_hotel_by_code($data['hotel_code'])) {
                $error_param = urlencode(__('A hotel with this code already exists.', 'dhr-hotel-management'));
                wp_safe_redirect(admin_url('admin.php?page=dhr-hotel-management&message=error&error=' . $error_param));
                exit;
            }
            $data['manual_entry'] = 1;
            $new_id = DHR_Hotel_Database::insert_hotel($data);
            $message = $new_id ? 'added' : 'error';
            wp_safe_redirect(admin_url('admin.php?page=dhr-hotel-management&message=' . $message));
            exit;
        }
        
        $result = DHR_Hotel_Database::update_hotel($hotel_id, $data);
        $message = $result ? 'updated' : 'error';
        wp_redirect(admin_url('admin.php?page=dhr-hotel-management&message=' . $message));
        exit;
    }
    
    /**
     * Delete hotel
     */
    public function delete_hotel() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        check_admin_referer('dhr_delete_hotel_nonce');
        
        $hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
        
        if ($hotel_id > 0) {
            $result = DHR_Hotel_Database::delete_hotel($hotel_id);
            $message = $result ? 'deleted' : 'error';
        } else {
            $message = 'error';
        }
        
        wp_redirect(admin_url('admin.php?page=dhr-hotel-management&message=' . $message));
        exit;
    }
    
    /**
     * Display settings page
     */
    public function display_settings() {
        $api_key = get_option('dhr_hotel_google_maps_api_key', '');
        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/settings.php';
    }

    /**
     * Display Room Settings – shortcodes with copy
     */
    public function display_room_settings() {
        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/room-settings.php';
    }

    /**
     * Save Room Settings (accommodation URL pattern for card layout "View Room" links).
     */
    public function save_room_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'dhr-hotel-management'));
        }

        check_admin_referer('dhr_room_settings_nonce');

        $pattern = isset($_POST['dhr_room_accommodation_url_pattern']) ? wp_unslash($_POST['dhr_room_accommodation_url_pattern']) : '';
        $pattern = sanitize_textarea_field($pattern);
        update_option('dhr_room_accommodation_url_pattern', $pattern);

        wp_redirect(admin_url('admin.php?page=dhr-hotel-room-settings&message=saved'));
        exit;
    }
    
    /**
     * Save settings
     */
    public function save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        check_admin_referer('dhr_settings_nonce');
        
        $api_key = isset($_POST['google_maps_api_key']) ? sanitize_text_field($_POST['google_maps_api_key']) : '';
        update_option('dhr_hotel_google_maps_api_key', $api_key);

        // SHR WS Shop API (REST) settings
        // Manual access token (preferred method)
        $shr_manual_token = isset($_POST['shr_manual_access_token']) ? trim($_POST['shr_manual_access_token']) : '';
        if (!empty($shr_manual_token)) {
            update_option('dhr_shr_manual_access_token', $shr_manual_token);
        } else {
            // Clear manual token if empty
            delete_option('dhr_shr_manual_access_token');
        }

        // Client credentials (optional, for auto token generation)
        $shr_client_id = isset($_POST['shr_client_id']) ? sanitize_text_field($_POST['shr_client_id']) : '';
        update_option('dhr_shr_client_id', $shr_client_id);

        $shr_client_secret = isset($_POST['shr_client_secret']) ? $_POST['shr_client_secret'] : '';
        if (!empty($shr_client_secret)) {
            // Store encoded for basic obfuscation
            update_option('dhr_shr_client_secret', base64_encode($shr_client_secret));
        }

        $shr_scope = isset($_POST['shr_scope']) ? sanitize_text_field($_POST['shr_scope']) : 'wsapi.hoteldetails.read';
        update_option('dhr_shr_scope', $shr_scope);

        $shr_token_url = isset($_POST['shr_token_url']) ? esc_url_raw($_POST['shr_token_url']) : 'https://id.shrglobal.com/connect/token';
        update_option('dhr_shr_token_url', rtrim($shr_token_url, '/'));

        $shr_shop_base_url = isset($_POST['shr_shop_base_url']) ? esc_url_raw($_POST['shr_shop_base_url']) : 'https://api.shrglobal.com/shop';
        update_option('dhr_shr_shop_base_url', rtrim($shr_shop_base_url, '/'));

        $shr_channel_id = isset($_POST['shr_channel_id']) ? sanitize_text_field($_POST['shr_channel_id']) : '30';
        update_option('dhr_shr_channel_id', $shr_channel_id);

        wp_redirect(admin_url('admin.php?page=dhr-hotel-settings&message=saved'));
        exit;
    }
    
    /**
     * Display map management page
     */
    public function display_map_management() {
        // Ensure tables exist
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_map_configs';
        
        // Check if table exists, if not create it
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        ));
        
        if (!$table_exists) {
            DHR_Hotel_Database::create_tables();
        }
        
        // Ensure any newly added default maps are inserted (skips existing ones)
        DHR_Hotel_Database::create_default_map_configs();
        
        // Get map configs
        $map_configs = DHR_Hotel_Database::get_all_map_configs();
        
        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/map-management.php';
    }
    
    /**
     * Save map configuration
     */
    public function save_map_config() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        check_admin_referer('dhr_map_config_nonce');
        
        $map_id = isset($_POST['map_id']) ? intval($_POST['map_id']) : 0;
        $settings = array();
        $is_partner_portfolio = !empty($_POST['is_partner_portfolio']);
        $is_lodges_camps = !empty($_POST['is_lodges_camps']);
        
        if ($is_partner_portfolio) {
            // Partner Portfolio Map: two separate hotel groups
            $cityblue_ids = array();
            if (isset($_POST['setting_cityblue_hotels']) && is_array($_POST['setting_cityblue_hotels'])) {
                $cityblue_ids = array_values(array_unique(array_filter(array_map('intval', $_POST['setting_cityblue_hotels']))));
            }
            $dream_ids = array();
            if (isset($_POST['setting_dream_hotels']) && is_array($_POST['setting_dream_hotels'])) {
                $dream_ids = array_values(array_unique(array_filter(array_map('intval', $_POST['setting_dream_hotels']))));
            }
            $settings['selected_cityblue_hotel_ids'] = $cityblue_ids;
            $settings['selected_dream_hotel_ids'] = $dream_ids;
            // Combined for the generic filter
            $settings['selected_hotel_ids'] = array_values(array_unique(array_merge($cityblue_ids, $dream_ids)));
        } elseif ($is_lodges_camps) {
            // Lodges & Camps Map: two separate hotel groups
            $lodges_ids = array();
            if (isset($_POST['setting_lodges_hotels']) && is_array($_POST['setting_lodges_hotels'])) {
                $lodges_ids = array_values(array_unique(array_filter(array_map('intval', $_POST['setting_lodges_hotels']))));
            }
            $weddings_ids = array();
            if (isset($_POST['setting_weddings_hotels']) && is_array($_POST['setting_weddings_hotels'])) {
                $weddings_ids = array_values(array_unique(array_filter(array_map('intval', $_POST['setting_weddings_hotels']))));
            }
            $settings['selected_lodges_hotel_ids'] = $lodges_ids;
            $settings['selected_weddings_hotel_ids'] = $weddings_ids;
            $settings['selected_hotel_ids'] = array_values(array_unique(array_merge($lodges_ids, $weddings_ids)));
        } else {
            // All other maps: single hotel selection
            $selected_ids = array();
            if (isset($_POST['setting_selected_hotels']) && is_array($_POST['setting_selected_hotels'])) {
                $selected_ids = array_merge($selected_ids, array_values(array_filter(array_map('intval', array_values($_POST['setting_selected_hotels'])))));
            }
            foreach ($_POST as $key => $value) {
                if ($key === 'setting_selected_hotels' && is_array($value)) {
                    $selected_ids = array_merge($selected_ids, array_values(array_filter(array_map('intval', array_values($value)))));
                    break;
                }
                if (strpos($key, 'setting_selected_hotels[') === 0 && is_numeric(str_replace(array('setting_selected_hotels[', ']'), '', $key))) {
                    $selected_ids[] = intval($value);
                }
            }
            $selected_ids = array_values(array_unique(array_filter($selected_ids)));
            $settings['selected_hotel_ids'] = $selected_ids;
        }
        
        // Get all POST data and build settings array
        $skip_keys = array('selected_hotels', 'cityblue_hotels', 'dream_hotels', 'lodges_hotels', 'weddings_hotels');
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') !== 0) {
                continue;
            }
            $setting_key = str_replace('setting_', '', $key);
            // Skip hotel selection keys (handled above)
            $skip = false;
            foreach ($skip_keys as $sk) {
                if ($setting_key === $sk || strpos($setting_key, $sk) === 0) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;
            // Handle different field types (wp_unslash to avoid storing escaped apostrophes)
            if (strpos($setting_key, 'description') !== false || strpos($setting_key, 'text') !== false) {
                $settings[$setting_key] = sanitize_textarea_field(wp_unslash($value));
            } elseif (strpos($setting_key, 'url') !== false || strpos($setting_key, 'link') !== false) {
                $settings[$setting_key] = esc_url_raw($value);
            } elseif ($setting_key === 'show_numbers' || $setting_key === 'show_list') {
                $settings[$setting_key] = isset($_POST[$key]) && ($value == '1' || $value == true) ? true : false;
            } else {
                $settings[$setting_key] = sanitize_text_field(wp_unslash($value));
            }
        }
        
        $data = array(
            'map_name' => isset($_POST['map_name']) ? sanitize_text_field(wp_unslash($_POST['map_name'])) : '',
            'settings' => $settings,
            'status' => isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : 'active'
        );
        
        $result = DHR_Hotel_Database::update_map_config($map_id, $data);
        $message = $result ? 'updated' : 'error';
        
        wp_redirect(admin_url('admin.php?page=dhr-hotel-map-management&message=' . $message));
        exit;
    }
    
    /**
     * Create default maps
     */
    public function create_default_maps() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        check_admin_referer('dhr_create_default_maps_nonce');
        
        // Ensure table exists
        DHR_Hotel_Database::create_tables();
        
        // Create default maps
        DHR_Hotel_Database::create_default_map_configs();
        
        wp_redirect(admin_url('admin.php?page=dhr-hotel-map-management&message=maps_created'));
        exit;
    }

    /**
     * Category list and form
     */
    public function display_category_list() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        if ($action === 'edit' && $category_id > 0) {
            $category = DHR_Hotel_Database::get_category($category_id);
            if (!$category) {
                wp_safe_redirect(admin_url('admin.php?page=dhr-hotel-categories&message=error'));
                exit;
            }
            include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/category-form.php';
            return;
        }
        if ($action === 'add') {
            $category = null;
            include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/category-form.php';
            return;
        }
        $categories = DHR_Hotel_Database::get_all_categories();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/category-list.php';
    }

    public function save_category() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        check_admin_referer('dhr_category_nonce');
        $id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $data = array(
            'title'            => isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '',
            'subtitle'         => isset($_POST['subtitle']) ? sanitize_text_field(wp_unslash($_POST['subtitle'])) : '',
            'description'      => isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '',
            'image_url'        => isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '',
            'icon_url'         => isset($_POST['icon_url']) ? esc_url_raw($_POST['icon_url']) : '',
            'view_package_url' => isset($_POST['view_package_url']) ? esc_url_raw($_POST['view_package_url']) : '',
            'is_active'        => isset($_POST['is_active']) ? 1 : 0,
        );
        if ($id > 0) {
            $result = DHR_Hotel_Database::update_category($id, $data);
            $message = $result ? 'updated' : 'error';
        } else {
            $result = DHR_Hotel_Database::insert_category($data);
            $message = $result ? 'added' : 'error';
        }
        wp_redirect(admin_url('admin.php?page=dhr-hotel-categories&message=' . $message));
        exit;
    }

    public function delete_category() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        check_admin_referer('dhr_delete_category_nonce');
        $id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        if ($id > 0) {
            DHR_Hotel_Database::delete_category($id);
        }
        wp_redirect(admin_url('admin.php?page=dhr-hotel-categories&message=deleted'));
        exit;
    }

    /**
     * Package list and form
     */
    public function display_package_list() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $package_id = isset($_GET['package_id']) ? intval($_GET['package_id']) : 0;
        if ($action === 'edit' && $package_id > 0) {
            $package = DHR_Hotel_Database::get_package($package_id);
            if (!$package) {
                wp_safe_redirect(admin_url('admin.php?page=dhr-hotel-packages&message=error'));
                exit;
            }
            include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/package-form.php';
            return;
        }
        if ($action === 'add') {
            $package = null;
            include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/package-form.php';
            return;
        }
        $packages = DHR_Hotel_Database::get_all_packages();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/package-list.php';
    }

    public function save_package() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        check_admin_referer('dhr_package_nonce');
        $id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;
        $package_code = isset($_POST['package_code']) ? sanitize_text_field($_POST['package_code']) : '';
        $hotel_code   = isset($_POST['hotel_code']) ? sanitize_text_field($_POST['hotel_code']) : '';
        $category_id  = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

        $valid_from = isset($_POST['valid_from']) ? sanitize_text_field($_POST['valid_from']) : '';
        $valid_to   = isset($_POST['valid_to']) ? sanitize_text_field($_POST['valid_to']) : '';

        // Add New Package: call SHR API to get package details and use beginDate/endDate; only insert if dates are not in the past
        if ($id === 0) {
            if (empty($package_code) || empty($hotel_code)) {
                wp_redirect(admin_url('admin.php?page=dhr-hotel-packages&message=error&error=' . urlencode(__('Package code and hotel code are required.', 'dhr-hotel-management'))));
                exit;
            }
            $api    = new DHR_Hotel_API();
            $result = $api->fetch_shr_package_details($hotel_code, $package_code);
            if (!$result['success']) {
                wp_redirect(admin_url('admin.php?page=dhr-hotel-packages&message=error&error=' . urlencode($result['error'])));
                exit;
            }
            $begin_date = $result['beginDate'];
            $end_date   = $result['endDate'];
            $validation = $api->validate_package_dates_not_past($begin_date, $end_date);
            if (!$validation['valid']) {
                wp_redirect(admin_url('admin.php?page=dhr-hotel-packages&message=error&error=' . urlencode($validation['error'])));
                exit;
            }
            // Convert SHR ISO dates to MySQL datetime (e.g. 2026-01-01T00:00:00 -> 2026-01-01 00:00:00)
            $valid_from = str_replace('T', ' ', substr($begin_date, 0, 19));
            $valid_to   = str_replace('T', ' ', substr($end_date, 0, 19));
            if (substr_count($valid_from, ':') === 1) {
                $valid_from .= ':00';
            }
            if (substr_count($valid_to, ':') === 1) {
                $valid_to .= ':00';
            }
            $api_result = $result; // Keep for saving to package_details table
        } else {
            // Edit: keep existing date handling
            if ($valid_from && strpos($valid_from, 'T') !== false) {
                $valid_from = str_replace('T', ' ', $valid_from);
                if (substr_count($valid_from, ':') === 1) {
                    $valid_from .= ':00';
                }
            }
            if ($valid_to && strpos($valid_to, 'T') !== false) {
                $valid_to = str_replace('T', ' ', $valid_to);
                if (substr_count($valid_to, ':') === 1) {
                    $valid_to .= ':00';
                }
            }
            if (empty($valid_from) || empty($valid_to)) {
                $existing = DHR_Hotel_Database::get_package($id);
                if ($existing) {
                    if (empty($valid_from)) $valid_from = $existing->valid_from;
                    if (empty($valid_to))   $valid_to   = $existing->valid_to;
                }
                if (empty($valid_from)) $valid_from = current_time('mysql');
                if (empty($valid_to))   $valid_to   = date('Y-m-d H:i:s', strtotime('+10 years'));
            }
        }

        $data = array(
            'package_code' => $package_code,
            'hotel_code'   => $hotel_code,
            'category_id'  => $category_id,
            'valid_from'   => $valid_from,
            'valid_to'     => $valid_to,
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        );
        if ($id > 0) {
            $result = DHR_Hotel_Database::update_package($id, $data);
            $message = $result ? 'updated' : 'error';
        } else {
            $new_package_id = DHR_Hotel_Database::insert_package($data);
            $result = (bool) $new_package_id;
            $message = $result ? 'added' : 'error';
            // Store SHR API response in package details table (only for new packages; we have $result from API in this block)
            if ($new_package_id && isset($api_result) && !empty($api_result['productDetails'])) {
                DHR_Hotel_Database::save_package_details($new_package_id, $api_result, $hotel_code);
            }
        }
        wp_redirect(admin_url('admin.php?page=dhr-hotel-packages&message=' . $message));
        exit;
    }

    public function delete_package() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        check_admin_referer('dhr_delete_package_nonce');
        $id = isset($_GET['package_id']) ? intval($_GET['package_id']) : 0;
        if ($id > 0) {
            DHR_Hotel_Database::delete_package($id);
        }
        wp_redirect(admin_url('admin.php?page=dhr-hotel-packages&message=deleted'));
        exit;
    }

    /**
     * Package Settings: category-wise shortcode generator.
     */
    public function display_package_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        $categories = DHR_Hotel_Database::get_all_categories();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/package-settings.php';
    }

    /**
     * Display property-wise settings for dhr_where_to_find_us_map shortcode.
     */
    public function display_where_to_find_us_property_map() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $selected_property_id = isset($_GET['property_id']) ? (int) $_GET['property_id'] : 0;
        $message = isset($_GET['message']) ? sanitize_text_field(wp_unslash($_GET['message'])) : '';

        $properties = get_posts(array(
            'post_type'      => 'properties',
            'post_status'    => array('publish', 'draft', 'pending', 'private'),
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));

        $saved_data = array();
        if ($selected_property_id > 0) {
            $saved_data = DHR_Hotel_Database::get_where_to_find_us_property_map($selected_property_id);
        }

        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/where-to-find-us-property-map.php';
    }

    /**
     * Save property-wise settings for dhr_where_to_find_us_map shortcode.
     */
    public function save_where_to_find_us_property_map() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        check_admin_referer('dhr_where_to_find_us_property_map_nonce');

        $property_id = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
        if ($property_id <= 0) {
            wp_safe_redirect(admin_url('admin.php?page=dhr-where-to-find-us-property-map&message=error'));
            exit;
        }

        $enquire_url_raw = isset($_POST['enquire_url']) ? wp_unslash((string) $_POST['enquire_url']) : '';
        $enquire_url_save = $enquire_url_raw !== '' ? esc_url_raw($enquire_url_raw) : '';

        $data = array(
            'property_image'           => isset($_POST['property_image']) ? esc_url_raw(wp_unslash($_POST['property_image'])) : '',
            'property_logo_image'      => isset($_POST['property_logo_image']) ? esc_url_raw(wp_unslash($_POST['property_logo_image'])) : '',
            'latitude'                 => isset($_POST['latitude']) ? sanitize_text_field(wp_unslash($_POST['latitude'])) : '',
            'longitude'                => isset($_POST['longitude']) ? sanitize_text_field(wp_unslash($_POST['longitude'])) : '',
            'google_maps_url'          => isset($_POST['google_maps_url']) ? esc_url_raw(wp_unslash((string) $_POST['google_maps_url'])) : '',
            'google_maps_button_text'  => isset($_POST['google_maps_button_text']) ? sanitize_text_field(wp_unslash((string) $_POST['google_maps_button_text'])) : '',
            'main_heading'             => isset($_POST['main_heading']) ? sanitize_text_field(wp_unslash($_POST['main_heading'])) : '',
            'sub_title'                => isset($_POST['sub_title']) ? sanitize_text_field(wp_unslash((string) $_POST['sub_title'])) : '',
            'gps_coordinates'          => isset($_POST['gps_coordinates']) ? sanitize_text_field(wp_unslash((string) $_POST['gps_coordinates'])) : '',
            'address_text'             => isset($_POST['address_text']) ? sanitize_textarea_field(wp_unslash($_POST['address_text'])) : '',
            'phone_label'              => isset($_POST['phone_label']) ? sanitize_text_field(wp_unslash($_POST['phone_label'])) : '',
            'phone_number'             => isset($_POST['phone_number']) ? sanitize_text_field(wp_unslash($_POST['phone_number'])) : '',
            'email_address'            => isset($_POST['email_address']) ? sanitize_email(wp_unslash($_POST['email_address'])) : '',
            'enquire_text'             => isset($_POST['enquire_text']) ? sanitize_text_field(wp_unslash($_POST['enquire_text'])) : '',
            'enquire_url'              => $enquire_url_save,
        );

        DHR_Hotel_Database::save_where_to_find_us_property_map($property_id, $data);

        wp_safe_redirect(admin_url('admin.php?page=dhr-where-to-find-us-property-map&property_id=' . $property_id . '&message=saved'));
        exit;
    }

    /**
     * Delete property-wise settings for dhr_where_to_find_us_map shortcode.
     */
    public function delete_where_to_find_us_property_map() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        check_admin_referer('dhr_where_to_find_us_property_map_delete_nonce');

        $property_id = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
        if ($property_id > 0) {
            DHR_Hotel_Database::delete_where_to_find_us_property_map($property_id);
            wp_safe_redirect(admin_url('admin.php?page=dhr-where-to-find-us-property-map&property_id=' . $property_id . '&message=deleted'));
            exit;
        }

        wp_safe_redirect(admin_url('admin.php?page=dhr-where-to-find-us-property-map&message=error'));
        exit;
    }
    
    /**
     * Sync a hotel from SHR WS Shop API (non-AJAX, from list form).
     * Only adds new hotels; if hotel code already exists, redirects with error.
     */
    public function sync_shr_hotel() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        check_admin_referer('dhr_sync_shr_hotel_nonce');

        $hotel_code = isset($_POST['hotel_code']) ? sanitize_text_field($_POST['hotel_code']) : '';

        if (empty($hotel_code)) {
            wp_redirect(admin_url('admin.php?page=dhr-hotel-management&message=error'));
            exit;
        }

        $existing = DHR_Hotel_Database::get_hotel_by_code($hotel_code);
        if ($existing) {
            $error_param = urlencode(__('A hotel with this code already exists. Use the Sync button on that row to update from SHR.', 'dhr-hotel-management'));
            wp_redirect(admin_url('admin.php?page=dhr-hotel-management&message=error&error=' . $error_param));
            exit;
        }

        $api    = new DHR_Hotel_API();
        $result = $api->fetch_shr_and_save_hotel($hotel_code);

        if ($result['success']) {
            wp_redirect(admin_url('admin.php?page=dhr-hotel-management&message=added'));
        } else {
            $error_param = urlencode($result['error']);
            wp_redirect(admin_url('admin.php?page=dhr-hotel-management&message=error&error=' . $error_param));
        }
        exit;
    }

    /**
     * Sync a hotel from SHR WS Shop API via AJAX.
     * When update_existing=1 (row sync): re-sync that hotel from SHR.
     * Otherwise (top form "Sync & Add"): only allow if hotel code does not already exist.
     */
    public function sync_shr_hotel_ajax() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }

        check_ajax_referer('dhr_sync_shr_hotel_ajax_nonce', 'nonce');

        $hotel_code     = isset($_POST['hotel_code']) ? sanitize_text_field($_POST['hotel_code']) : '';
        $update_existing = isset($_POST['update_existing']) && $_POST['update_existing'] === '1';

        if (empty($hotel_code)) {
            wp_send_json_error(array('message' => __('Hotel code is required.', 'dhr-hotel-management')));
            return;
        }

        $existing = DHR_Hotel_Database::get_hotel_by_code($hotel_code);
        if (!$update_existing && $existing) {
            wp_send_json_error(array('message' => __('A hotel with this code already exists. Use the Sync button on that row to update from SHR.', 'dhr-hotel-management')));
            return;
        }

        $api    = new DHR_Hotel_API();
        $result = $api->fetch_shr_and_save_hotel($hotel_code);

        if ($result['success']) {
            wp_send_json_success(array(
                'message'     => __('Hotel synced successfully from SHR.', 'dhr-hotel-management'),
                'hotel_id'    => $result['hotel_id'],
                'hotel_code'  => $result['hotel_code'],
                'hotel_name'  => $result['hotel_name'],
            ));
        } else {
            wp_send_json_error(array('message' => $result['error']));
        }
    }
}

