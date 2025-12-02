<?php
/**
 * Lodges & Camps Map Template (Map 7)
 */

if (!defined('ABSPATH')) {
    exit;
}

$panel_title = isset($settings['panel_title']) ? $settings['panel_title'] : 'Lodges & Camps';
$legend_lodges = isset($settings['legend_lodges']) ? $settings['legend_lodges'] : 'Lodges & Camps';
$legend_weddings = isset($settings['legend_weddings']) ? $settings['legend_weddings'] : 'Weddings & Conferences';
$show_list = isset($settings['show_list']) ? $settings['show_list'] : true;
?>

<div class="dhr-lodges-camps-map-container" style="height: <?php echo esc_attr($atts['height']); ?>;">
    <?php if ($show_list && !empty($hotels)): ?>
    <div class="dhr-lodges-camps-panel">
        <ul>
            <?php foreach ($hotels as $index => $hotel): ?>
                <li data-hotel-id="<?php echo esc_attr($hotel->id); ?>">
                   <?php echo esc_html($hotel->name); ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="dhr-lodges-legend">
            <ul>
                <li>
                   <?php echo esc_html($legend_lodges); ?>
                </li>
                <li>
                   <?php echo esc_html($legend_weddings); ?>
                </li>
            </ul>
            
        </div>
    </div>
    <?php endif; ?>
    <div class="dhr-lodges-camps-map-wrapper">
        <div id="dhr-lodges-camps-map" class="dhr-lodges-camps-map"></div>
    </div>
</div>

<script>
(function() {
    function initLodgesCampsMap() {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            setTimeout(initLodgesCampsMap, 100);
            return;
        }
        
        var mapElement = document.getElementById('dhr-lodges-camps-map');
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
            
            if (hotels.length > 1) {
                map.fitBounds(bounds);
            }
            
            hotels.forEach(function(hotel, index) {
                // Alternate between lodges (blue) and weddings (orange)
                var isLodge = index % 2 === 0;
                var markerClassName = isLodge ? 'dhr-lodge-marker' : 'dhr-wedding-marker';
                var hotelLocation = { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) };
                
                var marker = new CustomDivMarker(
                    hotelLocation,
                    map,
                    hotel.name,
                    markerClassName
                );
                
                var infoContent = '<div class="dhr-info-window">' +
                    '<h4 class="dhr-info-window-title">' + hotel.name + '</h4>' +
                    '<p class="dhr-info-window-content mb-0">' + hotel.city + ', ' + hotel.province + '</p>' +
                    '</div>';
                
                var infoWindow = new google.maps.InfoWindow({
                    content: infoContent
                });
                
                // Store info window on marker
                marker.infoWindow = infoWindow;
                
                markers.push({ marker: marker, hotel: hotel, index: index });
                infoWindows.push(infoWindow);
            });
            
            // Handle list item clicks
            var lodgeItems = document.querySelectorAll('.dhr-lodges-camps-panel > ul > li[data-hotel-id]');
            lodgeItems.forEach(function(item) {
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
            
            // Handle legend item clicks
            var legendItems = document.querySelectorAll('.dhr-lodges-legend ul li');
            legendItems.forEach(function(item, index) {
                item.style.cursor = 'pointer';
                item.addEventListener('click', function() {
                    // Close all info windows first
                    infoWindows.forEach(function(iw) { iw.close(); });
                    
                    // Find markers matching the legend type (first item = lodges, second = weddings)
                    var isLodgeType = index === 0;
                    var matchingMarkers = markers.filter(function(m) {
                        var markerIndex = m.index;
                        var markerIsLodge = markerIndex % 2 === 0;
                        return isLodgeType ? markerIsLodge : !markerIsLodge;
                    });
                    
                    if (matchingMarkers.length > 0) {
                        // Open first matching marker's info window
                        var firstMarker = matchingMarkers[0];
                        var infoWindow = infoWindows[firstMarker.index];
                        var position = firstMarker.marker.getPosition();
                        infoWindow.setPosition(position);
                        infoWindow.open(map);
                        map.setCenter(position);
                        map.setZoom(12);
                    }
                });
            });
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLodgesCampsMap);
    } else {
        initLodgesCampsMap();
    }
})();
</script>

