<?php
/**
 * Head Office Map Template (Map 2)
 */

if (!defined('ABSPATH')) {
    exit;
}

$title = isset($settings['title']) ? $settings['title'] : 'Head Office';
$address = isset($settings['address']) ? $settings['address'] : '330 Main Road, Bryanston 2021, Gauteng, South Africa';
$latitude = isset($settings['latitude']) ? $settings['latitude'] : '';
$longitude = isset($settings['longitude']) ? $settings['longitude'] : '';
$google_maps_url = isset($settings['google_maps_url']) ? $settings['google_maps_url'] : '';
?>

<div class="dhr-head-office-map-container" style="height: <?php echo esc_attr($atts['height']); ?>;">
    <div class="dhr-head-office-info">
        <h2 class="dhr-head-office-title"><?php echo esc_html($title); ?></h2>
        <p class="dhr-head-office-address">Address: <?php echo esc_html($address); ?></p>
        <?php if (!empty($google_maps_url)): ?>
        <a href="<?php echo esc_url($google_maps_url); ?>" target="_blank" rel="noopener noreferrer" class="dhr-google-maps-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 2C6.13 2 3 5.13 3 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S8.62 6.5 10 6.5 12.5 7.62 12.5 9 11.38 11.5 10 11.5z" fill="currentColor"/>
            </svg>
            View On Google Maps
        </a>
        <?php endif; ?>
    </div>
    <div class="dhr-head-office-map-wrapper">
        <div id="dhr-head-office-map" class="dhr-head-office-map"></div>
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
            
            // Add hotel markers
            if (hotels.length > 0) {
                hotels.forEach(function(hotel) {
                    var hotelLocation = { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) };
                    bounds.extend(hotelLocation);
                    
                    var marker = new google.maps.Marker({
                        position: hotelLocation,
                        map: map,
                        title: hotel.name,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 10,
                            fillColor: '#0066CC',
                            fillOpacity: 1,
                            strokeColor: '#fff',
                            strokeWeight: 2
                        }
                    });
                    
                    // Add info window
                    var infoContent = '<div style="padding: 10px; max-width: 250px;">' +
                        '<h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: bold;">' + hotel.name + '</h4>' +
                        '<p style="margin: 0 0 5px 0; font-size: 12px; color: #666;">' + hotel.address + '</p>' +
                        '<p style="margin: 0; font-size: 12px; color: #666;">' + hotel.city + ', ' + hotel.province + '</p>' +
                        '</div>';
                    
                    var infoWindow = new google.maps.InfoWindow({
                        content: infoContent
                    });
                    
                    marker.addListener('click', function() {
                        infoWindow.open(map, marker);
                    });
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
                    new google.maps.Marker({
                        position: centerLocation,
                        map: map,
                        title: '<?php echo esc_js($title); ?>'
                    });
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

