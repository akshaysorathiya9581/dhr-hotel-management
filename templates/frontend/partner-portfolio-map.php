<?php
/**
 * Partner Portfolio Map Template (Map 3)
 */

if (!defined('ABSPATH')) {
    exit;
}

$overview_label = isset($settings['overview_label']) ? $settings['overview_label'] : 'DISCOVER AFRICA';
$main_heading = isset($settings['main_heading']) ? $settings['main_heading'] : 'Our Partner Portfolio';
$description = isset($settings['description']) ? $settings['description'] : 'Together with CityBlue Hotels, we\'re crafting a unified hospitality experience that celebrates the rich cultures, stunning landscapes, and warm hospitality that Africa is known for.';
$legend_cityblue = isset($settings['legend_cityblue']) ? $settings['legend_cityblue'] : 'CityBlue Hotels';
$legend_dream = isset($settings['legend_dream']) ? $settings['legend_dream'] : 'Dream Hotels & Resorts';
?>

<div class="dhr-partner-portfolio-map-container" style="height: <?php echo esc_attr($atts['height']); ?>;">
    <div class="dhr-partner-portfolio-content">
        <p class="dhr-overview-label"><?php echo esc_html($overview_label); ?></p>
        <h2 class="dhr-partner-main-heading"><?php echo esc_html($main_heading); ?></h2>
        <p class="dhr-partner-description"><?php echo esc_html($description); ?></p>
        <div class="dhr-partner-legend">
            <div class="dhr-legend-item">
                <span class="dhr-legend-dot dhr-legend-cityblue"></span>
                <span class="dhr-legend-text"><?php echo esc_html($legend_cityblue); ?></span>
            </div>
            <div class="dhr-legend-item">
                <span class="dhr-legend-dot dhr-legend-dream"></span>
                <span class="dhr-legend-text"><?php echo esc_html($legend_dream); ?></span>
            </div>
        </div>
    </div>
    <div class="dhr-partner-portfolio-map-wrapper">
        <div id="dhr-partner-portfolio-map" class="dhr-partner-portfolio-map"></div>
    </div>
</div>

<script>
(function() {
    function initPartnerPortfolioMap() {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            setTimeout(initPartnerPortfolioMap, 100);
            return;
        }
        
        var mapElement = document.getElementById('dhr-partner-portfolio-map');
        if (!mapElement) {
            return;
        }
        
        var hotels = (typeof dhrHotelsData !== 'undefined' && dhrHotelsData.hotels) ? dhrHotelsData.hotels : [];
        
        // Initialize map centered on Africa
        var map = new google.maps.Map(mapElement, {
            zoom: 4,
            center: { lat: -1.9403, lng: 29.8739 }, // Center of Africa
            styles: [
                {
                    featureType: 'all',
                    elementType: 'geometry',
                    stylers: [{ color: '#B8D4E8' }]
                },
                {
                    featureType: 'water',
                    elementType: 'geometry',
                    stylers: [{ color: '#B8D4E8' }]
                }
            ]
        });
        
        // Create markers - alternate between CityBlue (blue) and Dream (light blue)
        if (hotels.length > 0) {
            var bounds = new google.maps.LatLngBounds();
            
            hotels.forEach(function(hotel, index) {
                var isCityBlue = index % 2 === 0;
                var markerColor = isCityBlue ? '#0066CC' : '#66B3FF';
                var hotelLocation = { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) };
                bounds.extend(hotelLocation);
                
                var marker = new google.maps.Marker({
                    position: hotelLocation,
                    map: map,
                    title: hotel.name,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 8,
                        fillColor: markerColor,
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
                map.setZoom(12);
            }
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPartnerPortfolioMap);
    } else {
        initPartnerPortfolioMap();
    }
})();
</script>

