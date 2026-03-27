<?php
/**
 * Frontend functionality for DHR Hotel Management
 */

if (!defined('ABSPATH')) {
    exit;
}

class DHR_Hotel_Frontend {
    
    public function __construct() {
        // Register all map shortcodes
        add_shortcode('dhr_hotel_map', array($this, 'display_hotel_map'));
        add_shortcode('dhr_head_office_map', array($this, 'display_head_office_map'));
        add_shortcode('dhr_partner_portfolio_map', array($this, 'display_partner_portfolio_map'));
        add_shortcode('dhr_dining_venue_map', array($this, 'display_dining_venue_map'));
        add_shortcode('dhr_wedding_venue_map', array($this, 'display_wedding_venue_map'));
        add_shortcode('dhr_property_portfolio_map', array($this, 'display_property_portfolio_map'));
        add_shortcode('dhr_lodges_camps_map', array($this, 'display_lodges_camps_map'));
        add_shortcode('dhr_conference_map', array($this, 'display_conference_map'));
        add_shortcode('dhr_where_to_find_us_map', array($this, 'display_where_to_find_us_map'));
        
        // Register hotel rooms shortcodes: [hotel_rooms] = grid, [hotel_rooms_cards] = cards, [hotel_rooms_second] = same grid
        add_shortcode('hotel_rooms', array($this, 'display_hotel_rooms'));
        add_shortcode('hotel_rooms_cards', array($this, 'display_hotel_rooms_cards'));
        add_shortcode('hotel_rooms_second', array($this, 'display_hotel_rooms_second'));
        
        // Register package design shortcodes
        add_shortcode('dhr_package_first_design', array($this, 'display_package_first_design'));
        add_shortcode('dhr_package_second_design', array($this, 'display_package_second_design'));
        add_shortcode('dhr_package_kids_design', array($this, 'display_package_kids_design'));
        add_shortcode('dhr_package_early_bird_design', array($this, 'display_package_early_bird_design'));
        add_shortcode('dhr_packages', array($this, 'display_packages_by_category'));
        add_shortcode('dhr_package_experiences_design', array($this, 'display_package_experiences_design'));
        add_shortcode('dhr_category_list', array($this, 'display_package_experiences_design'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_ajax_dhr_get_availability_booking_url', array($this, 'ajax_get_availability_booking_url'));
        add_action('wp_ajax_nopriv_dhr_get_availability_booking_url', array($this, 'ajax_get_availability_booking_url'));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        // Always enqueue styles (lightweight)
        wp_enqueue_style(
            'dhr-hotel-frontend-style',
            DHR_HOTEL_PLUGIN_URL . 'assets/css/frontend-style.css',
            array(),
            DHR_HOTEL_PLUGIN_VERSION
        );
        
        // Always enqueue scripts - they will only initialize if map elements exist
        // This ensures maps work even when shortcodes are used in templates
        // Google Maps API - Get API key from settings
        $api_key = get_option('dhr_hotel_google_maps_api_key', '');
        if (!empty($api_key)) {
            wp_enqueue_script(
                'google-maps-api',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places',
                array(),
                null,
                true
            );
        } else {
            // Show admin notice if API key is not set
            if (current_user_can('manage_options')) {
                add_action('wp_footer', function() {
                    echo '<div class="notice notice-error" style="position: fixed; top: 32px; left: 160px; right: 20px; z-index: 9999; padding: 10px;"><p><strong>DHR Hotel Management:</strong> Google Maps API key is not configured. Please set it in <a href="' . admin_url('admin.php?page=dhr-hotel-settings') . '">Settings</a>.</p></div>';
                });
            }
        }
        
        wp_enqueue_script(
            'dhr-hotel-frontend-script',
            DHR_HOTEL_PLUGIN_URL . 'assets/js/frontend-script.js',
            array('jquery'),
            DHR_HOTEL_PLUGIN_VERSION,
            true
        );
        
        // Localize script with hotels data
        // CRITICAL: Convert database objects to arrays for JSON encoding
        // WordPress cannot JSON encode database objects directly, which causes infinite loading
        $hotels_array = array();
        
        try {
            $hotels = DHR_Hotel_Database::get_all_hotels('active');

            // echo "<pre>";
            // print_r($hotels);
            // echo "</pre>";
            // die();
            
            if (!empty($hotels) && is_array($hotels)) {
                foreach ($hotels as $hotel) {
                    // Convert object to array for JSON encoding
                    if (is_object($hotel)) {
                        $hotels_array[] = array(
                            'id' => isset($hotel->id) ? intval($hotel->id) : 0,
                            'name' => isset($hotel->name) ? sanitize_text_field(wp_unslash((string) $hotel->name)) : '',
                            'description' => isset($hotel->description) ? sanitize_text_field(wp_unslash((string) $hotel->description)) : '',
                            'address' => isset($hotel->address) ? sanitize_text_field(wp_unslash((string) $hotel->address)) : '',
                            'city' => isset($hotel->city) ? sanitize_text_field(wp_unslash((string) $hotel->city)) : '',
                            'province' => isset($hotel->province) ? sanitize_text_field(wp_unslash((string) $hotel->province)) : '',
                            'country' => isset($hotel->country) ? sanitize_text_field(wp_unslash((string) $hotel->country)) : '',
                            'latitude' => isset($hotel->latitude) ? floatval($hotel->latitude) : 0,
                            'longitude' => isset($hotel->longitude) ? floatval($hotel->longitude) : 0,
                            'phone' => isset($hotel->phone) ? sanitize_text_field(wp_unslash((string) $hotel->phone)) : '',
                            'email' => isset($hotel->email) ? sanitize_email($hotel->email) : '',
                            'website' => isset($hotel->website) ? esc_url_raw($hotel->website) : '',
                            'image_url' => isset($hotel->image_url) ? esc_url_raw($hotel->image_url) : '',
                            'google_maps_url' => isset($hotel->google_maps_url) ? esc_url_raw($hotel->google_maps_url) : '',
                            'status' => isset($hotel->status) ? sanitize_text_field(wp_unslash((string) $hotel->status)) : 'active',
                            'hotel_code' => isset($hotel->hotel_code) ? sanitize_text_field($hotel->hotel_code) : ''
                        );
                    } elseif (is_array($hotel)) {
                        // Already an array, just sanitize
                        $hotels_array[] = array(
                            'id' => isset($hotel['id']) ? intval($hotel['id']) : 0,
                            'name' => isset($hotel['name']) ? sanitize_text_field(wp_unslash((string) ($hotel['name'] ?? ''))) : '',
                            'description' => isset($hotel['description']) ? sanitize_text_field(wp_unslash((string) ($hotel['description'] ?? ''))) : '',
                            'address' => isset($hotel['address']) ? sanitize_text_field(wp_unslash((string) ($hotel['address'] ?? ''))) : '',
                            'city' => isset($hotel['city']) ? sanitize_text_field(wp_unslash((string) ($hotel['city'] ?? ''))) : '',
                            'province' => isset($hotel['province']) ? sanitize_text_field(wp_unslash((string) ($hotel['province'] ?? ''))) : '',
                            'country' => isset($hotel['country']) ? sanitize_text_field(wp_unslash((string) ($hotel['country'] ?? ''))) : '',
                            'latitude' => isset($hotel['latitude']) ? floatval($hotel['latitude']) : 0,
                            'longitude' => isset($hotel['longitude']) ? floatval($hotel['longitude']) : 0,
                            'phone' => isset($hotel['phone']) ? sanitize_text_field(wp_unslash((string) ($hotel['phone'] ?? ''))) : '',
                            'email' => isset($hotel['email']) ? sanitize_email($hotel['email']) : '',
                            'website' => isset($hotel['website']) ? esc_url_raw($hotel['website']) : '',
                            'image_url' => isset($hotel['image_url']) ? esc_url_raw($hotel['image_url']) : '',
                            'google_maps_url' => isset($hotel['google_maps_url']) ? esc_url_raw($hotel['google_maps_url']) : '',
                            'status' => isset($hotel['status']) ? sanitize_text_field(wp_unslash((string) ($hotel['status'] ?? ''))) : 'active',
                            'hotel_code' => isset($hotel['hotel_code']) ? sanitize_text_field($hotel['hotel_code']) : ''
                        );
                    }
                }
            }
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('DHR Hotel Management Error: ' . $e->getMessage());
            }
            $hotels_array = array();
        }
        
        // Only localize if script is registered
        if (wp_script_is('dhr-hotel-frontend-script', 'registered')) {
            wp_localize_script('dhr-hotel-frontend-script', 'dhrHotelsData', array(
                'hotels' => $hotels_array,
                'pluginUrl' => DHR_HOTEL_PLUGIN_URL
            ));
            wp_localize_script('dhr-hotel-frontend-script', 'dhrBookNow', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('bys_booking_nonce'),
            ));
        }
    }

    /**
     * AJAX: get SHR availability booking URL (check-in today, check-out tomorrow by default).
     * Expects: hotel_code, channel_id, check_in, check_out, rooms, adults, child_age (optional).
     * Returns JSON: { success, url? } or { success: false, errors: [] }
     */
    public function ajax_get_availability_booking_url() {
        check_ajax_referer('dhr_get_availability_booking_url', 'nonce');
        $hotel_code = isset($_POST['hotel_code']) ? sanitize_text_field($_POST['hotel_code']) : '';
        $channel_id = isset($_POST['channel_id']) ? absint($_POST['channel_id']) : 0;
        $check_in   = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
        $check_out  = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
        $rooms      = isset($_POST['rooms']) ? max(1, absint($_POST['rooms'])) : 1;
        $adults     = isset($_POST['adults']) ? max(1, absint($_POST['adults'])) : 2;
        $child_age  = isset($_POST['child_age']) ? sanitize_text_field($_POST['child_age']) : '';

        if (empty($hotel_code)) {
            wp_send_json(array('success' => false, 'errors' => array(__('Hotel code is required.', 'dhr-hotel-management'))));
        }
        if ($channel_id <= 0) {
            $channel_id = (int) get_option('dhr_shr_channel_id', '30');
        }
        $today    = function_exists('wp_date') ? wp_date('Y-m-d') : date('Y-m-d', current_time('timestamp'));
        $tomorrow = function_exists('wp_date') ? wp_date('Y-m-d', current_time('timestamp') + DAY_IN_SECONDS) : date('Y-m-d', strtotime('+1 day', current_time('timestamp')));
        if (empty($check_in) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $check_in)) {
            $check_in = $today;
        }
        if (empty($check_out) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $check_out)) {
            $check_out = $tomorrow;
        }
        $child_age_int = $child_age === '' || $child_age === null ? null : max(0, min(17, (int) $child_age));

        $api    = new DHR_Hotel_API();
        $result = $api->get_shr_availability_booking_url($hotel_code, $channel_id, $check_in, $check_out, $rooms, $adults, $child_age_int);

        wp_send_json($result);
    }
    
    /**
     * Decode map config settings safely (string or array, always return array).
     *
     * @param object|null $map_config Map config row from database.
     * @return array
     */
    public static function get_map_settings($map_config) {
        $settings = array();
        if ($map_config && !empty($map_config->settings)) {
            $settings = is_string($map_config->settings) ? json_decode($map_config->settings, true) : (array) $map_config->settings;
            $settings = is_array($settings) ? $settings : array();
        }
        return $settings;
    }

    /**
     * Filter hotels to only those selected for this map.
     * When selected_hotel_ids is empty (not set, null, '', or []) = show ALL active hotels on the map.
     * When selected_hotel_ids has IDs = show only those hotels.
     * Normalizes selected_hotel_ids from array, object, comma-separated string, or single value.
     */
    public static function filter_hotels_by_map_selection($hotels, $settings) {
        // No selection configured at all => show all hotels
        if (!isset($settings['selected_hotel_ids']) || $settings['selected_hotel_ids'] === null) {
            return $hotels;
        }

        $raw = $settings['selected_hotel_ids'];

        $ids = array();
        if (is_array($raw)) {
            $ids = array_values(array_map('intval', $raw));
        } elseif (is_object($raw)) {
            $ids = array_values(array_map('intval', (array) $raw));
        } elseif (is_string($raw)) {
            $trimmed = trim($raw);
            // Empty or serialized-empty values mean "no selection" => show all
            if ($trimmed === '' || $trimmed === '[]' || $trimmed === '{}') {
                return $hotels;
            }
            if (strpos($raw, ',') !== false) {
                $ids = array_values(array_map('intval', array_map('trim', explode(',', $raw))));
            } else {
                $ids = array(intval($raw));
            }
        } else {
            $ids = array(intval($raw));
        }
        $ids = array_values(array_filter($ids));

        // No IDs or all IDs filtered out = show all hotels (same as "no selection")
        if (empty($ids)) {
            return $hotels;
        }
        // Normalize to integers for reliable comparison (DB may return string ids)
        $ids = array_map('intval', $ids);
        $by_id = array();
        foreach ($hotels as $h) {
            $id = (int) $h->id;
            if (in_array($id, $ids, true)) {
                $by_id[$id] = $h;
            }
        }
        // Preserve order of selection (checkbox order from admin)
        $ordered = array();
        foreach ($ids as $id) {
            if (isset($by_id[$id])) {
                $ordered[] = $by_id[$id];
            }
        }
        return $ordered;
    }

    /**
     * Display hotel map shortcode (Map 1 - Standard)
     */
    public function display_hotel_map($atts) {
        $atts = shortcode_atts(array(
            'province' => '',
            'city' => '',
            'height' => '531px'
        ), $atts);
        
        $hotels = DHR_Hotel_Database::get_all_hotels('active');
        
        // Filter by province or city if specified
        if (!empty($atts['province'])) {
            $hotels = array_filter($hotels, function($hotel) use ($atts) {
                return strtolower($hotel->province) === strtolower($atts['province']);
            });
        }
        
        if (!empty($atts['city'])) {
            $hotels = array_filter($hotels, function($hotel) use ($atts) {
                return strtolower($hotel->city) === strtolower($atts['city']);
            });
        }
        
        // Get map config and decode settings
        $map_config = DHR_Hotel_Database::get_map_config('dhr_hotel_map');
        $settings = self::get_map_settings($map_config);
        $hotels = self::filter_hotels_by_map_selection($hotels, $settings);
        
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/hotel-map.php';
        return ob_get_clean();
    }
    
    /**
     * Display head office map shortcode (Map 2)
     */
    public function display_head_office_map($atts) {
        $atts = shortcode_atts(array(
            'height' => '596px'
        ), $atts);
        
        $hotels = DHR_Hotel_Database::get_all_hotels('active');
        $map_config = DHR_Hotel_Database::get_map_config('dhr_head_office_map');
        $settings = self::get_map_settings($map_config);
        $hotels = self::filter_hotels_by_map_selection($hotels, $settings);
        
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/head-office-map.php';
        return ob_get_clean();
    }
    
    /**
     * Display partner portfolio map shortcode (Map 3)
     */
    public function display_partner_portfolio_map($atts) {
        $atts = shortcode_atts(array(
            'height' => '1002px'
        ), $atts);
        
        $hotels = DHR_Hotel_Database::get_all_hotels('active');
        $map_config = DHR_Hotel_Database::get_map_config('dhr_partner_portfolio_map');
        $settings = self::get_map_settings($map_config);
        $hotels = self::filter_hotels_by_map_selection($hotels, $settings);
        
        $cityblue_hotel_ids = isset($settings['selected_cityblue_hotel_ids']) ? array_map('intval', (array) $settings['selected_cityblue_hotel_ids']) : array();
        $dream_hotel_ids = isset($settings['selected_dream_hotel_ids']) ? array_map('intval', (array) $settings['selected_dream_hotel_ids']) : array();
        
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/partner-portfolio-map.php';
        return ob_get_clean();
    }
    
    /**
     * Display dining venue map shortcode (Map 4)
     */
    public function display_dining_venue_map($atts) {
        $atts = shortcode_atts(array(
            'height' => '620px'
        ), $atts);
        
        $hotels = DHR_Hotel_Database::get_all_hotels('active');
        $map_config = DHR_Hotel_Database::get_map_config('dhr_dining_venue_map');
        $settings = self::get_map_settings($map_config);
        $hotels = self::filter_hotels_by_map_selection($hotels, $settings);
        
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/dining-venue-map.php';
        return ob_get_clean();
    }
    
    /**
     * Display wedding venue map shortcode (Map 5)
     */
    public function display_wedding_venue_map($atts) {
        $atts = shortcode_atts(array(
            'height' => '600px'
        ), $atts);
        
        $hotels = DHR_Hotel_Database::get_all_hotels('active');
        $map_config = DHR_Hotel_Database::get_map_config('dhr_wedding_venue_map');
        $settings = self::get_map_settings($map_config);
        $hotels = self::filter_hotels_by_map_selection($hotels, $settings);
        
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/wedding-venue-map.php';
        return ob_get_clean();
    }
    
    /**
     * Display property portfolio map shortcode (Map 6)
     */
    public function display_property_portfolio_map($atts) {
        $atts = shortcode_atts(array(
            'height' => '600px'
        ), $atts);
        
        $hotels = DHR_Hotel_Database::get_all_hotels('active');
        $map_config = DHR_Hotel_Database::get_map_config('dhr_property_portfolio_map');
        $settings = self::get_map_settings($map_config);
        $hotels = self::filter_hotels_by_map_selection($hotels, $settings);
        
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/property-portfolio-map.php';
        return ob_get_clean();
    }
    
    /**
     * Display lodges & camps map shortcode (Map 7)
     */
    public function display_lodges_camps_map($atts) {
        $atts = shortcode_atts(array(
            'height' => '600px'
        ), $atts);
        
        $hotels = DHR_Hotel_Database::get_all_hotels('active');
        $map_config = DHR_Hotel_Database::get_map_config('dhr_lodges_camps_map');
        $settings = self::get_map_settings($map_config);
        $hotels = self::filter_hotels_by_map_selection($hotels, $settings);
        
        $lodges_hotel_ids = isset($settings['selected_lodges_hotel_ids']) ? array_map('intval', (array) $settings['selected_lodges_hotel_ids']) : array();
        $weddings_hotel_ids = isset($settings['selected_weddings_hotel_ids']) ? array_map('intval', (array) $settings['selected_weddings_hotel_ids']) : array();
        
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/lodges-camps-map.php';
        return ob_get_clean();
    }
    
    /**
     * Display conference map shortcode (Map 8)
     */
    public function display_conference_map($atts) {
        $atts = shortcode_atts(array(
            'height' => '600px'
        ), $atts);
        
        $hotels = DHR_Hotel_Database::get_all_hotels('active');
        $map_config = DHR_Hotel_Database::get_map_config('dhr_conference_map');
        $settings = self::get_map_settings($map_config);
        $hotels = self::filter_hotels_by_map_selection($hotels, $settings);
        
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/conference-map.php';
        return ob_get_clean();
    }
    
    /**
     * Display Where To Find Us map shortcode (Map 9)
     */
    public function display_where_to_find_us_map($atts) {
        $atts = shortcode_atts(array(
            'height' => '550px',
            'property_id' => 0
        ), $atts);
        
        $hotels = DHR_Hotel_Database::get_all_hotels('active');
        $map_config = DHR_Hotel_Database::get_map_config('dhr_where_to_find_us_map');
        $settings = self::get_map_settings($map_config);
        $hotels = self::filter_hotels_by_map_selection($hotels, $settings);

        $property_id = isset($atts['property_id']) ? (int) $atts['property_id'] : 0;
        if ($property_id <= 0 && function_exists('is_singular') && is_singular('properties')) {
            $property_id = (int) get_the_ID();
        }

        if ($property_id > 0) {
            $property_map = DHR_Hotel_Database::get_where_to_find_us_property_map($property_id);
            if (!empty($property_map)) {
                $settings['main_heading'] = isset($property_map['main_heading']) ? $property_map['main_heading'] : (isset($settings['main_heading']) ? $settings['main_heading'] : 'Where To Find Us');
                $settings['address_text'] = isset($property_map['address_text']) ? $property_map['address_text'] : '';
                $settings['phone_label'] = isset($property_map['phone_label']) ? $property_map['phone_label'] : '';
                $settings['phone_number'] = isset($property_map['phone_number']) ? $property_map['phone_number'] : '';
                $settings['email_address'] = isset($property_map['email_address']) ? $property_map['email_address'] : '';
                $settings['enquire_text'] = isset($property_map['enquire_text']) ? $property_map['enquire_text'] : 'Enquire now';

                $lat = isset($property_map['latitude']) ? (float) $property_map['latitude'] : 0;
                $lng = isset($property_map['longitude']) ? (float) $property_map['longitude'] : 0;

                $pseudo_hotel = (object) array(
                    'id' => $property_id,
                    'name' => get_the_title($property_id),
                    'address' => isset($property_map['address_text']) ? $property_map['address_text'] : '',
                    'city' => '',
                    'province' => '',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'phone' => isset($property_map['phone_number']) ? $property_map['phone_number'] : '',
                    'email' => isset($property_map['email_address']) ? $property_map['email_address'] : '',
                    'image_url' => isset($property_map['property_image']) ? $property_map['property_image'] : '',
                    'logo_url' => isset($property_map['property_logo_image']) ? $property_map['property_logo_image'] : '',
                    'google_maps_url' => ($lat && $lng) ? ('https://www.google.com/maps?q=' . $lat . ',' . $lng) : '',
                    'hotel_code' => 'property-' . $property_id,
                );

                $hotels = array($pseudo_hotel);
                $settings['default_hotel_code'] = 'property-' . $property_id;
            }
        }
        
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/where-to-find-us-map.php';
        return ob_get_clean();
    }
    
    /**
     * [hotel_rooms] – grid layout (specs, amenities, description). No code change, only shortcode.
     */
    public function display_hotel_rooms($atts) {
        return $this->render_hotel_rooms($atts, 'grid');
    }

    /**
     * [hotel_rooms_cards] – card overlay layout. No code change, only shortcode.
     */
    public function display_hotel_rooms_cards($atts) {
        return $this->render_hotel_rooms($atts, 'cards');
    }

    /**
     * [hotel_rooms_second] – alternate design (horizontal card layout).
     */
    public function display_hotel_rooms_second($atts) {
        $atts = is_array($atts) ? $atts : array();
        $atts['_shortcode'] = 'hotel_rooms_second';
        return $this->render_hotel_rooms($atts, 'grid_second');
    }

    /**
     * Shared renderer: fetches rooms and passes layout so template shows one design only.
     */
    private function render_hotel_rooms($atts, $layout) {
        $atts = shortcode_atts(array(
            'columns' => '2',
            'show_images' => 'true',
            'show_amenities' => 'true',
            'show_description' => 'true',
            '_shortcode' => ''
        ), $atts);

        $hotel_code = get_option('bys_hotel_code', '');
        $hotel_code = is_string($hotel_code) ? trim($hotel_code) : '';

        if (empty($hotel_code)) {
            $settings_url = admin_url('admin.php?page=book-your-stay');
            $sc = !empty($atts['_shortcode']) ? '[' . $atts['_shortcode'] . ']' : ($layout === 'cards' ? '[hotel_rooms_cards]' : '[hotel_rooms]');
            $message = sprintf(
                __('Hotel code is required. Set it in %s. Use shortcode: %s', 'dhr-hotel-management'),
                '<a href="' . esc_url($settings_url) . '">' . __('Book Your Stay Settings', 'dhr-hotel-management') . '</a>',
                $sc
            );
            return '<p class="dhr-hotel-rooms-error">' . $message . '</p>';
        }

        $api = new DHR_Hotel_API();
        $result = $api->get_shr_hotel_rooms($hotel_code);

        if (!$result['success']) {
            return '<p class="dhr-hotel-rooms-error">' . esc_html($result['error']) . '</p>';
        }

        $rooms = isset($result['rooms']) ? $result['rooms'] : array();
        $hotel_name = isset($result['hotel_name']) ? $result['hotel_name'] : $hotel_code;

        if (empty($rooms)) {
            return '<p class="dhr-hotel-rooms-error">' . sprintf(__('No rooms found for hotel %s.', 'dhr-hotel-management'), esc_html($hotel_name)) . '</p>';
        }

        // Fetch room-wise rates from rateCalendar API for each room that has room_type_id
        $today    = function_exists('wp_date') ? wp_date('Y-m-d') : date('Y-m-d', current_time('timestamp'));
        $max_date = date('Y-m-d', strtotime($today . ' +1 month'));
        foreach ($rooms as $room) {
            $room->from_price = 0;
            if (!empty($room->room_type_id)) {
                $rate_result = $api->get_shr_rate_calendar($hotel_code, $room->room_type_id, array(
                    'year'        => (int) date('Y'),
                    'month'       => (int) date('n'),
                    'checkInDate' => $today,
                    'minDate'     => $today,
                    'maxDate'     => $max_date,
                ));
                if (!empty($rate_result['success']) && isset($rate_result['from_price'])) {
                    $room->from_price = (int) $rate_result['from_price'];
                }
            }
        }

        $channel_id = (int) get_option('dhr_shr_channel_id', '30');
        $hotel_data = array(
            'layout' => $layout,
            'hotel_code' => $hotel_code,
            'hotel_name' => $hotel_name,
            'channel_id' => $channel_id,
            'rooms' => $rooms,
            'columns' => intval($atts['columns']),
            'show_images' => filter_var($atts['show_images'], FILTER_VALIDATE_BOOLEAN),
            'show_amenities' => filter_var($atts['show_amenities'], FILTER_VALIDATE_BOOLEAN),
            'show_description' => filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN)
        );

        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/hotel-rooms.php';
        return ob_get_clean();
    }
    
    /**
     * Get packages for frontend display (active, within valid date range) with details and hotel info.
     * Optionally filter by category IDs.
     *
     * @param int[] $category_ids Optional. Category IDs to filter by. Empty = all categories.
     * @return array List of items: [ 'package' => object, 'details' => object|null, 'hotel' => object|null ]
     */
    public static function get_packages_for_display($category_ids = array()) {
        $category_ids = array_filter(array_map('intval', (array) $category_ids));
        $packages = empty($category_ids)
            ? DHR_Hotel_Database::get_available_packages()
            : DHR_Hotel_Database::get_available_packages_by_category_ids($category_ids);
        $out = array();
        foreach ($packages as $pkg) {
            $details = DHR_Hotel_Database::get_package_details($pkg->id);
            $hotel = !empty($pkg->hotel_code) ? DHR_Hotel_Database::get_hotel_by_code($pkg->hotel_code) : null;
            $out[] = array(
                'package'  => $pkg,
                'details'  => $details,
                'hotel'    => $hotel,
            );
        }
        return $out;
    }

    /**
     * Shortcode [dhr_packages]: category-wise package display.
     * Attributes: categories (comma-separated category IDs), design (first_design|second_design|kids_design|early_bird_design).
     * For second_design grid: wrap shortcode in <div class="package-design-grid">...</div> (static class, no layout attribute).
     */
    public function display_packages_by_category($atts) {
        $atts = shortcode_atts(array(
            'categories' => '',
            'design'     => 'first_design',
        ), $atts, 'dhr_packages');
        $category_ids = array();
        if (!empty($atts['categories'])) {
            $category_ids = array_filter(array_map('intval', array_map('trim', explode(',', $atts['categories']))));
        }
        $packages = self::get_packages_for_display($category_ids);
        $plugin_url = DHR_HOTEL_PLUGIN_URL;
        $design = in_array($atts['design'], array('first_design', 'second_design', 'kids_design', 'early_bird_design'), true)
            ? $atts['design']
            : 'first_design';
        $templates = array(
            'first_design'      => 'package-first-design.php',
            'second_design'     => 'package-second-design.php',
            'kids_design'      => 'package-kids-design.php',
            'early_bird_design' => 'package-early-bird-design.php',
        );
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/' . $templates[$design];
        return ob_get_clean();
    }

    /**
     * Display first package design shortcode – data from database, same design.
     */
    public function display_package_first_design($atts) {
        $atts = shortcode_atts(array(), $atts);
        $packages = self::get_packages_for_display();
        $plugin_url = DHR_HOTEL_PLUGIN_URL;
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/package-first-design.php';
        return ob_get_clean();
    }

    /**
     * Display second package design shortcode – data from database, same design.
     */
    public function display_package_second_design($atts) {
        $atts = shortcode_atts(array(), $atts);
        $packages = self::get_packages_for_display();
        $plugin_url = DHR_HOTEL_PLUGIN_URL;
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/package-second-design.php';
        return ob_get_clean();
    }

    /**
     * Display kids package design shortcode – data from database, same design.
     */
    public function display_package_kids_design($atts) {
        $atts = shortcode_atts(array(), $atts);
        $packages = self::get_packages_for_display();
        $plugin_url = DHR_HOTEL_PLUGIN_URL;
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/package-kids-design.php';
        return ob_get_clean();
    }

    /**
     * Display early bird package design shortcode – data from database, same design.
     */
    public function display_package_early_bird_design($atts) {
        $atts = shortcode_atts(array(), $atts);
        $packages = self::get_packages_for_display();
        $plugin_url = DHR_HOTEL_PLUGIN_URL;
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/package-early-bird-design.php';
        return ob_get_clean();
    }
    
    /**
     * Display experiences package design shortcode
     */
    public function display_package_experiences_design($atts) {
        $atts = shortcode_atts(array(), $atts);
        
        ob_start();
        include DHR_HOTEL_PLUGIN_PATH . 'templates/frontend/package-experiences-design.php';
        return ob_get_clean();
    }
}

