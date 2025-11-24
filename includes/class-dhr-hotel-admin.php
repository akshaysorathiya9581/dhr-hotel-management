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
        add_action('admin_post_dhr_insert_sample_data', array($this, 'insert_sample_data'));
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
            __('Add New Hotel', 'dhr-hotel-management'),
            __('Add New', 'dhr-hotel-management'),
            'manage_options',
            'dhr-hotel-add',
            array($this, 'display_hotel_form')
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
            __('Insert Sample Data', 'dhr-hotel-management'),
            __('Insert Sample Data', 'dhr-hotel-management'),
            'manage_options',
            'dhr-hotel-sample-data',
            array($this, 'display_sample_data_page')
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
        
        if ($action === 'edit' && $hotel_id > 0) {
            $this->display_hotel_form($hotel_id);
            return;
        }
        
        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/hotels-list.php';
    }
    
    /**
     * Display hotel form (add/edit)
     */
    public function display_hotel_form($hotel_id = 0) {
        $hotel = null;
        if ($hotel_id > 0) {
            $hotel = DHR_Hotel_Database::get_hotel($hotel_id);
            if (!$hotel) {
                wp_die(__('Hotel not found.', 'dhr-hotel-management'));
            }
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
            'name' => isset($_POST['name']) ? $_POST['name'] : '',
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
            'address' => isset($_POST['address']) ? $_POST['address'] : '',
            'city' => isset($_POST['city']) ? $_POST['city'] : '',
            'province' => isset($_POST['province']) ? $_POST['province'] : '',
            'country' => isset($_POST['country']) ? $_POST['country'] : 'South Africa',
            'latitude' => isset($_POST['latitude']) ? $_POST['latitude'] : '',
            'longitude' => isset($_POST['longitude']) ? $_POST['longitude'] : '',
            'phone' => isset($_POST['phone']) ? $_POST['phone'] : '',
            'email' => isset($_POST['email']) ? $_POST['email'] : '',
            'website' => isset($_POST['website']) ? $_POST['website'] : '',
            'image_url' => isset($_POST['image_url']) ? $_POST['image_url'] : '',
            'google_maps_url' => isset($_POST['google_maps_url']) ? $_POST['google_maps_url'] : '',
            'status' => isset($_POST['status']) ? $_POST['status'] : 'active'
        );
        
        if ($hotel_id > 0) {
            $result = DHR_Hotel_Database::update_hotel($hotel_id, $data);
            $message = $result ? 'updated' : 'error';
        } else {
            $result = DHR_Hotel_Database::insert_hotel($data);
            $message = $result ? 'added' : 'error';
        }
        
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
     * Save settings
     */
    public function save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        check_admin_referer('dhr_settings_nonce');
        
        $api_key = isset($_POST['google_maps_api_key']) ? sanitize_text_field($_POST['google_maps_api_key']) : '';
        update_option('dhr_hotel_google_maps_api_key', $api_key);
        
        // Save map display settings
        $location_heading = isset($_POST['location_heading']) ? sanitize_text_field($_POST['location_heading']) : '';
        update_option('dhr_hotel_location_heading', $location_heading);
        
        $main_heading = isset($_POST['main_heading']) ? sanitize_text_field($_POST['main_heading']) : '';
        update_option('dhr_hotel_main_heading', $main_heading);
        
        $description_text = isset($_POST['description_text']) ? sanitize_textarea_field($_POST['description_text']) : '';
        update_option('dhr_hotel_description_text', $description_text);
        
        $reservation_label = isset($_POST['reservation_label']) ? sanitize_text_field($_POST['reservation_label']) : '';
        update_option('dhr_hotel_reservation_label', $reservation_label);
        
        $reservation_phone = isset($_POST['reservation_phone']) ? sanitize_text_field($_POST['reservation_phone']) : '';
        update_option('dhr_hotel_reservation_phone', $reservation_phone);
        
        $view_on_google_maps_link = isset($_POST['view_on_google_maps_link']) ? esc_url_raw($_POST['view_on_google_maps_link']) : '';
        update_option('dhr_hotel_view_on_google_maps_link', $view_on_google_maps_link);
        
        wp_redirect(admin_url('admin.php?page=dhr-hotel-settings&message=saved'));
        exit;
    }
    
    /**
     * Display sample data insertion page
     */
    public function display_sample_data_page() {
        include DHR_HOTEL_PLUGIN_PATH . 'templates/admin/sample-data.php';
    }
    
    /**
     * Insert sample data
     */
    public function insert_sample_data() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        check_admin_referer('dhr_insert_sample_data_nonce');
        
        // Perform the data insertion
        $this->do_insert_sample_data();
        
        wp_redirect(admin_url('admin.php?page=dhr-hotel-sample-data&message=inserted'));
        exit;
    }
    
    /**
     * Perform the actual data insertion
     */
    private function do_insert_sample_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotels';
        
        // Sample hotel data
        $hotels = array(
            array(
                'name' => 'Le Franschhoek Hotel & Spa',
                'description' => 'Luxurious hotel nestled in the heart of Franschhoek wine valley, offering world-class spa facilities and fine dining experiences.',
                'address' => '16 Akademie Street',
                'city' => 'Franschhoek',
                'province' => 'Western Cape',
                'country' => 'South Africa',
                'latitude' => -33.9075,
                'longitude' => 19.1234,
                'phone' => '+27 (0)21 876 8900',
                'email' => 'info@lefranschhoek.co.za',
                'website' => 'https://www.lefranschhoek.co.za',
                'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
                'google_maps_url' => 'https://www.google.com/maps?q=-33.9075,19.1234',
                'status' => 'active'
            ),
            array(
                'name' => 'Cape Town Waterfront Hotel',
                'description' => 'Modern 5-star hotel overlooking the V&A Waterfront with stunning views of Table Mountain and the harbor.',
                'address' => '17 Dock Road, V&A Waterfront',
                'city' => 'Cape Town',
                'province' => 'Western Cape',
                'country' => 'South Africa',
                'latitude' => -33.9048,
                'longitude' => 18.4211,
                'phone' => '+27 (0)21 419 2000',
                'email' => 'reservations@ctwaterfront.co.za',
                'website' => 'https://www.ctwaterfront.co.za',
                'image_url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800',
                'google_maps_url' => 'https://www.google.com/maps?q=-33.9048,18.4211',
                'status' => 'active'
            ),
            array(
                'name' => 'Stellenbosch Vineyard Estate',
                'description' => 'Boutique hotel set among rolling vineyards, offering wine tastings and gourmet cuisine in a tranquil setting.',
                'address' => 'R44, Annandale Road',
                'city' => 'Stellenbosch',
                'province' => 'Western Cape',
                'country' => 'South Africa',
                'latitude' => -33.9321,
                'longitude' => 18.8602,
                'phone' => '+27 (0)21 880 0100',
                'email' => 'stay@vineyardestate.co.za',
                'website' => 'https://www.vineyardestate.co.za',
                'image_url' => 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=800',
                'google_maps_url' => 'https://www.google.com/maps?q=-33.9321,18.8602',
                'status' => 'active'
            ),
            array(
                'name' => 'Hermanus Ocean View Resort',
                'description' => 'Beachfront resort with panoramic ocean views, perfect for whale watching during season.',
                'address' => 'Marine Drive, Westcliff',
                'city' => 'Hermanus',
                'province' => 'Western Cape',
                'country' => 'South Africa',
                'latitude' => -34.4186,
                'longitude' => 19.2345,
                'phone' => '+27 (0)28 312 3456',
                'email' => 'bookings@hermanusresort.co.za',
                'website' => 'https://www.hermanusresort.co.za',
                'image_url' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800',
                'google_maps_url' => 'https://www.google.com/maps?q=-34.4186,19.2345',
                'status' => 'active'
            ),
            array(
                'name' => 'Knysna Lagoon Lodge',
                'description' => 'Elegant lodge on the Knysna Lagoon offering water activities, fine dining, and access to the Garden Route.',
                'address' => 'Thesen Island, Knysna Quays',
                'city' => 'Knysna',
                'province' => 'Western Cape',
                'country' => 'South Africa',
                'latitude' => -34.0351,
                'longitude' => 23.0465,
                'phone' => '+27 (0)44 382 5500',
                'email' => 'info@knysnalodge.co.za',
                'website' => 'https://www.knysnalodge.co.za',
                'image_url' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800',
                'google_maps_url' => 'https://www.google.com/maps?q=-34.0351,23.0465',
                'status' => 'active'
            ),
            array(
                'name' => 'Paarl Mountain Retreat',
                'description' => 'Secluded mountain retreat offering spa treatments, hiking trails, and breathtaking valley views.',
                'address' => 'R301, Paarl Mountain Road',
                'city' => 'Paarl',
                'province' => 'Western Cape',
                'country' => 'South Africa',
                'latitude' => -33.7300,
                'longitude' => 18.9750,
                'phone' => '+27 (0)21 872 4848',
                'email' => 'retreat@paarlmountain.co.za',
                'website' => 'https://www.paarlmountain.co.za',
                'image_url' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800',
                'google_maps_url' => 'https://www.google.com/maps?q=-33.7300,18.9750',
                'status' => 'active'
            ),
            array(
                'name' => 'Cape Winelands Boutique Hotel',
                'description' => 'Intimate boutique hotel in the heart of wine country, featuring elegant rooms and award-winning restaurant.',
                'address' => 'Main Road, Franschhoek',
                'city' => 'Franschhoek',
                'province' => 'Western Cape',
                'country' => 'South Africa',
                'latitude' => -33.9147,
                'longitude' => 19.1244,
                'phone' => '+27 (0)21 876 2145',
                'email' => 'reservations@winelandshotel.co.za',
                'website' => 'https://www.winelandshotel.co.za',
                'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
                'google_maps_url' => 'https://www.google.com/maps?q=-33.9147,19.1244',
                'status' => 'active'
            ),
            array(
                'name' => 'Table Mountain View Hotel',
                'description' => 'Contemporary hotel with direct views of Table Mountain, located in the vibrant city center.',
                'address' => 'Long Street 123',
                'city' => 'Cape Town',
                'province' => 'Western Cape',
                'country' => 'South Africa',
                'latitude' => -33.9249,
                'longitude' => 18.4241,
                'phone' => '+27 (0)21 422 8888',
                'email' => 'book@tablemountainview.co.za',
                'website' => 'https://www.tablemountainview.co.za',
                'image_url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800',
                'google_maps_url' => 'https://www.google.com/maps?q=-33.9249,18.4241',
                'status' => 'active'
            ),
            array(
                'name' => 'Garden Route Safari Lodge',
                'description' => 'Luxury safari lodge combining wildlife experiences with modern comfort, set in a private game reserve.',
                'address' => 'N2 Highway, Wilderness',
                'city' => 'Wilderness',
                'province' => 'Western Cape',
                'country' => 'South Africa',
                'latitude' => -33.9816,
                'longitude' => 22.5687,
                'phone' => '+27 (0)44 877 1199',
                'email' => 'safari@gardenroute.co.za',
                'website' => 'https://www.gardenroutesafari.co.za',
                'image_url' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800',
                'google_maps_url' => 'https://www.google.com/maps?q=-33.9816,22.5687',
                'status' => 'active'
            ),
            array(
                'name' => 'Robben Island Heritage Hotel',
                'description' => 'Historic hotel near Robben Island ferry terminal, offering cultural tours and waterfront dining.',
                'address' => 'V&A Waterfront, Breakwater Boulevard',
                'city' => 'Cape Town',
                'province' => 'Western Cape',
                'country' => 'South Africa',
                'latitude' => -33.9068,
                'longitude' => 18.4233,
                'phone' => '+27 (0)21 419 5000',
                'email' => 'heritage@robbenislandhotel.co.za',
                'website' => 'https://www.robbenislandhotel.co.za',
                'image_url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800',
                'google_maps_url' => 'https://www.google.com/maps?q=-33.9068,18.4233',
                'status' => 'active'
            )
        );
        
        // Check if hotels already exist
        $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        $inserted = 0;
        $skipped = 0;
        
        foreach ($hotels as $hotel) {
            // Check if hotel with same name already exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE name = %s",
                $hotel['name']
            ));
            
            if ($exists > 0) {
                $skipped++;
                continue;
            }
            
            $result = $wpdb->insert(
                $table_name,
                $hotel,
                array('%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s')
            );
            
            if ($result !== false) {
                $inserted++;
            }
        }
        
        // Store results in transient for display
        set_transient('dhr_sample_data_result', array(
            'inserted' => $inserted,
            'skipped' => $skipped,
            'total' => count($hotels)
        ), 30);
    }
}

