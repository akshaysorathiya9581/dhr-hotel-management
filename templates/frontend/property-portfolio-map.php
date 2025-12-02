<?php
/**
 * Property Portfolio Map Template (Map 6)
 */

if (!defined('ABSPATH')) {
    exit;
}

$panel_title = isset($settings['panel_title']) ? $settings['panel_title'] : 'Ownership Property Portfolio';
?>

<div class="dhr-property-map-container" style="height: <?php echo esc_attr($atts['height']); ?>;">
    <div class="dhr-property-map-wrapper">
        <div id="dhr-property-map" class="dhr-property-map"></div>
    </div>
    <div class="dhr-property-panel">
        <h4><?php echo esc_html($panel_title); ?></h4>
        <ul>
            <?php if (!empty($hotels)): ?>
                <?php foreach ($hotels as $index => $hotel): ?>
                    <li data-hotel-id="<?php echo esc_attr($hotel->id); ?>" data-index="<?php echo esc_attr($index + 1); ?>">
                        <?php echo esc_html($hotel->name); ?>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script>
(function() {
    function initPropertyPortfolioMap() {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            setTimeout(initPropertyPortfolioMap, 100);
            return;
        }
        
        var mapElement = document.getElementById('dhr-property-map');
        if (!mapElement) {
            return;
        }
        
        var hotels = (typeof dhrHotelsData !== 'undefined' && dhrHotelsData.hotels) ? dhrHotelsData.hotels : [];
        var markers = [];
        var infoWindows = [];
        
        if (hotels.length > 0) {
            var bounds = new google.maps.LatLngBounds();
            var centerLat = 0;
            var centerLng = 0;
            
            hotels.forEach(function(hotel) {
                var hotelLocation = { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) };
                centerLat += hotelLocation.lat;
                centerLng += hotelLocation.lng;
                bounds.extend(hotelLocation);
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
                        stylers: [{ color: '#A0B6CB' }]
                    }
                ]
            });
            
            // Custom HTML Div Marker Class using OverlayView with number support
            function CustomDivMarker(position, map, title, className, labelText) {
                this.position = position;
                this.map = map;
                this.title = title;
                this.className = className || 'dhr-head-office-marker';
                this.labelText = labelText || '';
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
                div.style.display = 'flex';
                div.style.alignItems = 'center';
                div.style.justifyContent = 'center';
                
                // Add label text if provided
                if (this.labelText) {
                    div.textContent = this.labelText;
                    div.style.color = '#fff';
                    div.style.fontSize = '16px';
                    div.style.lineHeight = '1';
                }
                
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
            
            hotels.forEach(function(hotel, index) {
                var number = (index + 1).toString().padStart(2, '0');
                var hotelLocation = { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) };
                
                // Create numbered marker
                var marker = new CustomDivMarker(
                    hotelLocation,
                    map,
                    hotel.name,
                    'dhr-property-portfolio-marker',
                    number
                );
                
                // Create info window
                var infoContent = '<div class="dhr-property-info-window">' +
                    '<img src="' + (hotel.image_url || dhrHotelsData.pluginUrl + 'assets/images/default-hotel.jpg') + '" alt="' + hotel.name + '" style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px; margin-bottom: 10px;">' +
                    '<h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: bold;">' + hotel.name + '</h4>' +
                    '<p style="margin: 0 0 10px 0; font-size: 12px; color: #666;">' + hotel.city + ', ' + hotel.province + '</p>' +
                    '<a href="' + (hotel.google_maps_url || '#') + '" target="_blank" style="display: inline-block; padding: 8px 16px; background: #0066CC; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px;">View Packages</a>' +
                    '</div>';
                
                var infoWindow = new google.maps.InfoWindow({
                    content: infoContent
                });
                
                // Store info window on marker
                marker.infoWindow = infoWindow;
                
                markers.push({ marker: marker, hotel: hotel, index: index });
                infoWindows.push(infoWindow);
            });
            
            // Handle property list item clicks
            var propertyItems = document.querySelectorAll('.dhr-property-panel li');
            propertyItems.forEach(function(item) {
                item.style.cursor = 'pointer';
                item.addEventListener('click', function() {
                    var hotelId = parseInt(this.getAttribute('data-hotel-id'));
                    var markerData = markers.find(function(m) {
                        return m.hotel.id == hotelId;
                    });
                    
                    if (markerData) {
                        infoWindows.forEach(function(iw) { iw.close(); });
                        var infoWindow = infoWindows[markerData.index];
                        var position = markerData.marker.getPosition();
                        infoWindow.setPosition(position);
                        infoWindow.open(map);
                        map.setCenter(position);
                        map.setZoom(15);
                    }
                });
            });
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPropertyPortfolioMap);
    } else {
        initPropertyPortfolioMap();
    }
})();
</script>

