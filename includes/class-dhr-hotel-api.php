<?php
/**
 * API handler for DHR Hotel Management - SOAP API integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class DHR_Hotel_API {
    
    /**
     * Get API URL from settings
     */
    private function get_api_url() {
        $url = get_option('dhr_hotel_api_url', '');
        if (empty($url)) {
            // Default URL if not set
            $url = 'https://ota.windsurfercrs.com/HotelDescriptiveInfo';
        }
        return rtrim($url, '/') . '/';
    }
    
    /**
     * Get API username from settings
     */
    private function get_username() {
        $username = get_option('dhr_hotel_api_username', '');
        if (empty($username)) {
            // Default username if not set
            $username = '4SHAWDREAM1225';
        }
        return $username;
    }
    
    /**
     * Get API password from settings (decrypted)
     */
    private function get_password() {
        $encrypted = get_option('dhr_hotel_api_password', '');
        if (empty($encrypted)) {
            // Fallback to default if not set (for backward compatibility)
            // Note: This should be set in settings for production
            return 'aYvtZl$T#y#L';
        }
        // Decode base64 encoded password
        $password = base64_decode($encrypted);
        return $password !== false ? $password : '';
    }
    
    /**
     * Get hotel descriptive info from SOAP API
     */
    public function get_hotel_descriptive_info($hotel_code) {
        $soap_request = $this->build_soap_request($hotel_code);
        
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => ''
            ),
            'body' => $soap_request,
            'timeout' => 30
        );
        
        $response = wp_remote_request($this->get_api_url(), $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            return array(
                'success' => false,
                'error' => 'API returned status code: ' . $status_code
            );
        }
        
        return array(
            'success' => true,
            'xml' => $body
        );
    }
    
    /**
     * Build SOAP request XML
     */
    private function build_soap_request($hotel_code) {
        $echo_token = md5(uniqid(rand(), true));
        
        $xml = '<?xml version="1.0"?>';
        $xml .= '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">';
        $xml .= '<soap:Header>';
        $xml .= '<HTNGHeader xmlns="http://htng.org/2009">';
        $xml .= '<From>';
        $xml .= '<Credential>';
        $xml .= '<userName>' . esc_html($this->get_username()) . '</userName>';
        $xml .= '<password>' . esc_html($this->get_password()) . '</password>';
        $xml .= '</Credential>';
        $xml .= '</From>';
        $xml .= '</HTNGHeader>';
        $xml .= '</soap:Header>';
        $xml .= '<soap:Body>';
        $xml .= '<OTA_HotelDescriptiveInfoRQ EchoToken="' . esc_attr($echo_token) . '" Target="Production" Version="1.002" xmlns="http://www.opentravel.org/OTA/2003/05" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.opentravel.org/OTA/2003/05/OTA_HotelDescriptiveInfoRQ.xsd">';
        $xml .= '<HotelDescriptiveInfos>';
        $xml .= '<HotelDescriptiveInfo HotelCode="' . esc_attr($hotel_code) . '"/>';
        $xml .= '</HotelDescriptiveInfos>';
        $xml .= '</OTA_HotelDescriptiveInfoRQ>';
        $xml .= '</soap:Body>';
        $xml .= '</soap:Envelope>';
        
        return $xml;
    }
    
    /**
     * Parse SOAP XML response and extract hotel data
     */
    public function parse_hotel_xml($xml_string) {
        // Suppress XML errors
        libxml_use_internal_errors(true);
        
        $xml = simplexml_load_string($xml_string);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            return array(
                'success' => false,
                'error' => 'Failed to parse XML: ' . print_r($errors, true)
            );
        }
        
        // Register namespaces
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ota', 'http://www.opentravel.org/OTA/2003/05');
        
        // Get the body content
        $body = $xml->xpath('//soap:Body/ota:OTA_HotelDescriptiveInfoRS');
        
        if (empty($body)) {
            return array(
                'success' => false,
                'error' => 'No hotel data found in response'
            );
        }
        
        $hotel_data = $body[0];
        $hotel_data->registerXPathNamespace('ota', 'http://www.opentravel.org/OTA/2003/05');
        
        // Extract hotel information
        $hotel_contents = $hotel_data->xpath('.//ota:HotelDescriptiveContent');
        
        if (empty($hotel_contents)) {
            return array(
                'success' => false,
                'error' => 'No hotel descriptive content found'
            );
        }
        
        $content = $hotel_contents[0];
        $content->registerXPathNamespace('ota', 'http://www.opentravel.org/OTA/2003/05');
        
        // Get hotel code and name from parent
        $hotel_code = (string)$hotel_data->HotelDescriptiveContents['HotelCode'];
        $hotel_name = (string)$hotel_data->HotelDescriptiveContents['HotelName'];
        $chain_code = (string)$hotel_data->HotelDescriptiveContents['ChainCode'];
        
        // Extract hotel info
        $hotel_info = $content->xpath('.//ota:HotelInfo');
        $hotel_info_data = !empty($hotel_info) ? $hotel_info[0] : null;
        
        // Extract position
        $position = $content->xpath('.//ota:Position');
        $latitude = !empty($position) ? (string)$position[0]['Latitude'] : null;
        $longitude = !empty($position) ? (string)$position[0]['Longitude'] : null;
        
        // Extract descriptions
        $descriptions = $content->xpath('.//ota:Descriptions');
        $description_text = '';
        $renovation_text = '';
        
        if (!empty($descriptions)) {
            $desc_elements = $descriptions[0]->xpath('.//ota:DescriptiveText');
            foreach ($desc_elements as $desc) {
                $text = (string)$desc;
                if (strpos($text, 'Renovation') !== false) {
                    $renovation_text = $text;
                } else {
                    $description_text = $text;
                }
            }
        }
        
        // Extract policies
        $policies = $content->xpath('.//ota:Policies/ota:Policy');
        $check_in_time = '';
        $check_out_time = '';
        $cancellation_policy = '';
        $guarantee_policy = '';
        $pets_allowed = '';
        $commission_percent = null;
        
        if (!empty($policies)) {
            $policy = $policies[0];
            $policy->registerXPathNamespace('ota', 'http://www.opentravel.org/OTA/2003/05');
            
            // Check-in/out times
            $policy_info = $policy->xpath('.//ota:PolicyInfo');
            if (!empty($policy_info)) {
                $check_in_time = (string)$policy_info[0]['CheckInTime'];
                $check_out_time = (string)$policy_info[0]['CheckOutTime'];
            }
            
            // Cancellation policy
            $cancel_policy = $policy->xpath('.//ota:CancelPolicy/ota:CancelPenalty/ota:PenaltyDescription/ota:Text');
            if (!empty($cancel_policy)) {
                $cancellation_policy = (string)$cancel_policy[0];
            }
            
            // Guarantee policy
            $guarantee = $policy->xpath('.//ota:GuaranteePayment/ota:Description/ota:Text');
            if (!empty($guarantee)) {
                $guarantee_policy = (string)$guarantee[0];
            }
            
            // Pets policy
            $pets = $policy->xpath('.//ota:PetsPolicies');
            if (!empty($pets)) {
                $pets_allowed = (string)$pets[0]['PetsAllowedCode'];
            }
            
            // Commission
            $commission = $policy->xpath('.//ota:CommissionPolicy');
            if (!empty($commission)) {
                $commission_percent = (string)$commission[0]['Percent'];
            }
        }
        
        // Extract rooms
        $rooms = array();
        $guest_rooms = $content->xpath('.//ota:GuestRooms/ota:GuestRoom');
        
        foreach ($guest_rooms as $guest_room) {
            $guest_room->registerXPathNamespace('ota', 'http://www.opentravel.org/OTA/2003/05');
            
            $room_type = $guest_room->xpath('.//ota:TypeRoom');
            $room_type_code = !empty($room_type) ? (string)$room_type[0]['RoomTypeCode'] : '';
            $room_type_name = !empty($room_type) ? (string)$room_type[0]['Name'] : (string)$guest_room['RoomTypeName'];
            
            // Extract amenities
            $amenities = array();
            $amenity_elements = $guest_room->xpath('.//ota:Amenity');
            foreach ($amenity_elements as $amenity) {
                $amenities[] = array(
                    'code' => (string)$amenity['RoomAmenityCode'],
                    'name' => (string)$amenity['CodeDetail'],
                    'exists_code' => (string)$amenity['ExistsCode'],
                    'description' => (string)$amenity->DescriptiveText
                );
            }
            
            // Extract images
            $images = array();
            $image_items = $guest_room->xpath('.//ota:ImageItem/ota:ImageFormat/ota:URL');
            foreach ($image_items as $image_url) {
                $images[] = (string)$image_url;
            }
            
            // Extract description
            $room_desc = $guest_room->xpath('.//ota:DescriptiveText');
            $room_description = !empty($room_desc) ? html_entity_decode((string)$room_desc[0]) : '';
            
            $rooms[] = array(
                'room_type_name' => $room_type_name,
                'room_type_code' => $room_type_code,
                'max_occupancy' => (int)$guest_room['MaxOccupancy'],
                'max_adult_occupancy' => (int)$guest_room['MaxAdultOccupancy'],
                'max_child_occupancy' => (int)$guest_room['MaxChildOccupancy'],
                'standard_num_beds' => !empty($room_type) ? (int)$room_type[0]['StandardNumBeds'] : null,
                'standard_occupancy' => !empty($room_type) ? (int)$room_type[0]['StandardOccupancy'] : null,
                'room_size' => !empty($room_type) ? (float)$room_type[0]['Size'] : null,
                'description' => $room_description,
                'amenities' => $amenities,
                'images' => $images
            );
        }
        
        // Extract services
        $services = array();
        $service_elements = $content->xpath('.//ota:Services/ota:Service');
        
        foreach ($service_elements as $service) {
            $services[] = array(
                'service_code' => (string)$service['Code'],
                'service_name' => (string)$service['CodeDetail'],
                'exists_code' => (string)$service['ExistsCode'],
                'proximity_code' => (string)$service['ProximityCode'],
                'description' => (string)$service->DescriptiveText
            );
        }
        
        return array(
            'success' => true,
            'hotel_code' => $hotel_code,
            'hotel_name' => $hotel_name,
            'chain_code' => $chain_code,
            'chain_name' => (string)$content['ChainName'],
            'currency_code' => (string)$content['CurrencyCode'],
            'language_code' => (string)$content['LanguageCode'],
            'time_zone' => (string)$content['TimeZone'],
            'when_built' => $hotel_info_data ? (string)$hotel_info_data['WhenBuilt'] : '',
            'hotel_status' => $hotel_info_data ? (string)$hotel_info_data['HotelStatus'] : '',
            'hotel_status_code' => $hotel_info_data ? (string)$hotel_info_data['HotelStatusCode'] : '',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'description' => $description_text,
            'renovation_text' => $renovation_text,
            'check_in_time' => $check_in_time,
            'check_out_time' => $check_out_time,
            'cancellation_policy' => $cancellation_policy,
            'guarantee_policy' => $guarantee_policy,
            'pets_allowed' => $pets_allowed,
            'commission_percent' => $commission_percent,
            'rooms' => $rooms,
            'services' => $services,
            'raw_xml' => $xml_string
        );
    }
    
    /**
     * Fetch and save hotel data from API
     */
    public function fetch_and_save_hotel_data($hotel_code) {
        // Get data from API
        $api_response = $this->get_hotel_descriptive_info($hotel_code);
        
        if (!$api_response['success']) {
            return array(
                'success' => false,
                'error' => $api_response['error']
            );
        }
        
        // Parse XML
        $parsed_data = $this->parse_hotel_xml($api_response['xml']);
        
        if (!$parsed_data['success']) {
            return $parsed_data;
        }
        
        // Save hotel details
        $hotel_details_data = array(
            'hotel_code' => $parsed_data['hotel_code'],
            'hotel_name' => $parsed_data['hotel_name'],
            'chain_code' => $parsed_data['chain_code'],
            'chain_name' => $parsed_data['chain_name'],
            'currency_code' => $parsed_data['currency_code'],
            'language_code' => $parsed_data['language_code'],
            'time_zone' => $parsed_data['time_zone'],
            'when_built' => $parsed_data['when_built'],
            'hotel_status' => $parsed_data['hotel_status'],
            'hotel_status_code' => $parsed_data['hotel_status_code'],
            'latitude' => $parsed_data['latitude'],
            'longitude' => $parsed_data['longitude'],
            'description' => $parsed_data['description'],
            'renovation_text' => $parsed_data['renovation_text'],
            'check_in_time' => $parsed_data['check_in_time'],
            'check_out_time' => $parsed_data['check_out_time'],
            'cancellation_policy' => $parsed_data['cancellation_policy'],
            'guarantee_policy' => $parsed_data['guarantee_policy'],
            'pets_allowed' => $parsed_data['pets_allowed'],
            'commission_percent' => $parsed_data['commission_percent'],
            'raw_xml_data' => $parsed_data['raw_xml']
        );
        
        $hotel_details_id = DHR_Hotel_Database::save_hotel_details($hotel_details_data);
        
        if ($hotel_details_id === false) {
            global $wpdb;
            $error_message = 'Failed to save hotel details';
            
            // Get detailed error if available
            if (!empty($wpdb->last_error)) {
                $error_message .= ': ' . $wpdb->last_error;
            }
            
            // Log for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('DHR Hotel API: Failed to save hotel details for ' . $parsed_data['hotel_code']);
                error_log('DHR Hotel API: Last query - ' . $wpdb->last_query);
                error_log('DHR Hotel API: Data - ' . print_r($hotel_details_data, true));
            }
            
            return array(
                'success' => false,
                'error' => $error_message
            );
        }
        
        // Save rooms
        DHR_Hotel_Database::save_hotel_rooms($parsed_data['hotel_code'], $parsed_data['rooms']);
        
        // Save services
        DHR_Hotel_Database::save_hotel_services($parsed_data['hotel_code'], $parsed_data['services']);
        
        return array(
            'success' => true,
            'hotel_code' => $parsed_data['hotel_code'],
            'hotel_details_id' => $hotel_details_id
        );
    }

    /**
     * ==============================
     * SHR WS Shop API (REST) helpers
     * ==============================
     */

    /**
     * Get SHR client ID from settings
     */
    private function get_shr_client_id() {
        return get_option('dhr_shr_client_id', '');
    }

    /**
     * Get SHR client secret from settings (decrypted)
     */
    private function get_shr_client_secret() {
        $encrypted = get_option('dhr_shr_client_secret', '');
        return !empty($encrypted) ? base64_decode($encrypted) : '';
    }

    /**
     * Get SHR scope
     */
    private function get_shr_scope() {
        $scope = get_option('dhr_shr_scope', 'wsapi.hoteldetails.read');
        return trim($scope) !== '' ? $scope : 'wsapi.hoteldetails.read';
    }

    /**
     * Get SHR token URL
     */
    private function get_shr_token_url() {
        $url = get_option('dhr_shr_token_url', 'https://id.shrglobal.com/connect/token');
        return rtrim($url, '/');
    }

    /**
     * Get SHR Shop API base URL
     */
    private function get_shr_shop_base_url() {
        $url = get_option('dhr_shr_shop_base_url', 'https://api.shrglobal.com/shop');
        return rtrim($url, '/');
    }

    /**
     * Get SHR Availability API base URL (POST availability check, different from Shop API).
     */
    private function get_shr_availability_base_url() {
        $url = get_option('dhr_shr_availability_base_url', 'https://api.shrglobal.com/availability');
        return rtrim($url, '/');
    }

    /**
     * Clear cached SHR access token (so next call will request a new one).
     * Also clears manual token so expired manual token is not reused.
     * Used when API returns 401 to force token refresh.
     */
    private function clear_shr_token_cache() {
        delete_option('dhr_shr_access_token');
        delete_option('dhr_shr_access_token_expires_at');
        delete_option('dhr_shr_manual_access_token');
    }

    /**
     * Request a new SHR access token from id.shrglobal.com/connect/token.
     * Uses client_credentials grant; no Bearer on this request.
     */
    private function request_shr_token_from_api() {
        $client_id     = $this->get_shr_client_id();
        $client_secret = $this->get_shr_client_secret();
        $scope         = $this->get_shr_scope();
        $token_url     = $this->get_shr_token_url();

        if (empty($client_id) || empty($client_secret)) {
            return array(
                'success' => false,
                'error'   => __('SHR client ID and secret are required to generate a token.', 'dhr-hotel-management'),
            );
        }

        $response = wp_remote_post(
            $token_url,
            array(
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ),
                'body'    => array(
                    'client_id'     => $client_id,
                    'client_secret' => $client_secret,
                    'grant_type'    => 'client_credentials',
                    'scope'         => $scope,
                ),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error'   => $response->get_error_message(),
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);
        $data        = json_decode($body, true);

        if ($status_code !== 200 || !is_array($data) || empty($data['access_token'])) {
            return array(
                'success' => false,
                'error'   => sprintf(
                    /* translators: 1: HTTP status code */
                    __('SHR token request failed (status %d).', 'dhr-hotel-management'),
                    $status_code
                ),
                'details' => $body,
            );
        }

        $access_token = $data['access_token'];
        $expires_in   = isset($data['expires_in']) ? intval($data['expires_in']) : 3600;

        update_option('dhr_shr_access_token', $access_token);
        update_option('dhr_shr_access_token_expires_at', time() + $expires_in);

        return array(
            'success'      => true,
            'access_token' => $access_token,
        );
    }

    /**
     * Request and cache a token with scope wsapi.shop.availability (for availability API only).
     * Uses same client id/secret; separate cache so main token is unchanged.
     */
    private function request_shr_availability_token() {
        $client_id     = $this->get_shr_client_id();
        $client_secret = $this->get_shr_client_secret();
        $token_url     = $this->get_shr_token_url();
        $scope         = 'wsapi.shop.availability';

        if (empty($client_id) || empty($client_secret)) {
            return array(
                'success' => false,
                'error'   => __('SHR client ID and secret are required for availability.', 'dhr-hotel-management'),
            );
        }

        $response = wp_remote_post(
            $token_url,
            array(
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ),
                'body'    => array(
                    'client_id'     => $client_id,
                    'client_secret' => $client_secret,
                    'grant_type'    => 'client_credentials',
                    'scope'         => $scope,
                ),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error'   => $response->get_error_message(),
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);
        $data        = json_decode($body, true);

        if ($status_code !== 200 || !is_array($data) || empty($data['access_token'])) {
            return array(
                'success' => false,
                'error'   => sprintf(
                    __('SHR availability token failed (status %d). Ensure scope "wsapi.shop.availability" is allowed for your client.', 'dhr-hotel-management'),
                    $status_code
                ),
                'details' => $body,
            );
        }

        $access_token = $data['access_token'];
        $expires_in   = isset($data['expires_in']) ? intval($data['expires_in']) : 3600;

        update_option('dhr_shr_availability_access_token', $access_token);
        update_option('dhr_shr_availability_access_token_expires_at', time() + $expires_in);

        return array(
            'success'      => true,
            'access_token' => $access_token,
        );
    }

    /**
     * Get SHR token for availability API (scope wsapi.shop.availability).
     * Cached separately from main token; 60s buffer before expiry.
     *
     * @return array { success, access_token?, error? }
     */
    public function get_shr_availability_token() {
        $cached = get_option('dhr_shr_availability_access_token', '');
        $expires_at = intval(get_option('dhr_shr_availability_access_token_expires_at', 0));
        if (!empty($cached) && $expires_at > (time() + 60)) {
            return array('success' => true, 'access_token' => $cached);
        }
        return $this->request_shr_availability_token();
    }

    /**
     * Get SHR access token: use manual token if set, else cached token if still valid, else request new one.
     * Does not generate a token on every request; reuses cache until expiry (with 60s buffer).
     *
     * @param bool $force_refresh If true, ignore cache and request a new token (used after 401).
     * @param bool $force_from_api If true, skip manual and cache and get a fresh token from token API (for 401 retry).
     * @return array { success, access_token?, manual?, error? }
     */
    public function get_shr_access_token($force_refresh = false, $force_from_api = false) {
        // After 401 we want a fresh token from API only (ignore manual and cache)
        if ($force_from_api) {
            $result = $this->request_shr_token_from_api();
            if (!$result['success']) {
                return array(
                    'success' => false,
                    'error'   => isset($result['error']) ? $result['error'] : __('Failed to get SHR token.', 'dhr-hotel-management'),
                );
            }
            return array(
                'success'      => true,
                'access_token' => $result['access_token'],
                'from_cache'   => false,
                'manual'       => false,
            );
        }

        // Manual token is always used as-is (no expiry, no refresh)
        $manual_token = get_option('dhr_shr_manual_access_token', '');
        if (!empty($manual_token)) {
            return array(
                'success'      => true,
                'access_token' => trim($manual_token),
                'from_cache'   => false,
                'manual'       => true,
            );
        }

        // When 401 occurred, cache was cleared; force_refresh means "get new token"
        if (!$force_refresh) {
            $cached_token = get_option('dhr_shr_access_token', '');
            $expires_at   = intval(get_option('dhr_shr_access_token_expires_at', 0));
            if (!empty($cached_token) && $expires_at > (time() + 60)) {
                return array(
                    'success'      => true,
                    'access_token' => trim($cached_token),
                    'from_cache'   => true,
                    'manual'       => false,
                );
            }
        }

        $result = $this->request_shr_token_from_api();
        if (!$result['success']) {
            return array(
                'success' => false,
                'error'   => isset($result['error']) ? $result['error'] : __('Failed to get SHR token.', 'dhr-hotel-management'),
            );
        }

        return array(
            'success'      => true,
            'access_token' => trim($result['access_token']),
            'from_cache'   => false,
            'manual'       => false,
        );
    }

    /**
     * Perform one SHR hotelDetails GET request (used for initial call and retry after 401).
     */
    private function do_shr_hotel_details_request($url, $access_token) {
        $token = trim($access_token);
        if (stripos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
        }
        $response = wp_remote_get(
            $url,
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return array(
                'status_code' => 0,
                'body'        => '',
                'data'        => null,
                'error'       => $response->get_error_message(),
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);
        $data        = json_decode($body, true);

        return array(
            'status_code' => $status_code,
            'body'        => $body,
            'data'        => is_array($data) ? $data : null,
        );
    }

    /**
     * Fetch hotel rooms from SHR API: /hotelDetails/{hotelCode}/room?channelId=...
     * Uses token with auto-regeneration on 401.
     *
     * @param string $hotel_code Hotel code (e.g. DRE013)
     * @return array { success, rooms?, hotel_name?, error? }
     */
    public function get_shr_hotel_rooms($hotel_code) {
        $hotel_code = trim($hotel_code);
        if ($hotel_code === '') {
            return array('success' => false, 'error' => __('Hotel code is required.', 'dhr-hotel-management'));
        }

        $token_result = $this->get_shr_access_token(false);
        if (!$token_result['success']) {
            return array('success' => false, 'error' => $token_result['error']);
        }

        $base_url   = $this->get_shr_shop_base_url();
        $base_url   = rtrim($base_url, '/');
        $channel_id = get_option('dhr_shr_channel_id', '1');
        $url        = $base_url . '/hotelDetails/' . rawurlencode($hotel_code) . '/room';
        $url        = add_query_arg('channelId', $channel_id, $url);
        $url       .= (strpos($url, '?') !== false ? '&' : '?') . 'requiredDetails=all&requiredDetails=images';

        $result = $this->do_shr_hotel_details_request($url, $token_result['access_token']);

        // echo "<pre>" . print_r($result, true) . "</pre>"; // Debug output
        // die(); // Stop execution after debug output
        if (!empty($result['error'])) {
            if (isset($_GET['dhr_api_debug']) && $_GET['dhr_api_debug'] === '1') {
                echo '<pre>' . print_r(array('url' => $url, 'result' => $result), true) . '</pre>';
                exit;
            }
            return array('success' => false, 'error' => $result['error']);
        }

        $status_code = $result['status_code'];
        $body        = $result['body'];
        $data        = $result['data'];

        // On 401 (token expired), clear cache/manual token and regenerate from API, then retry once
        if ($status_code === 401) {
            $client_id     = $this->get_shr_client_id();
            $client_secret = $this->get_shr_client_secret();
            if (!empty($client_id) && !empty($client_secret)) {
                $this->clear_shr_token_cache();
                $token_result = $this->get_shr_access_token(true, true);
                if ($token_result['success']) {
                    $result = $this->do_shr_hotel_details_request($url, $token_result['access_token']);
                    if (!empty($result['error'])) {
                        return array('success' => false, 'error' => $result['error']);
                    }
                    $status_code = $result['status_code'];
                    $body        = $result['body'];
                    $data        = $result['data'];
                }
            }
        }

        if (isset($_GET['dhr_api_debug']) && $_GET['dhr_api_debug'] === '1') {
            echo '<pre>' . print_r(array('url' => $url, 'status_code' => $status_code, 'body' => $body, 'data' => $data), true) . '</pre>';
            exit;
        }

        if ($status_code !== 200) {
            $err_msg = sprintf(__('SHR room API failed (status %d).', 'dhr-hotel-management'), $status_code);
            $err_data = is_array(json_decode($body, true)) ? json_decode($body, true) : array();
            if (!empty($err_data['error'])) $err_msg .= ' ' . $err_data['error'];
            elseif (!empty($err_data['message'])) $err_msg .= ' ' . $err_data['message'];
            return array('success' => false, 'error' => $err_msg);
        }

        if (!is_array($data)) {
            return array('success' => false, 'error' => __('Invalid response format from SHR room API.', 'dhr-hotel-management'));
        }

        // Handle HTTP 200 with business error in body (e.g. status: 404, "Room Type Not Found")
        if (isset($data['status']) && (int) $data['status'] >= 400) {
            $err_msg = isset($data['title']) ? $data['title'] : __('SHR room API error.', 'dhr-hotel-management');
            if (!empty($data['errors']) && is_array($data['errors'])) {
                $err_msg .= ' ' . implode(' ', array_map('trim', $data['errors']));
            }
            if (strpos($err_msg, 'Room Type Not Found') !== false) {
                $err_msg .= ' ' . sprintf(__('Try channelId=1 in DHR Hotel Settings (current: %s).', 'dhr-hotel-management'), $channel_id);
            }
            return array('success' => false, 'error' => trim($err_msg));
        }

        // Store last room API response for debugging / print (transient expires in 1 hour)
        set_transient('dhr_last_shr_room_api_response', array(
            'hotel_code' => $hotel_code,
            'body'       => $body,
            'data'       => $data,
            'at'         => current_time('mysql'),
        ), HOUR_IN_SECONDS);

        $normalised = $this->normalise_shr_room_response($hotel_code, $data);
        return array(
            'success'     => true,
            'rooms'       => $normalised['rooms'],
            'hotel_name'  => $normalised['hotel_name'],
        );
    }

    /**
     * Get SHR channel ID from settings (used for hotelDetails package/room requests).
     *
     * @return string
     */
    private function get_shr_channel_id() {
        return trim((string) get_option('dhr_shr_channel_id', '30'));
    }

    /**
     * Fetch package details from SHR Shop API: GET hotelDetails/{hotelCode}/package?requiredDetails=all&requiredDetails=images&code={code}&channelId=...
     * Used when adding a new package in admin to get beginDate/endDate and validate they are not in the past.
     *
     * @param string $hotel_code   Hotel code (e.g. DRE002)
     * @param string $package_code Package code (e.g. CAPCLSQ)
     * @return array { success, productDetails?, beginDate?, endDate?, error? }
     */
    public function fetch_shr_package_details($hotel_code, $package_code) {
        $hotel_code   = trim($hotel_code);
        $package_code = trim($package_code);
        if ($hotel_code === '' || $package_code === '') {
            return array(
                'success' => false,
                'error'   => __('Hotel code and package code are required.', 'dhr-hotel-management'),
            );
        }

        $token_result = $this->get_shr_access_token(false);
        if (!$token_result['success']) {
            return array('success' => false, 'error' => $token_result['error']);
        }

        $base_url   = $this->get_shr_shop_base_url();
        $channel_id = $this->get_shr_channel_id();
        if ($channel_id === '') {
            $channel_id = '30';
        }
        $url = $base_url . '/hotelDetails/' . rawurlencode($hotel_code) . '/package';
        $url .= '?requiredDetails=all&requiredDetails=images&code=' . rawurlencode($package_code) . '&channelId=' . rawurlencode($channel_id);

        $result = $this->do_shr_hotel_details_request($url, $token_result['access_token']);

        if (!empty($result['error'])) {
            return array('success' => false, 'error' => $result['error']);
        }

        $status_code = $result['status_code'];
        $body        = $result['body'];
        $data        = $result['data'];

        if ($status_code === 401) {
            $client_id     = $this->get_shr_client_id();
            $client_secret = $this->get_shr_client_secret();
            if (!empty($client_id) && !empty($client_secret)) {
                $this->clear_shr_token_cache();
                $token_result = $this->get_shr_access_token(true, true);
                if ($token_result['success']) {
                    $result = $this->do_shr_hotel_details_request($url, $token_result['access_token']);
                    if (!empty($result['error'])) {
                        return array('success' => false, 'error' => $result['error']);
                    }
                    $status_code = $result['status_code'];
                    $body        = $result['body'];
                    $data        = $result['data'];
                }
            }
        }

        if ($status_code !== 200) {
            $err_msg = sprintf(__('SHR package API failed (status %d).', 'dhr-hotel-management'), $status_code);
            $err_data = is_array(json_decode($body, true)) ? json_decode($body, true) : array();
            if (!empty($err_data['error'])) {
                $err_msg .= ' ' . $err_data['error'];
            } elseif (!empty($err_data['message'])) {
                $err_msg .= ' ' . $err_data['message'];
            }
            return array('success' => false, 'error' => $err_msg);
        }

        if (!is_array($data)) {
            return array('success' => false, 'error' => __('Invalid response format from SHR package API.', 'dhr-hotel-management'));
        }

        if (isset($data['status']) && (int) $data['status'] >= 400) {
            $err_msg = isset($data['title']) ? $data['title'] : __('SHR package API error.', 'dhr-hotel-management');
            if (!empty($data['errors']) && is_array($data['errors'])) {
                $err_msg .= ' ' . implode(' ', array_map('trim', $data['errors']));
            }
            return array('success' => false, 'error' => trim($err_msg));
        }

        $product_details = isset($data['productDetails']) && is_array($data['productDetails']) ? $data['productDetails'] : null;
        if (!$product_details) {
            return array('success' => false, 'error' => __('Package details not found in SHR response.', 'dhr-hotel-management'));
        }

        $begin_date = isset($product_details['beginDate']) ? trim($product_details['beginDate']) : '';
        $end_date   = isset($product_details['endDate']) ? trim($product_details['endDate']) : '';
        if ($begin_date === '' || $end_date === '') {
            return array('success' => false, 'error' => __('Package begin date or end date is missing in SHR response.', 'dhr-hotel-management'));
        }

        return array(
            'success'         => true,
            'productDetails'  => $product_details,
            'beginDate'       => $begin_date,
            'endDate'         => $end_date,
            'raw_response'    => $data,
        );
    }

    /**
     * Check if package validity dates are in the future (end date must be >= today).
     * Used to block inserting packages that have already ended.
     *
     * @param string $begin_date ISO date or datetime (e.g. 2026-01-01T00:00:00)
     * @param string $end_date   ISO date or datetime (e.g. 2026-11-30T00:00:00)
     * @return array { valid, error? } valid true only when endDate >= today (no past-only packages).
     */
    public function validate_package_dates_not_past($begin_date, $end_date) {
        $today = current_time('Y-m-d');
        $begin_ymd = substr($begin_date, 0, 10);
        $end_ymd   = substr($end_date, 0, 10);
        if ($end_ymd < $today) {
            return array(
                'valid' => false,
                'error' => __('This package has already ended. Only packages with an end date in the future can be added.', 'dhr-hotel-management'),
            );
        }
        return array('valid' => true);
    }

    /**
     * Normalise SHR room API response to format expected by hotel-rooms.php template.
     * Handles productDetailList structure: code, name, totalOccupancy, roomAmenities (amenityName).
     */
    private function normalise_shr_room_response($hotel_code, $raw_data) {
        $rooms      = array();
        $hotel_name = '';

        // SHR room API returns productDetailList (productType: roomtype)
        $room_list = array();
        if (!empty($raw_data['productDetailList']) && is_array($raw_data['productDetailList'])) {
            foreach ($raw_data['productDetailList'] as $p) {
                $p = is_array($p) ? $p : (array) $p;
                if (isset($p['productType']) && $p['productType'] === 'roomtype') {
                    $room_list[] = $p;
                }
            }
        }
        // Fallbacks for other possible structures
        if (empty($room_list) && !empty($raw_data['rooms']) && is_array($raw_data['rooms'])) {
            $room_list = $raw_data['rooms'];
        } elseif (empty($room_list) && !empty($raw_data['room']) && is_array($raw_data['room'])) {
            $room_list = $raw_data['room'];
        } elseif (empty($room_list) && !empty($raw_data['roomTypes']) && is_array($raw_data['roomTypes'])) {
            $room_list = $raw_data['roomTypes'];
        }

        foreach ($room_list as $item) {
            $item = is_array($item) ? $item : (array) $item;
            $room = new stdClass();
            $room->room_type_code    = $this->pluck($item, array('code', 'roomTypeCode', 'room_type_code'));
            $room->room_type_id      = intval($this->pluck($item, array('id', 'roomTypeID', 'roomTypeId', 'room_type_id')));
            $room->room_type_name    = $this->pluck($item, array('name', 'roomTypeName', 'room_type_name', 'roomName', 'description'));
            $room->description       = $this->pluck($item, array('shortDescription', 'longDescription', 'description'));
            $room->max_occupancy     = intval($this->pluck($item, array('totalOccupancy', 'adultOccupancy', 'maxOccupancy', 'max_occupancy', 'maxAdults')));
            $room->standard_num_beds = intval($this->pluck($item, array('standardNumBeds', 'numBeds', 'beds')));

            // Images (SHR room API with requiredDetails=images returns productMedia, media, etc.)
            $imgs = $this->pluck($item, array('images', 'media', 'photos', 'productMedia', 'mediaList', 'productDetailMedia'));
            if (is_array($imgs)) {
                $urls = array();
                foreach ($imgs as $img) {
                    $i = is_array($img) ? $img : (array) $img;
                    $u = $this->pluck($i, array('url', 'fileName', 'path', 'value', 'mediaUrl', 'fileUrl', 'imageUrl'));
                    if (is_string($u) && !empty($u)) {
                        $urls[] = $u;
                    } elseif (is_array($u) && !empty($u['url'])) {
                        $urls[] = $u['url'];
                    }
                }
                $room->images = array_values(array_filter(array_unique($urls)));
            } else {
                $room->images = array();
            }

            // Amenities from roomAmenities (objects with amenityName)
            $am = $this->pluck($item, array('roomAmenities', 'amenities', 'features'));
            if (is_array($am)) {
                $room->amenities = array();
                foreach ($am as $a) {
                    if (is_string($a)) {
                        $room->amenities[] = array('name' => $a);
                    } elseif (is_array($a)) {
                        $name = isset($a['amenityName']) ? $a['amenityName'] : (isset($a['name']) ? $a['name'] : (isset($a['description']) ? $a['description'] : reset($a)));
                        $id_or_code = '';

                        // Try common amenity id/code keys across APIs
                        foreach (array('id', 'amenityId', 'amenityID', 'amenity_id', 'code', 'amenityCode', 'amenity_code', 'roomAmenityCode', 'RoomAmenityCode') as $k) {
                            if (isset($a[$k]) && (is_scalar($a[$k]) || is_numeric($a[$k]))) {
                                $id_or_code = (string) $a[$k];
                                break;
                            }
                        }

                        if (!empty($name)) {
                            $row = array('name' => (string) $name);
                            if ($id_or_code !== '') {
                                // Keep both fields so templates can pick either.
                                $row['code'] = $id_or_code;
                                $row['id']   = $id_or_code;
                            }
                            $room->amenities[] = $row;
                        }
                    }
                }
            } else {
                $room->amenities = array();
            }

            if (empty($room->room_type_name)) {
                $room->room_type_name = __('Room', 'dhr-hotel-management') . ' ' . ($room->room_type_code ?: count($rooms) + 1);
            }
            $rooms[] = $room;
        }

        if (empty($hotel_name) && !empty($raw_data['hotelName'])) {
            $hotel_name = $raw_data['hotelName'];
        }
        if (empty($hotel_name)) {
            $hotel_name = $hotel_code;
        }

        return array('rooms' => $rooms, 'hotel_name' => $hotel_name);
    }

    /**
     * Get SHR rate calendar base URL (api.shrglobal.com, not /shop).
     */
    private function get_shr_rate_calendar_base_url() {
        $shop = $this->get_shr_shop_base_url();
        $base = preg_replace('#/shop$#i', '', $shop);
        if ($base === $shop) {
            $base = 'https://api.shrglobal.com';
        }
        return rtrim($base, '/');
    }

    /**
     * Fetch room-wise rates from SHR rateCalendar API.
     * GET {{baseUrl}}/rateCalendar/:hotelCode?channelID=30&year=...&month=...&roomTypeID=...&checkInDate=...&minDate=...&maxDate=...
     * Requires SHR scope: wsapi.shop.ratecalendar (add to DHR Hotel Settings → SHR scope if needed).
     *
     * @param string $hotel_code   Hotel code (e.g. DRE002)
     * @param int    $room_type_id Room type ID (e.g. 99883)
     * @param array  $opts         Optional: year, month, checkInDate, minDate, maxDate (defaults: current month range)
     * @return array { success, from_price?, rates?, error? }  from_price = minimum valid amount from rates
     */
    public function get_shr_rate_calendar($hotel_code, $room_type_id, $opts = array()) {
        $hotel_code   = trim($hotel_code);
        $room_type_id = (int) $room_type_id;
        if ($hotel_code === '' || $room_type_id <= 0) {
            return array('success' => false, 'error' => __('Hotel code and room type ID are required.', 'dhr-hotel-management'));
        }

        $token_result = $this->get_shr_access_token(false);
        if (!$token_result['success']) {
            return array('success' => false, 'error' => $token_result['error']);
        }

        $today = current_time('Y-m-d');
        $year  = isset($opts['year']) ? (int) $opts['year'] : (int) date('Y');
        $month = isset($opts['month']) ? (int) $opts['month'] : (int) date('n');
        $check_in = isset($opts['checkInDate']) ? $opts['checkInDate'] : $today;
        $min_date = isset($opts['minDate']) ? $opts['minDate'] : $today;
        $max_date = isset($opts['maxDate']) ? $opts['maxDate'] : date('Y-m-d', strtotime($today . ' +1 month'));

        $base_url  = $this->get_shr_rate_calendar_base_url();
        $channel_id = get_option('dhr_shr_channel_id', '30');
        $url = $base_url . '/rateCalendar/' . rawurlencode($hotel_code);
        $url = add_query_arg(array(
            'channelID'   => $channel_id,
            'year'        => $year,
            'month'       => $month,
            'roomTypeID'  => $room_type_id,
            'checkInDate' => $check_in,
            'minDate'     => $min_date,
            'maxDate'     => $max_date,
        ), $url);

        $result = $this->do_shr_hotel_details_request($url, $token_result['access_token']);

        if (!empty($result['error'])) {
            return array('success' => false, 'error' => $result['error']);
        }
        if ($result['status_code'] !== 200) {
            return array(
                'success' => false,
                'error'   => sprintf(__('Rate calendar API returned status %d.', 'dhr-hotel-management'), $result['status_code']),
            );
        }

        $data = $result['data'];
        if (!is_array($data) || !isset($data['rates']) || !is_array($data['rates'])) {
            return array('success' => true, 'from_price' => 0, 'rates' => array());
        }

        $rates = $data['rates'];
        $from_price = 0;
        foreach ($rates as $r) {
            $amount = isset($r['amount']) ? (int) $r['amount'] : -999;
            if ($amount > 0) {
                if ($from_price === 0 || $amount < $from_price) {
                    $from_price = $amount;
                }
            }
        }

        return array(
            'success'    => true,
            'from_price' => $from_price,
            'rates'      => $rates,
        );
    }

    /**
     * Check SHR availability and return Windsurfer booking URL if rooms available.
     * Uses scope wsapi.shop.availability (separate token). Deep link: res.windsurfercrs.com/ibe/details.aspx
     *
     * @param string   $hotel_code Hotel code e.g. DRE002
     * @param int      $channel_id Channel ID e.g. 30
     * @param string   $check_in   Y-m-d
     * @param string   $check_out  Y-m-d
     * @param int      $rooms      Number of rooms
     * @param int      $adults     Number of adults
     * @param int|null $child_age  Single child age or null
     * @return array { success, url? } or { success: false, errors: string[] }
     */
    public function get_shr_availability_booking_url($hotel_code, $channel_id, $check_in, $check_out, $rooms, $adults, $child_age = null) {
        $hotel_code = trim($hotel_code);
        $channel_id = (int) $channel_id;
        $rooms      = max(1, (int) $rooms);
        $adults     = max(1, (int) $adults);
        if ($hotel_code === '') {
            return array('success' => false, 'errors' => array(__('Hotel code is required.', 'dhr-hotel-management')));
        }

        $token_result = $this->get_shr_availability_token();
        if (!$token_result['success']) {
            return array(
                'success' => false,
                'errors'  => array($token_result['error'] ?: __('Unable to connect to the booking service.', 'dhr-hotel-management')),
            );
        }

        $base_url = $this->get_shr_availability_base_url();
        $url      = $base_url . '/' . rawurlencode($hotel_code);
        $payload  = array(
            'adults'    => $adults,
            'Child'     => $child_age !== null ? array((int) $child_age) : array(),
            'channelID' => $channel_id,
            'checkIn'   => $check_in,
            'checkOut'  => $check_out,
            'rooms'     => $rooms,
        );

        $response = wp_remote_post(
            $url,
            array(
                'headers' => array(
                    'Content-Type'              => 'application/json',
                    'Accept'                   => 'application/json',
                    'Authorization'            => 'Bearer ' . $token_result['access_token'],
                    'X-LoyaltyAPI-AccessToken' => 'string',
                    'X-BookingEngine-ClientIP'  => 'string',
                    'X-BookingEngine-Domain'   => 'string',
                ),
                'body'    => wp_json_encode($payload),
                'timeout' => 30,
            )
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'errors'  => array(__('We could not reach the booking service. Please check your connection and try again.', 'dhr-hotel-management')),
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);
        $data        = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return array(
                'success' => false,
                'errors'  => array(__('Something went wrong on our end. Please try again shortly.', 'dhr-hotel-management')),
            );
        }

        if (isset($data['status']) && (int) $data['status'] === -1) {
            $errors = isset($data['errors']) && is_array($data['errors']) ? $data['errors'] : array(__('We were unable to process your request. Please try different dates or contact us.', 'dhr-hotel-management'));
            return array('success' => false, 'errors' => $errors);
        }

        $avail = isset($data['availResponse']) && is_array($data['availResponse']) ? $data['availResponse'] : array();
        $available_rooms = isset($avail['rooms']) ? (int) $avail['rooms'] : 0;
        if ($available_rooms <= 1) {
            return array(
                'success' => false,
                'errors'  => array(__('Sorry, there are no rooms available for your selected dates. Please try different dates or adjust your guest count.', 'dhr-hotel-management')),
            );
        }

        if (empty($avail['roomStayInfos']) || !is_array($avail['roomStayInfos']) || empty($avail['roomTypes']) || !is_array($avail['roomTypes'])) {
            return array(
                'success' => false,
                'errors'  => array(__('No rooms could be found matching your request. Please try adjusting your search criteria.', 'dhr-hotel-management')),
            );
        }

        $room_stays = $avail['roomStayInfos'];
        usort($room_stays, function ($a, $b) {
            $ra = isset($a['sortRank']) ? (int) $a['sortRank'] : 999;
            $rb = isset($b['sortRank']) ? (int) $b['sortRank'] : 999;
            return $ra <=> $rb;
        });
        $best = $room_stays[0];
        $room_type_id = isset($best['roomTypeID']) ? $best['roomTypeID'] : null;
        $rate_code_id = isset($best['rateCodeID']) ? $best['rateCodeID'] : null;

        $matched_room = null;
        foreach ($avail['roomTypes'] as $rt) {
            if (isset($rt['id']) && $rt['id'] === $room_type_id) {
                $matched_room = $rt;
                break;
            }
        }
        $matched_rate = null;
        $rate_codes   = isset($avail['rateCodes']) && is_array($avail['rateCodes']) ? $avail['rateCodes'] : array();
        foreach ($rate_codes as $rc) {
            if (isset($rc['id']) && $rc['id'] === $rate_code_id) {
                $matched_rate = $rc;
                break;
            }
        }

        $request_info = isset($data['requestInfo']) && is_array($data['requestInfo']) ? $data['requestInfo'] : array();
        $hotel_id     = isset($request_info['hotelID']) ? $request_info['hotelID'] : null;

        $fmt_date = function ($d) {
            $t = strtotime($d);
            return $t ? date('m/d/Y', $t) : $d;
        };
        $child_count = $child_age !== null ? 1 : 0;
        $child_ages  = $child_age !== null ? (string) (int) $child_age : '';

        $params = array_filter(array(
            'hotelID'   => $hotel_id,
            'langID'    => 1,
            'checkin'   => $fmt_date($check_in),
            'checkout'  => $fmt_date($check_out),
            'rooms'     => $rooms,
            'adults'    => $adults,
            'children'  => $child_count ? $child_count : null,
            'childAges' => $child_ages !== '' ? $child_ages : null,
            'roomType'  => $matched_room && isset($matched_room['code']) ? $matched_room['code'] : null,
            'rmID'      => $matched_room && isset($matched_room['id']) ? $matched_room['id'] : null,
            'rate'      => $matched_rate && isset($matched_rate['code']) ? $matched_rate['code'] : null,
            'rcID'      => $matched_rate && isset($matched_rate['id']) ? $matched_rate['id'] : null,
        ), function ($v) { return $v !== null && $v !== ''; });

        $booking_url = 'https://res.windsurfercrs.com/ibe/details.aspx?' . http_build_query($params);

        return array('success' => true, 'url' => $booking_url);
    }

    /**
     * Get first non-empty value from array using given keys (case-insensitive).
     */
    private function pluck($arr, $keys) {
        if (!is_array($arr)) return null;
        foreach ($keys as $k) {
            foreach (array_keys($arr) as $actual) {
                if (strcasecmp($actual, $k) === 0 && isset($arr[$actual])) {
                    $v = $arr[$actual];
                    if ($v !== '' && $v !== null) return $v;
                    break;
                }
            }
        }
        return null;
    }

    /**
     * Call SHR /hotelDetails/{hotelCode} and return raw decoded data.
     * Uses cached token when valid; on 401 (Unauthorized) regenerates token and retries once.
     */
    private function call_shr_hotel_details($hotel_code) {
        $token_result = $this->get_shr_access_token(false);
        if (!$token_result['success']) {
            return $token_result;
        }

        $access_token = $token_result['access_token'];
        $is_manual    = !empty($token_result['manual']);
        $base_url    = $this->get_shr_shop_base_url();
        $base_url    = rtrim($base_url, '/');
        $url         = $base_url . '/hotelDetails/' . rawurlencode($hotel_code);

        $hotel_id    = get_option('dhr_shr_hotel_id', '');
        $language_id = get_option('dhr_shr_language_id', '4416');
        $channel_id  = get_option('dhr_shr_channel_id', '30');

        $params = array(
            'channelId'        => $channel_id,
            'requiredDetails'  => 'all',
        );
        if (!empty($hotel_id)) {
            $params['hotelID'] = $hotel_id;
        }
        if (!empty($language_id)) {
            $params['languageId'] = $language_id;
        }

        $params = apply_filters('dhr_shr_hotel_details_query_args', $params, $hotel_code);
        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }

        $result = $this->do_shr_hotel_details_request($url, $access_token);

        if (!empty($result['error'])) {
            return array(
                'success' => false,
                'error'   => $result['error'],
            );
        }

        $status_code = $result['status_code'];
        $body        = $result['body'];
        $data        = $result['data'];

        // 401 invalid_token: get fresh token from API and retry once (when client credentials are configured)
        if ($status_code === 401) {
            $client_id     = $this->get_shr_client_id();
            $client_secret = $this->get_shr_client_secret();
            if (!empty($client_id) && !empty($client_secret)) {
                $this->clear_shr_token_cache();
                $token_result = $this->get_shr_access_token(true, true);
                if ($token_result['success']) {
                    $result = $this->do_shr_hotel_details_request($url, $token_result['access_token']);
                    if (!empty($result['error'])) {
                        return array('success' => false, 'error' => $result['error']);
                    }
                    $status_code = $result['status_code'];
                    $body        = $result['body'];
                    $data        = $result['data'];
                }
            }
        }

        if ($status_code !== 200) {
            $error_message = sprintf(
                __('SHR hotelDetails request failed (status %d).', 'dhr-hotel-management'),
                $status_code
            );
            if (!empty($body)) {
                $error_data = json_decode($body, true);
                if (is_array($error_data)) {
                    if (isset($error_data['error'])) {
                        $error_message .= ' ' . $error_data['error'];
                    } elseif (isset($error_data['message'])) {
                        $error_message .= ' ' . $error_data['message'];
                    } elseif (isset($error_data['error_description'])) {
                        $error_message .= ' ' . $error_data['error_description'];
                    }
                } else {
                    $error_message .= ' Response: ' . substr(strip_tags($body), 0, 200);
                }
            }
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('DHR SHR API Error - URL: ' . $url);
                error_log('DHR SHR API Error - Status: ' . $status_code);
                error_log('DHR SHR API Error - Response: ' . $body);
            }
            return array(
                'success' => false,
                'error'   => $error_message,
                'details' => $body,
                'url'     => $url,
            );
        }

        if (!is_array($data)) {
            return array(
                'success' => false,
                'error'   => __('Invalid response format from SHR API.', 'dhr-hotel-management'),
                'details' => $body,
            );
        }

        // Store last API response for debugging / print (transient expires in 1 hour)
        set_transient('dhr_last_shr_api_response', array(
            'hotel_code' => $hotel_code,
            'body'       => $body,
            'data'       => $data,
            'at'         => current_time('mysql'),
        ), HOUR_IN_SECONDS);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('DHR SHR API Response [' . $hotel_code . ']: ' . substr($body, 0, 5000) . (strlen($body) > 5000 ? '...' : ''));
        }

        return array(
            'success' => true,
            'data'    => $data,
        );
    }

    /**
     * Normalise SHR hotel details data into the fields we need
     */
    private function normalise_shr_hotel_data($hotel_code, $raw_data) {
        // SHR responses wrap data inside "hotelDetailInfo"
        $info = isset($raw_data['hotelDetailInfo']) && is_array($raw_data['hotelDetailInfo'])
            ? $raw_data['hotelDetailInfo']
            : $raw_data;

        // Hotel name
        $name = isset($info['hotelName']) ? $info['hotelName'] : $hotel_code;

        // Description - prefer longDescription, fallback to sellingPoints
        $description = '';
        if (!empty($info['longDescription'])) {
            // Strip HTML tags for plain text, but keep line breaks
            $description = wp_strip_all_tags($info['longDescription']);
        } elseif (!empty($info['sellingPoints'])) {
            $description = $info['sellingPoints'];
        } elseif (!empty($info['generalPolicy'])) {
            $description = wp_strip_all_tags($info['generalPolicy']);
        }

        // Address from contactInfo.address, or locationDesc when no contactInfo
        $address   = '';
        $city      = '';
        $province  = '';
        $country   = 'South Africa';
        $postal_code = '';

        if (!empty($info['contactInfo']['address'])) {
            $addr = $info['contactInfo']['address'];
            if (!empty($addr['addressLine']) && is_array($addr['addressLine'])) {
                $address = implode(', ', array_filter($addr['addressLine']));
            }
            $city      = isset($addr['cityName']) ? $addr['cityName'] : '';
            $province  = isset($addr['stateProv']['state']) ? $addr['stateProv']['state'] : '';
            $postal_code = isset($addr['postalCode']) ? $addr['postalCode'] : '';
            if (!empty($addr['countryName']['code'])) {
                $country_map = array('ZA' => 'South Africa', 'US' => 'United States', 'GB' => 'United Kingdom');
                $country = isset($country_map[$addr['countryName']['code']]) ? $country_map[$addr['countryName']['code']] : $addr['countryName']['code'];
            }
        }
        if (empty($address) && !empty($info['locationDesc'])) {
            $address = wp_trim_words(wp_strip_all_tags($info['locationDesc']), 50);
            if (strlen($address) > 500) {
                $address = substr($address, 0, 497) . '...';
            }
        }

        // Coordinates (directly on hotelDetailInfo root)
        $latitude = isset($info['latitude']) ? floatval($info['latitude']) : 0;
        $longitude = isset($info['longitude']) ? floatval($info['longitude']) : 0;

        // Phone: contactInfo.phones first, then resPhone (API response format without contactInfo)
        $phone = '';
        if (!empty($info['contactInfo']['phones']) && is_array($info['contactInfo']['phones'])) {
            foreach ($info['contactInfo']['phones'] as $phone_obj) {
                if (isset($phone_obj['phoneTechType']) && $phone_obj['phoneTechType'] === 'voice'
                    && isset($phone_obj['primary']) && $phone_obj['primary'] === true) {
                    $country_code = isset($phone_obj['countryAccessCode']) ? $phone_obj['countryAccessCode'] : '';
                    $area_code    = isset($phone_obj['areaCityCode']) ? $phone_obj['areaCityCode'] : '';
                    $number       = isset($phone_obj['phoneNumber']) ? $phone_obj['phoneNumber'] : '';
                    if ($country_code && $area_code && $number) {
                        $phone = '+' . $country_code . '-' . $area_code . '-' . $number;
                    } elseif ($number) {
                        $phone = $number;
                    }
                    break;
                }
            }
        }
        if (empty($phone) && !empty($info['resPhone'])) {
            $phone = is_string($info['resPhone']) ? trim($info['resPhone']) : '';
        }

        // Email: contactInfo.emails first, then resEmail (API response format without contactInfo)
        $email = '';
        if (!empty($info['contactInfo']['emails']) && is_array($info['contactInfo']['emails'])) {
            $email_str = isset($info['contactInfo']['emails'][0]['value']) ? $info['contactInfo']['emails'][0]['value'] : '';
            if (strpos($email_str, ';') !== false) {
                $emails = explode(';', $email_str);
                $email = trim($emails[0]);
            } else {
                $email = trim($email_str);
            }
        }
        if (empty($email) && !empty($info['resEmail'])) {
            $email_str = is_string($info['resEmail']) ? $info['resEmail'] : '';
            if (strpos($email_str, ';') !== false) {
                $emails = explode(';', $email_str);
                $email = trim($emails[0]);
            } else {
                $email = trim($email_str);
            }
        }

        // Website from contactInfo.urLs array (prefer Property type)
        $website = '';
        if (!empty($info['contactInfo']['urLs']) && is_array($info['contactInfo']['urLs'])) {
            foreach ($info['contactInfo']['urLs'] as $url_obj) {
                if (isset($url_obj['type']) && $url_obj['type'] === 'Property') {
                    $url_value = isset($url_obj['value']) ? $url_obj['value'] : '';
                    if (!empty($url_value)) {
                        // Add https:// if missing
                        $website = (strpos($url_value, 'http') === 0) ? $url_value : 'https://' . $url_value;
                    }
                    break;
                }
            }
            // Fallback: use Reservation URL or urlHotel
            if (empty($website)) {
                foreach ($info['contactInfo']['urLs'] as $url_obj) {
                    if (isset($url_obj['type']) && $url_obj['type'] === 'Reservation') {
                        $url_value = isset($url_obj['value']) ? $url_obj['value'] : '';
                        if (!empty($url_value)) {
                            $website = (strpos($url_value, 'http') === 0) ? $url_value : 'https://' . $url_value;
                        }
                        break;
                    }
                }
            }
        }
        // Fallback: use urlHotel then resUrlBase (API response format)
        if (empty($website) && !empty($info['urlHotel'])) {
            $url_value = $info['urlHotel'];
            $website   = (strpos($url_value, 'http') === 0) ? $url_value : 'https://' . $url_value;
        }
        if (empty($website) && !empty($info['resUrlBase'])) {
            $url_value = $info['resUrlBase'];
            $website   = (strpos($url_value, 'http') === 0) ? $url_value : 'https://' . $url_value;
        }

        // Image URL: first propertyImage_Stardard, then propertyLogo, then first image with fileName
        $image_url = '';
        if (!empty($info['images']) && is_array($info['images'])) {
            foreach ($info['images'] as $img) {
                if (isset($img['mediaType']) && $img['mediaType'] === 'propertyImage_Stardard' && !empty($img['fileName'])) {
                    $image_url = $img['fileName'];
                    break;
                }
            }
            if (empty($image_url)) {
                foreach ($info['images'] as $img) {
                    if (isset($img['mediaType']) && $img['mediaType'] === 'propertyLogo' && !empty($img['fileName'])) {
                        $image_url = $img['fileName'];
                        break;
                    }
                }
            }
            if (empty($image_url) && !empty($info['images'][0]['fileName'])) {
                $image_url = $info['images'][0]['fileName'];
            }
        }

        // Google Maps URL - build from coordinates
        $google_maps_url = '';
        if ($latitude != 0 && $longitude != 0) {
            $google_maps_url = 'https://www.google.com/maps?q=' . $latitude . ',' . $longitude;
        }

        return array(
            'hotel_code'      => $hotel_code,
            'name'            => $name,
            'description'     => $description,
            'address'         => $address,
            'city'            => $city,
            'province'        => $province,
            'country'         => $country,
            'postal_code'     => $postal_code,
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'phone'           => $phone,
            'email'           => $email,
            'website'         => $website,
            'image_url'       => $image_url,
            'google_maps_url' => $google_maps_url,
            'raw'             => $raw_data,
        );
    }

    /**
     * Fetch hotel details from SHR and create/update local hotel + details
     */
    public function fetch_shr_and_save_hotel($hotel_code) {
        $hotel_code = trim($hotel_code);
        if ($hotel_code === '') {
            return array(
                'success' => false,
                'error'   => __('Hotel code is required.', 'dhr-hotel-management'),
            );
        }

        $api_result = $this->call_shr_hotel_details($hotel_code);
        if (!$api_result['success']) {
            return $api_result;
        }

        $normalised = $this->normalise_shr_hotel_data($hotel_code, $api_result['data']);

        $hotel_data = array(
            'hotel_code'      => $normalised['hotel_code'],
            'name'            => $normalised['name'] ?? '',
            'description'     => $normalised['description'] ?? '',
            'address'         => $normalised['address'] ?? '',
            'city'            => $normalised['city'] ?? '',
            'province'        => $normalised['province'] ?? '',
            'country'         => $normalised['country'] ?? 'South Africa',
            'latitude'        => isset($normalised['latitude']) ? floatval($normalised['latitude']) : 0,
            'longitude'       => isset($normalised['longitude']) ? floatval($normalised['longitude']) : 0,
            'phone'           => $normalised['phone'] ?? '',
            'email'           => $normalised['email'] ?? '',
            'website'         => $normalised['website'] ?? '',
            'image_url'       => $normalised['image_url'] ?? '',
            'google_maps_url' => $normalised['google_maps_url'] ?? '',
            'status'          => 'active',
        );

        $existing = DHR_Hotel_Database::get_hotel_by_code($hotel_code);

        if ($existing) {
            $hotel_id = $existing->id;
            $hotel_data['manual_entry'] = 0;
            $updated  = DHR_Hotel_Database::update_hotel($hotel_id, $hotel_data);
            if (!$updated) {
                global $wpdb;
                $db_error = $wpdb->last_error ? ' ' . $wpdb->last_error : '';
                return array(
                    'success' => false,
                    'error'   => __('Failed to update existing hotel record.', 'dhr-hotel-management') . $db_error,
                );
            }
        } else {
            $hotel_id = DHR_Hotel_Database::insert_hotel($hotel_data);
            if ($hotel_id === false) {
                global $wpdb;
                $db_error = $wpdb->last_error ? ' ' . $wpdb->last_error : '';
                return array(
                    'success' => false,
                    'error'   => __('Failed to insert new hotel record.', 'dhr-hotel-management') . $db_error,
                );
            }
        }

        // Store detailed SHR data in the hotel details table
        $info = isset($normalised['raw']['hotelDetailInfo']) 
            ? $normalised['raw']['hotelDetailInfo'] 
            : (isset($normalised['raw']) ? $normalised['raw'] : array());

        // Extract check-in/out times from policies
        $check_in_time = '';
        $check_out_time = '';
        if (!empty($info['policies']['policyInfo'])) {
            $policy_info = $info['policies']['policyInfo'];
            $check_in_time = isset($policy_info['checkInTime']) ? $policy_info['checkInTime'] : '';
            $check_out_time = isset($policy_info['checkOutTime']) ? $policy_info['checkOutTime'] : '';
        }

        // Extract cancellation policy
        $cancellation_policy = '';
        if (!empty($info['policies']['cancelPolicy']['cancelPenalty']) 
            && is_array($info['policies']['cancelPolicy']['cancelPenalty'])) {
            $penalties = array();
            foreach ($info['policies']['cancelPolicy']['cancelPenalty'] as $penalty) {
                if (!empty($penalty['penaltyDescription'])) {
                    $penalties[] = $penalty['penaltyDescription'];
                }
            }
            $cancellation_policy = implode("\n\n", $penalties);
        }

        // Extract guarantee policy
        $guarantee_policy = '';
        if (!empty($info['policies']['guaranteePaymentPolicy']['guaranteePayment']) 
            && is_array($info['policies']['guaranteePaymentPolicy']['guaranteePayment'])) {
            $guarantees = array();
            foreach ($info['policies']['guaranteePaymentPolicy']['guaranteePayment'] as $guarantee) {
                if (!empty($guarantee['description'])) {
                    $guarantees[] = $guarantee['description'];
                }
            }
            $guarantee_policy = implode("\n\n", $guarantees);
        }

        // Extract pets policy
        $pets_allowed = '';
        if (!empty($info['policies']['petsPolicy'])) {
            $pets_policy = $info['policies']['petsPolicy'];
            $pets_allowed = isset($pets_policy['petsAllowed']) && $pets_policy['petsAllowed'] === true ? 'Yes' : 'No';
            if (!empty($pets_policy['description'])) {
                $pets_allowed .= ' - ' . $pets_policy['description'];
            }
        }

        // Extract commission percent
        $commission_percent = null;
        if (!empty($info['policies']['commissionPolicy']['percent'])) {
            $commission_percent = floatval($info['policies']['commissionPolicy']['percent']);
        }

        // Chain info
        $chain_code = isset($info['chainCode']) ? $info['chainCode'] : '';
        $chain_name = isset($info['chainCode']) ? $info['chainCode'] : ''; // Can be extended if chain name is available

        // Currency
        $currency_code = '';
        if (!empty($info['currencies']) && is_array($info['currencies'])) {
            foreach ($info['currencies'] as $curr) {
                if (isset($curr['default']) && $curr['default'] === true) {
                    $currency_code = isset($curr['code']) ? $curr['code'] : '';
                    break;
                }
            }
        }

        // Language
        $language_code = '';
        if (!empty($info['languageCodes']) && is_array($info['languageCodes'])) {
            $language_code = $info['languageCodes'][0];
        }

        // Time zone
        $time_zone = '';
        if (!empty($info['timeZone'])) {
            $time_zone = $info['timeZone'];
        }

        $details = array(
            'hotel_code'          => $normalised['hotel_code'],
            'hotel_name'          => $normalised['name'],
            'chain_code'          => $chain_code,
            'chain_name'          => $chain_name,
            'currency_code'       => $currency_code,
            'language_code'       => $language_code,
            'time_zone'           => $time_zone,
            'description'         => $normalised['description'],
            'latitude'            => $normalised['latitude'],
            'longitude'           => $normalised['longitude'],
            'check_in_time'       => $check_in_time,
            'check_out_time'      => $check_out_time,
            'cancellation_policy' => $cancellation_policy,
            'guarantee_policy'    => $guarantee_policy,
            'pets_allowed'        => $pets_allowed,
            'commission_percent'  => $commission_percent,
            'raw_xml_data'        => wp_json_encode($normalised['raw']),
        );

        DHR_Hotel_Database::save_hotel_details($details);

        return array(
            'success'    => true,
            'hotel_id'   => $hotel_id,
            'hotel_code' => $normalised['hotel_code'],
            'hotel_name' => $normalised['name'],
        );
    }
}
