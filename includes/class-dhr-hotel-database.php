<?php
/**
 * Database handler for DHR Hotel Management
 */

if (!defined('ABSPATH')) {
    exit;
}

class DHR_Hotel_Database {
    
    /**
     * Create database table on plugin activation
     */
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'dhr_hotels';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        hotel_code varchar(50) DEFAULT NULL,
        name varchar(255) NOT NULL,
        description text,
        address varchar(500) NOT NULL,
        city varchar(100) NOT NULL,
        province varchar(100) NOT NULL,
        country varchar(100) DEFAULT 'South Africa',
        latitude decimal(10, 8) NOT NULL,
        longitude decimal(11, 8) NOT NULL,
        phone varchar(50),
        email varchar(255),
        website varchar(255),
        image_url varchar(500),
        logo_url varchar(500),
        google_maps_url varchar(500),
        status varchar(20) DEFAULT 'active',
        manual_entry tinyint(1) NOT NULL DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY hotel_code (hotel_code)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        self::maybe_upgrade_dhr_hotels_table($table_name);

        // Add FULLTEXT index for search (only on text columns; id/lat/long/dates cannot be in FULLTEXT)
        $fulltext_index = $wpdb->prefix . 'dhr_hotels_fulltext';
        $index_exists   = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = %s AND table_name = %s AND index_name = %s",
            DB_NAME,
            $table_name,
            $fulltext_index
        ));
        if (!$index_exists) {
            $wpdb->query("ALTER TABLE `$table_name` ADD FULLTEXT INDEX `$fulltext_index` (`name`, `description`, `address`, `city`, `province`, `country`, `phone`, `email`, `website`, `image_url`, `google_maps_url`, `status`)");
        }

        // Create map configurations table
        $map_config_table = $wpdb->prefix . 'dhr_map_configs';
        $sql_map = "CREATE TABLE IF NOT EXISTS $map_config_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            map_type varchar(50) NOT NULL,
            map_name varchar(255) NOT NULL,
            shortcode varchar(100) NOT NULL,
            settings longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY shortcode (shortcode)
        ) $charset_collate;";
        
        dbDelta($sql_map);
        
        // Create hotel details table
        $hotel_details_table = $wpdb->prefix . 'dhr_hotel_details';
        $sql_details = "CREATE TABLE IF NOT EXISTS $hotel_details_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            hotel_code varchar(50) NOT NULL,
            hotel_name varchar(255) NOT NULL,
            chain_code varchar(50),
            chain_name varchar(255),
            currency_code varchar(10),
            language_code varchar(10),
            time_zone varchar(50),
            when_built varchar(50),
            hotel_status varchar(50),
            hotel_status_code varchar(10),
            latitude decimal(10, 8),
            longitude decimal(11, 8),
            description text,
            renovation_text text,
            check_in_time varchar(20),
            check_out_time varchar(20),
            cancellation_policy text,
            guarantee_policy text,
            pets_allowed varchar(50),
            commission_percent decimal(5, 2),
            raw_xml_data longtext,
            last_synced_at datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY hotel_code (hotel_code)
        ) $charset_collate;";
        
        dbDelta($sql_details);
        
        // Create hotel rooms table
        $hotel_rooms_table = $wpdb->prefix . 'dhr_hotel_rooms';
        $sql_rooms = "CREATE TABLE IF NOT EXISTS $hotel_rooms_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            hotel_code varchar(50) NOT NULL,
            room_type_name varchar(255) NOT NULL,
            room_type_code varchar(50),
            max_occupancy int(11),
            max_adult_occupancy int(11),
            max_child_occupancy int(11),
            standard_num_beds int(11),
            standard_occupancy int(11),
            room_size decimal(10, 2),
            description text,
            amenities longtext,
            images longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY hotel_code (hotel_code)
        ) $charset_collate;";
        
        dbDelta($sql_rooms);
        
        // Create hotel services table
        $hotel_services_table = $wpdb->prefix . 'dhr_hotel_services';
        $sql_services = "CREATE TABLE IF NOT EXISTS $hotel_services_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            hotel_code varchar(50) NOT NULL,
            service_code varchar(50),
            service_name varchar(255),
            exists_code varchar(100),
            proximity_code varchar(10),
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY hotel_code (hotel_code)
        ) $charset_collate;";
        
        dbDelta($sql_services);

        // Create categories table
        $categories_table = $wpdb->prefix . 'dhr_categories';
        $sql_categories = "CREATE TABLE IF NOT EXISTS $categories_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            subtitle varchar(255) DEFAULT NULL,
            description text,
            image_url varchar(500) DEFAULT NULL,
            icon_url varchar(500) DEFAULT NULL,
            icon_svg text DEFAULT NULL,
            icon_type varchar(20) DEFAULT 'url',
            view_package_url varchar(500) DEFAULT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_by bigint(20) DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by bigint(20) DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql_categories);
        self::maybe_upgrade_dhr_categories_table($categories_table);

        // Create packages table
        $packages_table = $wpdb->prefix . 'dhr_packages';
        $sql_packages = "CREATE TABLE IF NOT EXISTS $packages_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            package_code varchar(100) NOT NULL,
            hotel_code varchar(50) NOT NULL,
            category_id int(11) NOT NULL,
            valid_from datetime NOT NULL,
            valid_to datetime NOT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_by bigint(20) DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY hotel_code (hotel_code),
            KEY category_id (category_id),
            KEY valid_dates (valid_from, valid_to)
        ) $charset_collate;";
        dbDelta($sql_packages);

        // Create package details table (SHR API response data per package)
        $package_details_table = $wpdb->prefix . 'dhr_package_details';
        $sql_package_details = "CREATE TABLE IF NOT EXISTS $package_details_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            package_id int(11) NOT NULL,
            hotel_code varchar(50) NOT NULL,
            package_code varchar(100) NOT NULL,
            shr_product_id bigint(20) DEFAULT NULL,
            name varchar(500) DEFAULT NULL,
            description longtext,
            images longtext,
            policies longtext,
            rate_code_id bigint(20) DEFAULT NULL,
            begin_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            raw_response longtext,
            last_synced_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY package_id (package_id),
            KEY hotel_code (hotel_code),
            KEY package_code (package_code)
        ) $charset_collate;";
        dbDelta($sql_package_details);
        
        // Insert default map configurations if they don't exist
        self::create_default_map_configs();
    }

    /**
     * Add missing columns to wp_dhr_hotels if the table was created with an older schema.
     */
    public static function maybe_upgrade_dhr_hotels_table($table_name) {
        global $wpdb;

        $existing = $wpdb->get_col($wpdb->prepare(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table_name
        ));
        if (!is_array($existing)) {
            return;
        }

        $columns_to_add = array(
            'hotel_code'      => "ADD COLUMN hotel_code varchar(50) DEFAULT NULL AFTER id",
            'name'            => "ADD COLUMN name varchar(255) NOT NULL DEFAULT '' AFTER hotel_code",
            'description'     => "ADD COLUMN description text AFTER name",
            'address'         => "ADD COLUMN address varchar(500) NOT NULL DEFAULT '' AFTER description",
            'city'            => "ADD COLUMN city varchar(100) NOT NULL DEFAULT '' AFTER address",
            'province'        => "ADD COLUMN province varchar(100) NOT NULL DEFAULT '' AFTER city",
            'country'         => "ADD COLUMN country varchar(100) DEFAULT 'South Africa' AFTER province",
            'latitude'        => "ADD COLUMN latitude decimal(10,8) NOT NULL DEFAULT 0 AFTER country",
            'longitude'       => "ADD COLUMN longitude decimal(11,8) NOT NULL DEFAULT 0 AFTER latitude",
            'phone'           => "ADD COLUMN phone varchar(50) DEFAULT NULL AFTER longitude",
            'email'           => "ADD COLUMN email varchar(255) DEFAULT NULL AFTER phone",
            'website'         => "ADD COLUMN website varchar(255) DEFAULT NULL AFTER email",
            'image_url'       => "ADD COLUMN image_url varchar(500) DEFAULT NULL AFTER website",
            'logo_url'        => "ADD COLUMN logo_url varchar(500) DEFAULT NULL AFTER image_url",
            'google_maps_url' => "ADD COLUMN google_maps_url varchar(500) DEFAULT NULL AFTER logo_url",
            'status'          => "ADD COLUMN status varchar(20) DEFAULT 'active' AFTER google_maps_url",
            'manual_entry'    => "ADD COLUMN manual_entry tinyint(1) NOT NULL DEFAULT 0 AFTER status",
            'created_at'      => "ADD COLUMN created_at datetime DEFAULT CURRENT_TIMESTAMP AFTER manual_entry",
            'updated_at'      => "ADD COLUMN updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
        );

        foreach ($columns_to_add as $col => $alter_sql) {
            if (!in_array($col, $existing, true)) {
                $wpdb->query("ALTER TABLE `$table_name` " . $alter_sql);
            }
        }

        if (!in_array('hotel_code', $existing, true)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD UNIQUE KEY hotel_code (hotel_code)");
        }
    }

    /**
     * Add missing columns to wp_dhr_categories (e.g. subtitle) for existing installs.
     */
    public static function maybe_upgrade_dhr_categories_table($table_name) {
        global $wpdb;
        $existing = $wpdb->get_col($wpdb->prepare(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table_name
        ));
        if (!is_array($existing)) {
            return;
        }
        if (!in_array('subtitle', $existing, true)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN subtitle varchar(255) DEFAULT NULL AFTER title");
        }
        if (!in_array('icon_svg', $existing, true)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN icon_svg text DEFAULT NULL AFTER icon_url");
        }
        if (!in_array('icon_type', $existing, true)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN icon_type varchar(20) DEFAULT 'url' AFTER icon_svg");
        }
        if (!in_array('view_package_url', $existing, true)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN view_package_url varchar(500) DEFAULT NULL AFTER icon_type");
        }
    }

    /**
     * Create default map configurations
     */
    public static function create_default_map_configs() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_map_configs';
        
        $default_maps = array(
            array(
                'map_type' => 'standard',
                'map_name' => 'Standard Map',
                'shortcode' => 'dhr_hotel_map',
                'settings' => json_encode(array(
                    'location_heading' => 'LOCATED IN THE WESTERN CAPE',
                    'main_heading' => 'Find Us',
                    'description_text' => 'Discover our hotel locations across the Western Cape. Click on any marker to view hotel details and make a reservation.',
                    'reservation_label' => 'RESERVATION BY PHONE',
                    'reservation_phone' => '+27 (0)13 243 9401/2',
                    'view_on_google_maps_link' => '',
                    'view_on_google_maps_text' => 'View On Google Maps',
                    'book_now_text' => 'Book Now'
                ))
            ),
            array(
                'map_type' => 'head_office',
                'map_name' => 'Head Office Map',
                'shortcode' => 'dhr_head_office_map',
                'settings' => json_encode(array(
                    'title' => 'Head Office',
                    'address' => '330 Main Road, Bryanston 2021, Gauteng, South Africa',
                    'latitude' => '-26.0519',
                    'longitude' => '28.0231',
                    'google_maps_url' => ''
                ))
            ),
            array(
                'map_type' => 'partner_portfolio',
                'map_name' => 'Partner Portfolio Map',
                'shortcode' => 'dhr_partner_portfolio_map',
                'settings' => json_encode(array(
                    'overview_label' => 'DISCOVER AFRICA',
                    'main_heading' => 'Our Partner Portfolio',
                    'description' => 'Together with CityBlue Hotels, we\'re crafting a unified hospitality experience that celebrates the rich cultures, stunning landscapes, and warm hospitality that Africa is known for. Whether you\'re seeking adventure, relaxation, or a blend of both, our properties are designed to connect you to the heart of Africa.',
                    'legend_cityblue' => 'CityBlue Hotels',
                    'legend_dream' => 'Dream Hotels & Resorts',
                    'selected_cityblue_hotel_ids' => array(),
                    'selected_dream_hotel_ids' => array()
                ))
            ),
            array(
                'map_type' => 'dining_venue',
                'map_name' => 'Dining Venue Map',
                'shortcode' => 'dhr_dining_venue_map',
                'settings' => json_encode(array(
                    'overview_label' => 'OVERVIEW',
                    'main_heading' => 'Find A Dining Venue',
                    'description' => 'Whether you\'re savoring fresh seafood with a view of Table Mountain or indulging in gourmet delights by the Indian Ocean, our dining experiences promise to delight every palate. Join us for an unforgettable gastronomic adventure across our exquisite destinations.',
                    'reservation_label' => 'RESERVATION BY PHONE',
                    'reservation_phone' => '+27 (0)13 243 9401/2',
                    'dropdown_placeholder' => 'Select a Hotel'
                ))
            ),
            array(
                'map_type' => 'wedding_venue',
                'map_name' => 'Wedding Venue Map',
                'shortcode' => 'dhr_wedding_venue_map',
                'settings' => json_encode(array(
                    'header_label' => 'WEDDINGS',
                    'main_heading' => 'Find A Wedding Venue For Your Dream Celebration',
                    'description' => 'Embraced by the tranquil beauty of lakes, sunlit beaches, wild African bushveld, and majestic mountain views, our venues offer stunning settings that will transform your special moments into unforgettable memories.',
                    'reservation_label' => 'RESERVATION BY PHONE',
                    'reservation_phone' => '+27 (0)13 243 9401/2',
                    'dropdown_placeholder' => 'Select a Hotel'
                ))
            ),
            array(
                'map_type' => 'property_portfolio',
                'map_name' => 'Ownership Property Portfolio Map',
                'shortcode' => 'dhr_property_portfolio_map',
                'settings' => json_encode(array(
                    'panel_title' => 'Ownership Property Portfolio',
                    'show_numbers' => true
                ))
            ),
            array(
                'map_type' => 'lodges_camps',
                'map_name' => 'Lodges & Camps Map',
                'shortcode' => 'dhr_lodges_camps_map',
                'settings' => json_encode(array(
                    'panel_title' => 'Lodges & Camps',
                    'legend_lodges' => 'Lodges & Camps',
                    'legend_weddings' => 'Weddings & Conferences',
                    'show_list' => true,
                    'selected_lodges_hotel_ids' => array(),
                    'selected_weddings_hotel_ids' => array()
                ))
            ),
            array(
                'map_type' => 'conference',
                'map_name' => 'Conference Map',
                'shortcode' => 'dhr_conference_map',
                'settings' => json_encode(array(
                    'header_label' => 'CONFERENCES',
                    'main_heading' => 'Find A Conference Venue For Your Next Event',
                    'description' => 'From intimate boardroom meetings to large-scale conferences, our venues offer world-class facilities equipped with modern technology, flexible spaces, and dedicated event coordinators to ensure your business gathering is a resounding success.',
                    'reservation_label' => 'RESERVATION BY PHONE',
                    'reservation_phone' => '+27 (0)13 243 9401/2',
                    'dropdown_placeholder' => 'Select a Venue',
                    'book_now_text' => 'Get A Quote'
                ))
            ),
            array(
                'map_type' => 'where_to_find_us',
                'map_name' => 'Where To Find Us Map',
                'shortcode' => 'dhr_where_to_find_us_map',
                'settings' => json_encode(array(
                    'main_heading' => 'Where To Find Us',
                    'address_text' => '',
                    'phone_label' => '',
                    'phone_number' => '',
                    'email_address' => '',
                    'gps_coordinates' => '',
                    'enquire_text' => 'Enquire now',
                    'enquire_url' => '',
                    'logo_url' => '',
                    'bg_color' => '#8FA7BF'
                ))
            )
        );
        
        foreach ($default_maps as $map) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE shortcode = %s",
                $map['shortcode']
            ));
            
            if ($exists == 0) {
                $wpdb->insert($table_name, $map, array('%s', '%s', '%s', '%s', '%s'));
            }
        }
    }
    
    /**
     * Get all map configurations
     */
    public static function get_all_map_configs() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_map_configs';
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        ));
        
        if (!$table_exists) {
            // Table doesn't exist, create it
            self::create_tables();
        }
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY id ASC");
    }
    
    /**
     * Get map configuration by shortcode
     */
    public static function get_map_config($shortcode) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_map_configs';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE shortcode = %s",
            $shortcode
        ));
    }
    
    /**
     * Update map configuration
     */
    public static function update_map_config($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_map_configs';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'map_name' => sanitize_text_field($data['map_name']),
                'settings' => json_encode($data['settings']),
                'status' => sanitize_text_field($data['status'])
            ),
            array('id' => intval($id)),
            array('%s', '%s', '%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get all hotels
     */
    public static function get_all_hotels($status = 'all') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotels';
        
        if ($status === 'all') {
            return $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        } else {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE status = %s ORDER BY created_at DESC",
                $status
            ));
        }
    }
    
    /**
     * Get hotel by ID
     */
    public static function get_hotel($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotels';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
    }

    /**
     * Get hotel by SHR/remote hotel code
     *
     * @param string $hotel_code
     * @return object|null
     */
    public static function get_hotel_by_code($hotel_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotels';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE hotel_code = %s",
            $hotel_code
        ));
    }
    
    /**
     * Insert new hotel
     * All values are coerced to non-null to avoid DB errors (NOT NULL columns).
     */
    public static function insert_hotel($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotels';

        $data = wp_parse_args($data, array(
            'hotel_code'      => '',
            'name'            => '',
            'description'     => '',
            'address'         => '',
            'city'            => '',
            'province'        => '',
            'country'         => 'South Africa',
            'latitude'        => 0,
            'longitude'       => 0,
            'phone'           => '',
            'email'           => '',
            'website'         => '',
            'image_url'       => '',
            'logo_url'        => '',
            'google_maps_url' => '',
            'status'          => 'active',
            'manual_entry'    => 0,
        ));

        $code_raw = sanitize_text_field(wp_unslash((string) ($data['hotel_code'] ?? '')));
        // Several manual hotels may omit a code; UNIQUE allows multiple NULLs but not duplicate empty strings.
        $hotel_code_val = ($code_raw === '') ? null : $code_raw;

        $row = array(
            'hotel_code'      => $hotel_code_val,
            'name'            => sanitize_text_field(wp_unslash((string) ($data['name'] ?? ''))) ?: 'Hotel',
            'description'     => sanitize_textarea_field(wp_unslash((string) ($data['description'] ?? ''))),
            'address'         => sanitize_text_field(wp_unslash((string) ($data['address'] ?? ''))),
            'city'            => sanitize_text_field(wp_unslash((string) ($data['city'] ?? ''))),
            'province'        => sanitize_text_field(wp_unslash((string) ($data['province'] ?? ''))),
            'country'         => sanitize_text_field(wp_unslash((string) ($data['country'] ?? ''))) ?: 'South Africa',
            'latitude'        => floatval($data['latitude'] ?? 0),
            'longitude'       => floatval($data['longitude'] ?? 0),
            'phone'           => sanitize_text_field(wp_unslash((string) ($data['phone'] ?? ''))),
            'email'           => sanitize_email(wp_unslash((string) ($data['email'] ?? ''))),
            'website'         => esc_url_raw((string) ($data['website'] ?? '')) ?: '',
            'image_url'       => esc_url_raw((string) ($data['image_url'] ?? '')) ?: '',
            'logo_url'        => esc_url_raw((string) ($data['logo_url'] ?? '')) ?: '',
            'google_maps_url' => esc_url_raw((string) ($data['google_maps_url'] ?? '')) ?: '',
            'status'          => sanitize_text_field(wp_unslash((string) ($data['status'] ?? ''))) ?: 'active',
            'manual_entry'    => !empty($data['manual_entry']) ? 1 : 0,
        );

        $result = $wpdb->insert(
            $table_name,
            $row,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
        );

        if ($result === false && defined('WP_DEBUG') && WP_DEBUG && $wpdb->last_error) {
            error_log('DHR insert_hotel failed: ' . $wpdb->last_error);
        }

        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Update hotel
     */
    public static function update_hotel($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotels';

        $set_manual_entry = array_key_exists('manual_entry', $data);
        $manual_entry_val = $set_manual_entry ? (!empty($data['manual_entry']) ? 1 : 0) : null;
        if ($set_manual_entry) {
            unset($data['manual_entry']);
        }

        $data = wp_parse_args($data, array(
            'hotel_code' => '', 'name' => '', 'description' => '', 'address' => '', 'city' => '', 'province' => '',
            'country' => 'South Africa', 'latitude' => 0, 'longitude' => 0, 'phone' => '', 'email' => '',
            'website' => '', 'image_url' => '', 'logo_url' => '', 'google_maps_url' => '', 'status' => 'active',
        ));

        $row = array(
            'hotel_code'      => sanitize_text_field(wp_unslash((string) ($data['hotel_code'] ?? ''))),
            'name'            => sanitize_text_field(wp_unslash((string) ($data['name'] ?? ''))) ?: 'Hotel',
            'description'     => sanitize_textarea_field(wp_unslash((string) ($data['description'] ?? ''))),
            'address'         => sanitize_text_field(wp_unslash((string) ($data['address'] ?? ''))),
            'city'            => sanitize_text_field(wp_unslash((string) ($data['city'] ?? ''))),
            'province'        => sanitize_text_field(wp_unslash((string) ($data['province'] ?? ''))),
            'country'         => sanitize_text_field(wp_unslash((string) ($data['country'] ?? ''))) ?: 'South Africa',
            'latitude'        => floatval($data['latitude'] ?? 0),
            'longitude'       => floatval($data['longitude'] ?? 0),
            'phone'           => sanitize_text_field(wp_unslash((string) ($data['phone'] ?? ''))),
            'email'           => sanitize_email(wp_unslash((string) ($data['email'] ?? ''))),
            'website'         => esc_url_raw((string) ($data['website'] ?? '')) ?: '',
            'image_url'       => esc_url_raw((string) ($data['image_url'] ?? '')) ?: '',
            'logo_url'        => esc_url_raw((string) ($data['logo_url'] ?? '')) ?: '',
            'google_maps_url' => esc_url_raw((string) ($data['google_maps_url'] ?? '')) ?: '',
            'status'          => sanitize_text_field(wp_unslash((string) ($data['status'] ?? ''))) ?: 'active',
        );

        $formats = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
        if ($set_manual_entry) {
            $row['manual_entry'] = $manual_entry_val;
            $formats[] = '%d';
        }

        $result = $wpdb->update(
            $table_name,
            $row,
            array('id' => intval($id)),
            $formats,
            array('%d')
        );

        if ($result === false && defined('WP_DEBUG') && WP_DEBUG && $wpdb->last_error) {
            error_log('DHR update_hotel failed: ' . $wpdb->last_error);
        }

        return $result !== false;
    }
    
    /**
     * Delete hotel
     */
    public static function delete_hotel($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotels';
        
        return $wpdb->delete(
            $table_name,
            array('id' => intval($id)),
            array('%d')
        );
    }
    
    /**
     * Save or update hotel details
     */
    public static function save_hotel_details($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotel_details';
        
        // Ensure table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        ));
        
        if (!$table_exists) {
            self::create_tables();
        }
        
        // Prepare data - convert empty strings to empty strings (not null) for text fields
        // WordPress wpdb handles null for numeric fields, but we'll use 0.0 for floats if null
        $prepared_data = array(
            'hotel_name' => !empty($data['hotel_name']) ? sanitize_text_field($data['hotel_name']) : '',
            'chain_code' => !empty($data['chain_code']) ? sanitize_text_field($data['chain_code']) : '',
            'chain_name' => !empty($data['chain_name']) ? sanitize_text_field($data['chain_name']) : '',
            'currency_code' => !empty($data['currency_code']) ? sanitize_text_field($data['currency_code']) : '',
            'language_code' => !empty($data['language_code']) ? sanitize_text_field($data['language_code']) : '',
            'time_zone' => !empty($data['time_zone']) ? sanitize_text_field($data['time_zone']) : '',
            'when_built' => !empty($data['when_built']) ? sanitize_text_field($data['when_built']) : '',
            'hotel_status' => !empty($data['hotel_status']) ? sanitize_text_field($data['hotel_status']) : '',
            'hotel_status_code' => !empty($data['hotel_status_code']) ? sanitize_text_field($data['hotel_status_code']) : '',
            'latitude' => (!empty($data['latitude']) && $data['latitude'] !== '' && $data['latitude'] !== '0') ? floatval($data['latitude']) : null,
            'longitude' => (!empty($data['longitude']) && $data['longitude'] !== '' && $data['longitude'] !== '0') ? floatval($data['longitude']) : null,
            'description' => !empty($data['description']) ? sanitize_textarea_field($data['description']) : '',
            'renovation_text' => !empty($data['renovation_text']) ? sanitize_textarea_field($data['renovation_text']) : '',
            'check_in_time' => !empty($data['check_in_time']) ? sanitize_text_field($data['check_in_time']) : '',
            'check_out_time' => !empty($data['check_out_time']) ? sanitize_text_field($data['check_out_time']) : '',
            'cancellation_policy' => !empty($data['cancellation_policy']) ? sanitize_textarea_field($data['cancellation_policy']) : '',
            'guarantee_policy' => !empty($data['guarantee_policy']) ? sanitize_textarea_field($data['guarantee_policy']) : '',
            'pets_allowed' => !empty($data['pets_allowed']) ? sanitize_text_field($data['pets_allowed']) : '',
            'commission_percent' => (!empty($data['commission_percent']) && $data['commission_percent'] !== '') ? floatval($data['commission_percent']) : null,
            'raw_xml_data' => !empty($data['raw_xml_data']) ? $data['raw_xml_data'] : '',
            'last_synced_at' => current_time('mysql')
        );
        
        // Format specifiers array - must match the order of fields above
        $format_specifiers = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s');
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE hotel_code = %s",
            $data['hotel_code']
        ));
        
        if ($existing) {
            // Update existing record
            $result = $wpdb->update(
                $table_name,
                $prepared_data,
                array('hotel_code' => sanitize_text_field($data['hotel_code'])),
                $format_specifiers,
                array('%s')
            );
            
            if ($result === false) {
                // Log error for debugging
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('DHR Hotel: Update failed - ' . $wpdb->last_error);
                }
                return false;
            }
            
            return $existing->id;
        } else {
            // Insert new record
            $prepared_data['hotel_code'] = sanitize_text_field($data['hotel_code']);
            // Add format for hotel_code at the beginning
            $insert_format_specifiers = array_merge(array('%s'), $format_specifiers);
            
            $result = $wpdb->insert(
                $table_name,
                $prepared_data,
                $insert_format_specifiers
            );
            
            if ($result === false) {
                // Log error for debugging
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('DHR Hotel: Insert failed - ' . $wpdb->last_error);
                    error_log('DHR Hotel: Last query - ' . $wpdb->last_query);
                }
                return false;
            }
            
            return $wpdb->insert_id;
        }
    }
    
    /**
     * Save hotel rooms
     */
    public static function save_hotel_rooms($hotel_code, $rooms) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotel_rooms';
        
        // Delete existing rooms for this hotel
        $wpdb->delete($table_name, array('hotel_code' => $hotel_code), array('%s'));
        
        // Insert new rooms
        foreach ($rooms as $room) {
            $wpdb->insert(
                $table_name,
                array(
                    'hotel_code' => sanitize_text_field($hotel_code),
                    'room_type_name' => sanitize_text_field($room['room_type_name']),
                    'room_type_code' => sanitize_text_field($room['room_type_code']),
                    'max_occupancy' => isset($room['max_occupancy']) ? intval($room['max_occupancy']) : null,
                    'max_adult_occupancy' => isset($room['max_adult_occupancy']) ? intval($room['max_adult_occupancy']) : null,
                    'max_child_occupancy' => isset($room['max_child_occupancy']) ? intval($room['max_child_occupancy']) : null,
                    'standard_num_beds' => isset($room['standard_num_beds']) ? intval($room['standard_num_beds']) : null,
                    'standard_occupancy' => isset($room['standard_occupancy']) ? intval($room['standard_occupancy']) : null,
                    'room_size' => isset($room['room_size']) ? floatval($room['room_size']) : null,
                    'description' => sanitize_textarea_field($room['description']),
                    'amenities' => json_encode($room['amenities']),
                    'images' => json_encode($room['images'])
                ),
                array('%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%f', '%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Save hotel services
     */
    public static function save_hotel_services($hotel_code, $services) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotel_services';
        
        // Delete existing services for this hotel
        $wpdb->delete($table_name, array('hotel_code' => $hotel_code), array('%s'));
        
        // Insert new services
        foreach ($services as $service) {
            $wpdb->insert(
                $table_name,
                array(
                    'hotel_code' => sanitize_text_field($hotel_code),
                    'service_code' => sanitize_text_field($service['service_code']),
                    'service_name' => sanitize_text_field($service['service_name']),
                    'exists_code' => sanitize_text_field($service['exists_code']),
                    'proximity_code' => sanitize_text_field($service['proximity_code']),
                    'description' => sanitize_textarea_field($service['description'])
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Get hotel details by hotel code
     */
    public static function get_hotel_details($hotel_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotel_details';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE hotel_code = %s",
            $hotel_code
        ));
    }
    
    /**
     * Get hotel rooms by hotel code
     */
    public static function get_hotel_rooms($hotel_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotel_rooms';
        
        $rooms = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE hotel_code = %s ORDER BY id ASC",
            $hotel_code
        ));
        
        // Decode JSON fields
        foreach ($rooms as $room) {
            $room->amenities = json_decode($room->amenities, true);
            $room->images = json_decode($room->images, true);
        }
        
        return $rooms;
    }
    
    /**
     * Get hotel services by hotel code
     */
    public static function get_hotel_services($hotel_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dhr_hotel_services';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE hotel_code = %s ORDER BY id ASC",
            $hotel_code
        ));
    }

    // ─── Categories ─────────────────────────────────────────────────────────
    public static function get_all_categories() {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_categories';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY id ASC");
    }

    public static function get_active_categories() {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_categories';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE is_active = %d ORDER BY id ASC", 1));
    }

    public static function get_category($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_categories';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function insert_category($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_categories';
        $user_id = get_current_user_id();
        $row = array(
            'title'             => sanitize_text_field(wp_unslash(isset($data['title']) ? $data['title'] : '')),
            'subtitle'          => sanitize_text_field(wp_unslash(isset($data['subtitle']) ? $data['subtitle'] : '')),
            'description'       => sanitize_textarea_field(wp_unslash(isset($data['description']) ? $data['description'] : '')),
            'image_url'         => esc_url_raw(isset($data['image_url']) ? $data['image_url'] : ''),
            'icon_url'          => esc_url_raw(isset($data['icon_url']) ? $data['icon_url'] : ''),
            'view_package_url'  => esc_url_raw(isset($data['view_package_url']) ? $data['view_package_url'] : ''),
            'is_active'         => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'created_by'        => $user_id ? $user_id : null,
            'updated_by'        => $user_id ? $user_id : null,
        );
        $result = $wpdb->insert($table, $row, array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d'));
        return $result !== false ? $wpdb->insert_id : false;
    }

    public static function update_category($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_categories';
        $user_id = get_current_user_id();
        $row = array(
            'title'             => sanitize_text_field(wp_unslash(isset($data['title']) ? $data['title'] : '')),
            'subtitle'          => sanitize_text_field(wp_unslash(isset($data['subtitle']) ? $data['subtitle'] : '')),
            'description'       => sanitize_textarea_field(wp_unslash(isset($data['description']) ? $data['description'] : '')),
            'image_url'         => esc_url_raw(isset($data['image_url']) ? $data['image_url'] : ''),
            'icon_url'          => esc_url_raw(isset($data['icon_url']) ? $data['icon_url'] : ''),
            'view_package_url'  => esc_url_raw(isset($data['view_package_url']) ? $data['view_package_url'] : ''),
            'is_active'         => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'updated_by'        => $user_id ? $user_id : null,
        );
        return $wpdb->update($table, $row, array('id' => (int) $id), array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d'), array('%d')) !== false;
    }

    public static function delete_category($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_categories';
        return $wpdb->delete($table, array('id' => (int) $id), array('%d')) !== false;
    }

    // ─── Packages ─────────────────────────────────────────────────────────
    public static function get_all_packages() {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_packages';
        $cat_table = $wpdb->prefix . 'dhr_categories';
        return $wpdb->get_results(
            "SELECT p.*, c.title AS category_title, c.icon_url AS category_icon_url, c.icon_svg AS category_icon_svg, c.icon_type AS category_icon_type FROM $table p " .
            "LEFT JOIN $cat_table c ON c.id = p.category_id ORDER BY p.created_at DESC"
        );
    }

    public static function get_package($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_packages';
        $cat_table = $wpdb->prefix . 'dhr_categories';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT p.*, c.title AS category_title, c.icon_url AS category_icon_url, c.icon_svg AS category_icon_svg, c.icon_type AS category_icon_type FROM $table p " .
            "LEFT JOIN $cat_table c ON c.id = p.category_id WHERE p.id = %d",
            $id
        ));
    }

    /** Packages that are active and within valid date range (for frontend / mapping) */
    public static function get_available_packages() {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_packages';
        $cat_table = $wpdb->prefix . 'dhr_categories';
        $now = current_time('mysql');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, c.title AS category_title, c.icon_url AS category_icon_url, c.icon_svg AS category_icon_svg, c.icon_type AS category_icon_type FROM $table p " .
            "LEFT JOIN $cat_table c ON c.id = p.category_id AND c.is_active = 1 " .
            "WHERE p.is_active = 1 AND p.valid_from <= %s AND p.valid_to >= %s ORDER BY p.valid_from DESC",
            $now,
            $now
        ));
    }

    /**
     * Packages that are active, within valid date range, and in the given category IDs.
     *
     * @param int[] $category_ids Array of category IDs (e.g. [1, 2, 3]). Empty = all (same as get_available_packages).
     * @return object[]
     */
    public static function get_available_packages_by_category_ids($category_ids = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_packages';
        $cat_table = $wpdb->prefix . 'dhr_categories';
        $now = current_time('mysql');
        $category_ids = array_filter(array_map('intval', (array) $category_ids));
        if (empty($category_ids)) {
            return self::get_available_packages();
        }
        $placeholders = implode(',', array_fill(0, count($category_ids), '%d'));
        $query = $wpdb->prepare(
            "SELECT p.*, c.title AS category_title, c.icon_url AS category_icon_url, c.icon_svg AS category_icon_svg, c.icon_type AS category_icon_type FROM $table p " .
            "LEFT JOIN $cat_table c ON c.id = p.category_id AND c.is_active = 1 " .
            "WHERE p.is_active = 1 AND p.valid_from <= %s AND p.valid_to >= %s AND p.category_id IN ($placeholders) ORDER BY p.valid_from DESC",
            array_merge(array($now, $now), $category_ids)
        );
        return $wpdb->get_results($query);
    }

    public static function insert_package($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_packages';
        $user_id = get_current_user_id();
        $row = array(
            'package_code' => sanitize_text_field($data['package_code']),
            'hotel_code'   => sanitize_text_field($data['hotel_code']),
            'category_id'  => (int) $data['category_id'],
            'valid_from'   => sanitize_text_field($data['valid_from']),
            'valid_to'     => sanitize_text_field($data['valid_to']),
            'is_active'    => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'created_by'   => $user_id ? $user_id : null,
            'updated_by'   => $user_id ? $user_id : null,
        );
        $result = $wpdb->insert($table, $row, array('%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d'));
        return $result !== false ? $wpdb->insert_id : false;
    }

    public static function update_package($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_packages';
        $user_id = get_current_user_id();
        $row = array(
            'package_code' => sanitize_text_field($data['package_code']),
            'hotel_code'   => sanitize_text_field($data['hotel_code']),
            'category_id'  => (int) $data['category_id'],
            'valid_from'   => sanitize_text_field($data['valid_from']),
            'valid_to'     => sanitize_text_field($data['valid_to']),
            'is_active'    => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'updated_by'   => $user_id ? $user_id : null,
        );
        return $wpdb->update($table, $row, array('id' => (int) $id), array('%s', '%s', '%d', '%s', '%s', '%d', '%d'), array('%d')) !== false;
    }

    public static function delete_package($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_packages';
        $details_table = $wpdb->prefix . 'dhr_package_details';
        $wpdb->delete($details_table, array('package_id' => (int) $id), array('%d'));
        return $wpdb->delete($table, array('id' => (int) $id), array('%d')) !== false;
    }

    /**
     * Save or update SHR package API response data for a package.
     *
     * @param int   $package_id   Package id from dhr_packages.
     * @param array $api_result   Result from DHR_Hotel_API::fetch_shr_package_details (productDetails, raw_response).
     * @param string $hotel_code   Hotel code (from form / package).
     * @return int|false Insert id or updated package_id on success, false on failure.
     */
    public static function save_package_details($package_id, $api_result, $hotel_code = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_package_details';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
        if (!$table_exists) {
            self::create_tables();
        }

        $product = isset($api_result['productDetails']) && is_array($api_result['productDetails']) ? $api_result['productDetails'] : array();
        $raw_response = isset($api_result['raw_response']) && is_array($api_result['raw_response']) ? $api_result['raw_response'] : array();

        $name = isset($product['name']) ? sanitize_text_field($product['name']) : null;
        $description = isset($product['description']) ? wp_kses_post($product['description']) : null;
        $images = isset($product['images']) && is_array($product['images']) ? wp_json_encode($product['images']) : null;
        $policies = isset($product['policies']) && is_array($product['policies']) ? wp_json_encode($product['policies']) : null;
        $rate_code_id = isset($product['rateCodeId']) ? intval($product['rateCodeId']) : null;
        $shr_product_id = isset($product['id']) ? intval($product['id']) : null;

        $begin_date = null;
        $end_date = null;
        if (!empty($product['beginDate'])) {
            $begin_date = str_replace('T', ' ', substr($product['beginDate'], 0, 19));
            if (substr_count($begin_date, ':') === 1) $begin_date .= ':00';
        }
        if (!empty($product['endDate'])) {
            $end_date = str_replace('T', ' ', substr($product['endDate'], 0, 19));
            if (substr_count($end_date, ':') === 1) $end_date .= ':00';
        }

        $package_code = isset($product['code']) ? sanitize_text_field($product['code']) : '';
        if ($hotel_code === '' && !empty($raw_response['requestInfo']['hotelCode'])) {
            $hotel_code = sanitize_text_field($raw_response['requestInfo']['hotelCode']);
        }

        $row = array(
            'package_id'      => (int) $package_id,
            'hotel_code'       => $hotel_code,
            'package_code'     => $package_code,
            'shr_product_id'   => $shr_product_id,
            'name'             => $name,
            'description'      => $description,
            'images'           => $images,
            'policies'         => $policies,
            'rate_code_id'     => $rate_code_id,
            'begin_date'       => $begin_date,
            'end_date'         => $end_date,
            'raw_response'     => wp_json_encode($raw_response),
            'last_synced_at'   => current_time('mysql'),
        );

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE package_id = %d",
            (int) $package_id
        ));

        if ($existing) {
            $result = $wpdb->update(
                $table,
                $row,
                array('package_id' => (int) $package_id),
                array('%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s'),
                array('%d')
            );
            return $result !== false ? (int) $package_id : false;
        }

        $result = $wpdb->insert($table, $row, array('%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s'));
        return $result !== false ? $wpdb->insert_id : false;
    }

    /**
     * Get package details (SHR API response data) by package id.
     *
     * @param int $package_id
     * @return object|null
     */
    public static function get_package_details($package_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'dhr_package_details';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE package_id = %d",
            (int) $package_id
        ));
        if ($row && $row->raw_response) {
            $row->raw_response = json_decode($row->raw_response, true);
        }
        if ($row && $row->images) {
            $row->images = json_decode($row->images, true);
        }
        if ($row && $row->policies) {
            $row->policies = json_decode($row->policies, true);
        }
        return $row;
    }

    /**
     * Get all saved "Where To Find Us" property map settings.
     *
     * Stored as option:
     * [ property_id => [field => value, ...], ... ]
     *
     * @return array
     */
    public static function get_where_to_find_us_property_maps() {
        $maps = get_option('dhr_where_to_find_us_property_maps', array());
        return is_array($maps) ? $maps : array();
    }

    /**
     * Get one "Where To Find Us" property map config by property ID.
     *
     * @param int $property_id
     * @return array
     */
    public static function get_where_to_find_us_property_map($property_id) {
        $property_id = (int) $property_id;
        if ($property_id <= 0) {
            return array();
        }
        $maps = self::get_where_to_find_us_property_maps();
        $row = isset($maps[$property_id]) && is_array($maps[$property_id]) ? $maps[$property_id] : array();
        return $row;
    }

    /**
     * Save one "Where To Find Us" property map config.
     *
     * @param int   $property_id
     * @param array $data
     * @return bool
     */
    public static function save_where_to_find_us_property_map($property_id, $data) {
        $property_id = (int) $property_id;
        if ($property_id <= 0) {
            return false;
        }

        $defaults = array(
            'property_image'      => '',
            'property_logo_image' => '',
            'latitude'            => '',
            'longitude'           => '',
            'main_heading'        => 'Where To Find Us',
            'address_text'        => '',
            'phone_label'         => '',
            'phone_number'        => '',
            'email_address'       => '',
            'enquire_text'        => 'Enquire now',
        );
        $row = wp_parse_args((array) $data, $defaults);
        $row = array(
            'property_image'      => esc_url_raw($row['property_image']),
            'property_logo_image' => esc_url_raw($row['property_logo_image']),
            'latitude'            => sanitize_text_field(wp_unslash((string) $row['latitude'])),
            'longitude'           => sanitize_text_field(wp_unslash((string) $row['longitude'])),
            'main_heading'        => sanitize_text_field(wp_unslash((string) $row['main_heading'])),
            'address_text'        => sanitize_textarea_field(wp_unslash((string) $row['address_text'])),
            'phone_label'         => sanitize_text_field(wp_unslash((string) $row['phone_label'])),
            'phone_number'        => sanitize_text_field(wp_unslash((string) $row['phone_number'])),
            'email_address'       => sanitize_email(wp_unslash((string) $row['email_address'])),
            'enquire_text'        => sanitize_text_field(wp_unslash((string) $row['enquire_text'])),
        );

        $maps = self::get_where_to_find_us_property_maps();
        $maps[$property_id] = $row;

        return (bool) update_option('dhr_where_to_find_us_property_maps', $maps, false);
    }

    /**
     * Delete one "Where To Find Us" property map config.
     *
     * @param int $property_id
     * @return bool
     */
    public static function delete_where_to_find_us_property_map($property_id) {
        $property_id = (int) $property_id;
        if ($property_id <= 0) {
            return false;
        }

        $maps = self::get_where_to_find_us_property_maps();
        if (!isset($maps[$property_id])) {
            return true;
        }

        unset($maps[$property_id]);
        return (bool) update_option('dhr_where_to_find_us_property_maps', $maps, false);
    }
}

