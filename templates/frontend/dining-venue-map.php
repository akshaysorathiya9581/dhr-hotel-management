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
    <div class="dhr-dining-venue-area">
        <div class="dhr-map-row align-items-end">
            <div class="dhr-map-left">
                <div class="dhr-dining-venue-block">
                    <p class="dhr-map-label"><?php echo esc_html($overview_label); ?></p>
                    <h2 class="dhr-map-title"><?php echo esc_html($main_heading); ?></h2>
                    <p class="dhr-map-description"><?php echo esc_html($description); ?></p>
                    <div>
                    <div class="dhr-map-reservation">
                        <div class="dhr-dining-phone-icon">
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="#0B5991" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.8203 3.75C10.166 3.75 9.52149 3.98438 8.98438 4.41406L8.90626 4.45312L8.86719 4.49219L4.96094 8.51562L5.00001 8.55469C3.79395 9.66797 3.42286 11.333 3.94532 12.7734C3.9502 12.7832 3.94044 12.8027 3.94532 12.8125C5.00489 15.8447 7.71485 21.6992 13.0078 26.9922C18.3203 32.3047 24.2529 34.9072 27.1875 36.0547H27.2266C28.7451 36.5625 30.3906 36.2012 31.5625 35.1953L35.5078 31.25C36.543 30.2148 36.543 28.418 35.5078 27.3828L30.4297 22.3047L30.3906 22.2266C29.3555 21.1914 27.5195 21.1914 26.4844 22.2266L23.9844 24.7266C23.0811 24.292 20.9277 23.1787 18.8672 21.2109C16.8213 19.2578 15.7764 17.0117 15.3906 16.1328L17.8906 13.6328C18.9404 12.583 18.96 10.835 17.8516 9.80469L17.8906 9.76562L17.7734 9.64844L12.7734 4.49219L12.7344 4.45312L12.6563 4.41406C12.1191 3.98438 11.4746 3.75 10.8203 3.75Z" fill="none"/>
                            </svg>
                        </div>
                        <div>
                            <p class="dhr-map-label"><?php echo esc_html($reservation_label); ?></p>
                            <p class="dhr-map-description"><?php echo esc_html($reservation_phone); ?></p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <div class="dhr-map-right">
                <div id="dhr-dining-venue-map" class="dhr-dining-venue-map"></div>
            </div>
        </div>
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
                        stylers: [{ color: '#ffffff' }]
                    },
                    {
                        featureType: 'water',
                        elementType: 'geometry',
                        stylers: [{ color: '#b1c2a8' }]
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
            
            if (hotels.length > 1) {
                map.fitBounds(bounds);
            }
            
            hotels.forEach(function(hotel) {
                var hotelLocation = { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) };
                
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
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDiningVenueMap);
    } else {
        initDiningVenueMap();
    }
})();
</script>

