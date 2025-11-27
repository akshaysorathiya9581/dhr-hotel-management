<?php
/**
 * Dining Venue Map Template (Map 4)
 */

if (!defined('ABSPATH')) {
    exit;
}

$overview_label = isset($settings['overview_label']) ? $settings['overview_label'] : 'OVERVIEW';
$main_heading = isset($settings['main_heading']) ? $settings['main_heading'] : 'Find A Dining Venue';
$description = isset($settings['description']) ? $settings['description'] : 'Whether you\'re savoring fresh seafood with a view of Table Mountain or indulging in gourmet delights by the Indian Ocean, our dining experiences promise to delight every palate.';
$reservation_label = isset($settings['reservation_label']) ? $settings['reservation_label'] : 'RESERVATION BY PHONE';
$reservation_phone = isset($settings['reservation_phone']) ? $settings['reservation_phone'] : '+27 (0)13 243 9401/2';
?>

<div class="dhr-dining-venue-map-container" style="height: <?php echo esc_attr($atts['height']); ?>;">
    <div class="dhr-dining-venue-content">
        <p class="dhr-dining-overview-label"><?php echo esc_html($overview_label); ?></p>
        <h2 class="dhr-dining-main-heading"><?php echo esc_html($main_heading); ?></h2>
        <p class="dhr-dining-description"><?php echo esc_html($description); ?></p>
        <div class="dhr-dining-reservation">
            <span class="dhr-dining-phone-icon">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.8203 3.75C10.166 3.75 9.52149 3.98438 8.98438 4.41406L8.90626 4.45312L8.86719 4.49219L4.96094 8.51562L5.00001 8.55469C3.79395 9.66797 3.42286 11.333 3.94532 12.7734C3.9502 12.7832 3.94044 12.8027 3.94532 12.8125C5.00489 15.8447 7.71485 21.6992 13.0078 26.9922C18.3203 32.3047 24.2529 34.9072 27.1875 36.0547H27.2266C28.7451 36.5625 30.3906 36.2012 31.5625 35.1953L35.5078 31.25C36.543 30.2148 36.543 28.418 35.5078 27.3828L30.4297 22.3047L30.3906 22.2266C29.3555 21.1914 27.5195 21.1914 26.4844 22.2266L23.9844 24.7266C23.0811 24.292 20.9277 23.1787 18.8672 21.2109C16.8213 19.2578 15.7764 17.0117 15.3906 16.1328L17.8906 13.6328C18.9404 12.583 18.96 10.835 17.8516 9.80469L17.8906 9.76562L17.7734 9.64844L12.7734 4.49219L12.7344 4.45312L12.6563 4.41406C12.1191 3.98438 11.4746 3.75 10.8203 3.75Z" fill="currentColor"/>
                </svg>
            </span>
            <div>
                <p class="dhr-dining-reservation-label"><?php echo esc_html($reservation_label); ?></p>
                <p class="dhr-dining-phone-number"><?php echo esc_html($reservation_phone); ?></p>
            </div>
        </div>
    </div>
    <div class="dhr-dining-venue-map-wrapper">
        <div id="dhr-dining-venue-map" class="dhr-dining-venue-map"></div>
    </div>
</div>

<script>
(function() {
    function initDiningVenueMap() {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            setTimeout(initDiningVenueMap, 100);
            return;
        }
        
        var mapElement = document.getElementById('dhr-dining-venue-map');
        if (!mapElement) {
            return;
        }
        
        var hotels = (typeof dhrHotelsData !== 'undefined' && dhrHotelsData.hotels) ? dhrHotelsData.hotels : [];
        
        if (hotels.length > 0) {
            var bounds = new google.maps.LatLngBounds();
            var centerLat = 0;
            var centerLng = 0;
            
            hotels.forEach(function(hotel) {
                centerLat += parseFloat(hotel.latitude);
                centerLng += parseFloat(hotel.longitude);
                bounds.extend(new google.maps.LatLng(
                    parseFloat(hotel.latitude),
                    parseFloat(hotel.longitude)
                ));
            });
            
            centerLat = centerLat / hotels.length;
            centerLng = centerLng / hotels.length;
            
            var map = new google.maps.Map(mapElement, {
                zoom: 10,
                center: { lat: centerLat, lng: centerLng },
                styles: [
                    {
                        featureType: 'all',
                        elementType: 'geometry',
                        stylers: [{ color: '#f5f5f5' }]
                    },
                    {
                        featureType: 'water',
                        elementType: 'geometry',
                        stylers: [{ color: '#B8E6B8' }]
                    }
                ]
            });
            
            if (hotels.length > 1) {
                map.fitBounds(bounds);
            }
            
            hotels.forEach(function(hotel) {
                var marker = new google.maps.Marker({
                    position: { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) },
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
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDiningVenueMap);
    } else {
        initDiningVenueMap();
    }
})();
</script>

