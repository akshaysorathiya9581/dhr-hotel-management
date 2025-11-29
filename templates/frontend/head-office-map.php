<?php
/**
 * Head Office Map Template (Map 2)
 */

if (!defined('ABSPATH')) {
    exit;
}

$title = isset($settings['title']) ? $settings['title'] : 'Head Office';
$address = isset($settings['address']) ? $settings['address'] : '310 Main Road, Bryanston 2021, Gauteng, South Africa';
$phone1 = isset($settings['phone1']) ? $settings['phone1'] : '+27 (0) 11 267 8300';
$phone2 = isset($settings['phone2']) ? $settings['phone2'] : '+27 861 010 347';
$po_box = isset($settings['po_box']) ? $settings['po_box'] : '86027, Sandton 2146, Gauteng, South Africa';
$email = isset($settings['email']) ? $settings['email'] : 'info@dreamresorts.co.za';
$trade_phone = isset($settings['trade_phone']) ? $settings['trade_phone'] : '+27 (0) 11 267 8300';
$trade_email = isset($settings['trade_email']) ? $settings['trade_email'] : 'trade@dreamresorts.co.za';
$complaints_phone = isset($settings['complaints_phone']) ? $settings['complaints_phone'] : '+27 (0) 11 267 8300';
$complaints_email = isset($settings['complaints_email']) ? $settings['complaints_email'] : 'complaints@dreamresorts.co.za';
$latitude = isset($settings['latitude']) ? $settings['latitude'] : '';
$longitude = isset($settings['longitude']) ? $settings['longitude'] : '';
$google_maps_url = isset($settings['google_maps_url']) ? $settings['google_maps_url'] : '';
$twitter_url = isset($settings['twitter_url']) ? $settings['twitter_url'] : '#';
$instagram_url = isset($settings['instagram_url']) ? $settings['instagram_url'] : '#';
$facebook_url = isset($settings['facebook_url']) ? $settings['facebook_url'] : '#';
$linkedin_url = isset($settings['linkedin_url']) ? $settings['linkedin_url'] : '#';
$youtube_url = isset($settings['youtube_url']) ? $settings['youtube_url'] : '#';
?>

<div class="dhr-head-office-map-container" style="height: <?php echo esc_attr($atts['height']); ?>;">
    <div class="dhr-head-office-map-area">
        <div id="dhr-head-office-map" class="dhr-head-office-map"></div>
    </div>
    <div class="dhr-map-container">
        <div class="dhr-head-office-area">
            <div class="dhr-map-row justify-content-between">
                <div class="dhr-head-office-left">
                    <h2 class="dhr-head-office-title dhr-text-primary mb-0"><?php echo esc_html($title); ?></h2>
                </div>
                <div class="dhr-head-office-right">
                    <?php if (!empty($google_maps_url)): ?>
                        <a href="<?php echo esc_url($google_maps_url); ?>" target="_blank" rel="noopener noreferrer" class="dhr-google-maps-btn">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 2C6.13 2 3 5.13 3 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S8.62 6.5 10 6.5 12.5 7.62 12.5 9 11.38 11.5 10 11.5z" fill="currentColor"/>
                            </svg>
                            View On Google Maps
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="dhr-map-row justify-content-between">
                <div class="dhr-head-office-left">
                    <div class="dhr-head-office-block">
                        <ul>
                            <li>
                                Address: <?php echo esc_html($address); ?>
                            </li>
                            <li>
                                Phone Number: <?php echo esc_html($phone1); ?>
                            </li>
                            <li>
                                Phone Number: <?php echo esc_html($phone2); ?>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="dhr-head-office-right">
                    <div class="dhr-head-office-block">
                        <ul>
                            <li>
                                PO Box: <?php echo esc_html($po_box); ?>
                            </li>
                            <li>
                                Email: <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                            </li>
                            <li>
                                <div class="dhr-head-office-social-list">
                                    <ul>
                                        <li>
                                            <a href="<?php echo esc_url($twitter_url); ?>" target="_blank" rel="noopener noreferrer" class="dhr-social-icon">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo esc_url($instagram_url); ?>" target="_blank" rel="noopener noreferrer" class="dhr-social-icon">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo esc_url($facebook_url); ?>" target="_blank" rel="noopener noreferrer" class="dhr-social-icon">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener noreferrer" class="dhr-social-icon">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo esc_url($youtube_url); ?>" target="_blank" rel="noopener noreferrer" class="dhr-social-icon">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="dhr-map-row justify-content-between">
                <div class="dhr-head-office-left">
                    <div class="dhr-head-office-block">
                        <h4 class="dhr-head-office-block-subtitle dhr-text-primary">Trade Enquiries</h4>
                        <ul>
                            <li>
                                Phone Number: <?php echo esc_html($trade_phone); ?>
                            </li>
                            <li>
                                Email: <a href="mailto:<?php echo esc_attr($trade_email); ?>"><?php echo esc_html($trade_email); ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="dhr-head-office-right">
                    <div class="dhr-head-office-block">
                        <h4 class="dhr-head-office-block-subtitle dhr-text-primary">Complaints</h4>
                        <ul>
                            <li>
                                Phone Number: <?php echo esc_html($complaints_phone); ?>
                            </li>
                            <li>
                                Email: <a href="mailto:<?php echo esc_attr($complaints_email); ?>"><?php echo esc_html($complaints_email); ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function initHeadOfficeMap() {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            // Wait for Google Maps API to load
            setTimeout(initHeadOfficeMap, 100);
            return;
        }
        
        var mapElement = document.getElementById('dhr-head-office-map');
        if (!mapElement) {
            return;
        }
        
        // Get hotels data
        var hotels = (typeof dhrHotelsData !== 'undefined' && dhrHotelsData.hotels) ? dhrHotelsData.hotels : [];
        
        // Initialize map
        var map;
        var centerLocation = null;
        var bounds = new google.maps.LatLngBounds();
        
        // Use coordinates if available, otherwise geocode address
        var latitude = <?php echo !empty($latitude) ? floatval($latitude) : 'null'; ?>;
        var longitude = <?php echo !empty($longitude) ? floatval($longitude) : 'null'; ?>;
        
        function initializeMapWithHotels(centerLocation) {
            // Create map
            map = new google.maps.Map(mapElement, {
                zoom: hotels.length > 1 ? 10 : 15,
                center: centerLocation,
                styles: [
                    {
                        featureType: 'all',
                        elementType: 'geometry',
                        stylers: [{ color: '#f5f5f5' }]
                    },
                    {
                        featureType: 'water',
                        elementType: 'geometry',
                        stylers: [{ color: '#e0e0e0' }]
                    }
                ]
            });
            
            // Custom HTML Div Marker Class using OverlayView
            function CustomDivMarker(position, map, title, className) {
                this.position = position;
                this.map = map;
                this.title = title;
                this.className = className || 'dhr-head-office-marker';
                this.div = null;
                this.infoWindow = null;
                this.setMap(map);
            }
            
            CustomDivMarker.prototype = new google.maps.OverlayView();
            
            CustomDivMarker.prototype.onAdd = function() {
                var self = this;
                var div = document.createElement('div');
                div.className = this.className;
                div.style.position = 'absolute';
                div.style.cursor = 'pointer';
                
                this.div = div;
                var panes = this.getPanes();
                panes.overlayMouseTarget.appendChild(div);
                
                // Add click listener
                google.maps.event.addDomListener(div, 'click', function() {
                    if (self.infoWindow) {
                        self.infoWindow.setPosition(self.getPosition());
                        self.infoWindow.open(self.map);
                    }
                });
            };
            
            CustomDivMarker.prototype.draw = function() {
                var overlayProjection = this.getProjection();
                var position = overlayProjection.fromLatLngToDivPixel(this.position);
                var div = this.div;
                
                if (div) {
                    div.style.left = position.x + 'px';
                    div.style.top = position.y + 'px';
                }
            };
            
            CustomDivMarker.prototype.onRemove = function() {
                if (this.div) {
                    this.div.parentNode.removeChild(this.div);
                    this.div = null;
                }
            };
            
            CustomDivMarker.prototype.getPosition = function() {
                return this.position;
            };
            
            // Add hotel markers
            if (hotels.length > 0) {
                hotels.forEach(function(hotel) {
                    var hotelLocation = { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) };
                    bounds.extend(hotelLocation);
                    
                    var marker = new CustomDivMarker(
                        hotelLocation,
                        map,
                        hotel.name,
                        'dhr-head-office-marker'
                    );
                    
                    // Add info window
                    var infoContent = '<div class="dhr-info-window">' +
                        '<h4 class="dhr-info-window-title">' + hotel.name + '</h4>' +
                        '<p class="dhr-info-window-content">' + hotel.address + '</p>' +
                        '<p class="dhr-info-window-content mb-0">' + hotel.city + ', ' + hotel.province + '</p>' +
                        '</div>';
                    
                    var infoWindow = new google.maps.InfoWindow({
                        content: infoContent
                    });
                    
                    // Store info window on marker
                    marker.infoWindow = infoWindow;
                });
                
                // Fit bounds to show all hotels
                if (hotels.length > 1) {
                    map.fitBounds(bounds);
                } else if (hotels.length === 1) {
                    map.setCenter({ lat: parseFloat(hotels[0].latitude), lng: parseFloat(hotels[0].longitude) });
                    map.setZoom(15);
                }
            } else {
                // No hotels, show head office location
                if (centerLocation) {
                    var headOfficeMarker = new CustomDivMarker(
                        centerLocation,
                        map,
                        '<?php echo esc_js($title); ?>',
                        'dhr-head-office-marker'
                    );
                }
            }
        }
        
        if (latitude !== null && longitude !== null) {
            // Use coordinates directly (no Geocoding API needed)
            centerLocation = { lat: latitude, lng: longitude };
            initializeMapWithHotels(centerLocation);
        } else if (hotels.length > 0) {
            // Use first hotel as center if no coordinates provided
            centerLocation = { lat: parseFloat(hotels[0].latitude), lng: parseFloat(hotels[0].longitude) };
            initializeMapWithHotels(centerLocation);
        } else {
            // Fallback to geocoding if no hotels and no coordinates
            var address = <?php echo json_encode($address); ?>;
            var geocoder = new google.maps.Geocoder();
            
            geocoder.geocode({ address: address }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    centerLocation = results[0].geometry.location;
                    initializeMapWithHotels(centerLocation);
                } else {
                    // Handle geocoding errors gracefully
                    if (status === 'REQUEST_DENIED') {
                        console.warn('Geocoding API not enabled. Using fallback coordinates.');
                        centerLocation = { lat: -26.0519, lng: 28.0231 };
                        initializeMapWithHotels(centerLocation);
                    } else {
                        console.error('Geocoding failed: ' + status);
                        mapElement.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;"><p>Unable to load map. Please check your Google Maps API configuration.</p><p style="font-size: 12px;">Error: ' + status + '</p></div>';
                    }
                }
            });
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeadOfficeMap);
    } else {
        initHeadOfficeMap();
    }
})();
</script>

