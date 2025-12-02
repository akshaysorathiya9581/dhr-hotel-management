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
    <div class="dhr-partner-portfolio-area">
        <div class="dhr-map-row align-items-end">
            <div class="dhr-map-left">
                <div class="dhr-partner-portfolio-block">
                    <p class="dhr-map-label"><?php echo esc_html($overview_label); ?></p>
                    <h2 class="dhr-map-title dhr-text-primary"><?php echo esc_html($main_heading); ?></h2>
                    <p class="dhr-map-description"><?php echo esc_html($description); ?></p>
                    <div>
                        <div class="dhr-partner-portfolio-legend">
                            <ul>
                                <li><?php echo esc_html($legend_cityblue); ?></li>
                                <li><?php echo esc_html($legend_dream); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dhr-map-right">
                <div id="dhr-partner-portfolio-map" class="dhr-partner-portfolio-map"></div>
            </div>
        </div>
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
                    stylers: [{ color: '#ffffff' }]
                },
                {
                    featureType: 'water',
                    elementType: 'geometry',
                    stylers: [{ color: '#E2EFF7' }]
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
        
        // Create markers - alternate between CityBlue (blue) and Dream (light blue)
        if (hotels.length > 0) {
            var bounds = new google.maps.LatLngBounds();
            
            hotels.forEach(function(hotel, index) {
                var isCityBlue = index % 2 === 0;
                var markerClassName = isCityBlue ? 'dhr-head-office-marker dhr-cityblue-marker' : 'dhr-head-office-marker dhr-dream-marker';
                var hotelLocation = { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) };
                bounds.extend(hotelLocation);
                
                var marker = new CustomDivMarker(
                    hotelLocation,
                    map,
                    hotel.name,
                    markerClassName
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

