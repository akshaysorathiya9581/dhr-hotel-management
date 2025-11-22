/**
 * Frontend JavaScript for DHR Hotel Management
 */

(function ($) {
    'use strict';

    var map;
    var markers = [];
    var infoWindows = [];
    var pulseOverlays = {}; // Store pulse overlay elements for each marker
    var activeMarker = null; // Track currently active marker
    var hoveredMarker = null; // Track currently hovered marker

    // Detect if device is mobile
    function isMobileDevice() {
        return window.innerWidth <= 991;
    }

    // Custom Overlay for Pulse Effect
    function PulseOverlay(position, map, isActive) {
        this.position = position;
        this.map = map;
        this.isActive = isActive;
        this.div = null;
        this.setMap(map);
    }

    PulseOverlay.prototype = new google.maps.OverlayView();

    PulseOverlay.prototype.onAdd = function () {
        var div = document.createElement('div');
        div.className = 'dhr-marker-pulse';
        if (this.isActive) {
            div.classList.add('dhr-marker-pulse-active');
        } else {
            div.classList.add('dhr-marker-pulse-hover');
        }
        
        // Create SVG structure matching the EXACT marker design
        var size = this.isActive ? 57 : 27;
        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('width', size);
        svg.setAttribute('height', size);
        svg.setAttribute('viewBox', this.isActive ? '0 0 57 57' : '0 0 27 27');
        svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        svg.style.width = '100%';
        svg.style.height = '100%';
        svg.style.display = 'block';
        
        if (this.isActive) {
            // Active marker structure - EXACT match
            // Outer circle (pulsing)
            var outerCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            outerCircle.setAttribute('cx', '28.314');
            outerCircle.setAttribute('cy', '28.314');
            outerCircle.setAttribute('r', '28.314');
            outerCircle.setAttribute('fill', '#44B9F8');
            outerCircle.setAttribute('opacity', '0.1');
            outerCircle.classList.add('pulse-outer-circle');
            svg.appendChild(outerCircle);
            
            // Middle circle (pulsing)
            var middleCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            middleCircle.setAttribute('cx', '27.8784');
            middleCircle.setAttribute('cy', '28.7496');
            middleCircle.setAttribute('r', '20.9088');
            middleCircle.setAttribute('fill', '#44B9F8');
            middleCircle.setAttribute('opacity', '0.3');
            middleCircle.classList.add('pulse-middle-circle');
            svg.appendChild(middleCircle);
        } else {
            // Normal marker structure - EXACT match
            // Outer circle (pulsing)
            var outerCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            outerCircle.setAttribute('cx', '13.068');
            outerCircle.setAttribute('cy', '13.068');
            outerCircle.setAttribute('r', '13.068');
            outerCircle.setAttribute('fill', '#44B9F8');
            outerCircle.setAttribute('opacity', '0.1');
            outerCircle.classList.add('pulse-outer-circle');
            svg.appendChild(outerCircle);
            
            // Middle circle (pulsing)
            var middleCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            middleCircle.setAttribute('cx', '13.068');
            middleCircle.setAttribute('cy', '13.0681');
            middleCircle.setAttribute('r', '6.0984');
            middleCircle.setAttribute('fill', '#44B9F8');
            middleCircle.setAttribute('opacity', '0.3');
            middleCircle.classList.add('pulse-middle-circle');
            svg.appendChild(middleCircle);
        }
        
        div.appendChild(svg);
        this.div = div;

        var panes = this.getPanes();
        panes.overlayLayer.appendChild(div);
        
        // Force initial draw
        this.draw();
    };

    PulseOverlay.prototype.draw = function () {
        var overlayProjection = this.getProjection();
        if (!overlayProjection) {
            return;
        }

        var position = overlayProjection.fromLatLngToDivPixel(this.position);

        if (this.div) {
            var size = this.isActive ? 57 : 27;
            // Match the anchor point of the marker exactly
            // Active marker: anchor (12.5, 12.5), size 57x57
            // Normal marker: anchor (12.5, 12.5), size 27x27
            var anchorOffset = 12.5;
            this.div.style.left = (position.x - anchorOffset) + 'px';
            this.div.style.top = (position.y - anchorOffset) + 'px';
            this.div.style.width = size + 'px';
            this.div.style.height = size + 'px';
            this.div.style.margin = '0';
            this.div.style.padding = '0';
            this.div.style.border = 'none';
            this.div.style.outline = 'none';
            
            // Ensure the pulse animation continues
            if (this.isActive && !this.div.classList.contains('dhr-marker-pulse-active')) {
                this.div.classList.add('dhr-marker-pulse-active');
            }
        }
    };

    PulseOverlay.prototype.onRemove = function () {
        if (this.div && this.div.parentNode) {
            this.div.parentNode.removeChild(this.div);
            this.div = null;
        }
    };

    function initMap() {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            console.error('Google Maps API not loaded');
            return;
        }

        if (!dhrHotelsData || !dhrHotelsData.hotels || dhrHotelsData.hotels.length === 0) {
            console.warn('No hotels data available');
            return;
        }

        var hotels = dhrHotelsData.hotels;

        // Calculate center of all hotels
        var bounds = new google.maps.LatLngBounds();
        var centerLat = 0;
        var centerLng = 0;

        hotels.forEach(function (hotel) {
            centerLat += parseFloat(hotel.latitude);
            centerLng += parseFloat(hotel.longitude);
            bounds.extend(new google.maps.LatLng(
                parseFloat(hotel.latitude),
                parseFloat(hotel.longitude)
            ));
        });

        centerLat = centerLat / hotels.length;
        centerLng = centerLng / hotels.length;

        // Initialize map
        map = new google.maps.Map(document.getElementById('dhr-hotel-map'), {
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
                    stylers: [{ color: '#C1C0BB' }]
                },
                {
                    featureType: 'road',
                    elementType: 'labels.text.fill',
                    stylers: [{ color: '#c9c9c9' }]
                },
            ]
        });

        // Fit bounds to show all hotels
        if (hotels.length > 1) {
            map.fitBounds(bounds);
        }

        // Create markers for each hotel
        hotels.forEach(function (hotel, index) {
            createMarker(hotel, index);
        });

        // Add click handlers to hotel items
        $('.dhr-hotel-item').on('click', function () {
            var hotelId = $(this).data('hotel-id');
            var marker = markers.find(function (m) {
                return m.hotelId == hotelId;
            });

            if (marker) {
                // Set all markers to normal
                setAllMarkersToNormal();

                // Set this marker to active
                setMarkerToActive(marker.marker);
                activeMarker = marker.marker;

                // Close all info windows
                infoWindows.forEach(function (iw) {
                    iw.close();
                });

                // Open info window for clicked hotel
                marker.infoWindow.open(map, marker.marker);
                
                // Center map with mobile offset if needed
                centerMapOnMarker(marker.marker, marker.infoWindow);
            }
        });
    }

    function createMarker(hotel, index) {
        var position = {
            lat: parseFloat(hotel.latitude),
            lng: parseFloat(hotel.longitude)
        };

        // Create normal marker icon
        var normalIcon = createNormalMarkerIcon();

        // Create marker
        var marker = new google.maps.Marker({
            position: position,
            map: map,
            title: hotel.name,
            icon: normalIcon,
            animation: index === 0 ? google.maps.Animation.DROP : null
        });

        // Create info window content
        var infoWindowContent = getInfoWindowContent(hotel);

        // Create info window
        var infoWindow = new google.maps.InfoWindow({
            content: infoWindowContent
        });

        // Add click listener to marker
        marker.addListener('click', function () {
            // Set all markers to normal
            setAllMarkersToNormal();

            // Set this marker to active
            setMarkerToActive(marker);
            activeMarker = marker;

            // Close all other info windows
            infoWindows.forEach(function (iw) {
                iw.close();
            });

            // Open this info window
            infoWindow.open(map, marker);

            // Center map with mobile offset if needed
            centerMapOnMarker(marker, infoWindow);

            // Highlight hotel item in sidebar
            $('.dhr-hotel-item').removeClass('active');
            $('.dhr-hotel-item[data-hotel-id="' + hotel.id + '"]').addClass('active');
        });

        // Add hover listeners for pulse effect
        marker.addListener('mouseover', function () {
            hoveredMarker = marker;
            // Only start pulse if not already active
            if (activeMarker !== marker) {
                startPulse(marker, false);
            }
        });

        marker.addListener('mouseout', function () {
            hoveredMarker = null;
            // Only stop pulse if not active
            if (activeMarker !== marker) {
                stopPulse(marker);
            }
        });

        // Store marker and info window
        markers.push({
            marker: marker,
            infoWindow: infoWindow,
            hotelId: hotel.id
        });

        infoWindows.push(infoWindow);

        // Open first hotel's info window by default
        if (index === 0) {
            setTimeout(function () {
                // Set all markers to normal first
                setAllMarkersToNormal();
                // Set first marker to active
                setMarkerToActive(marker);
                activeMarker = marker;
                infoWindow.open(map, marker);
                
                // Center map with mobile offset if needed
                centerMapOnMarker(marker, infoWindow);
            }, 500);
        }
    }

    function getInfoWindowContent(hotel) {
        var template = $('#dhr-hotel-info-window-template').html();

        var content = template
            .replace(/{name}/g, escapeHtml(hotel.name))
            .replace(/{city}/g, escapeHtml(hotel.city))
            .replace(/{province}/g, escapeHtml(hotel.province))
            .replace(/{image_url}/g, hotel.image_url || (dhrHotelsData.pluginUrl + 'assets/images/default-hotel.jpg'))
            .replace(/{pluginUrl}/g, dhrHotelsData.pluginUrl)
            .replace(/{google_maps_url}/g, hotel.google_maps_url || 'https://www.google.com/maps?q=' + hotel.latitude + ',' + hotel.longitude)
            .replace(/{phone}/g, escapeHtml(hotel.phone || ''));

        return content;
    }

    function createNormalMarkerIcon() {
        // Create SVG for normal map marker
        var svg = '<svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg"><circle opacity="0.1" cx="13.068" cy="13.068" r="13.068" fill="#44B9F8"/><circle opacity="0.3" cx="13.068" cy="13.0681" r="6.0984" fill="#44B9F8"/><circle cx="13.068" cy="13.0681" r="6.0984" fill="#062943"/></svg>';

        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(27, 27),
            anchor: new google.maps.Point(12.5, 12.5)
        };
    }

    function createActiveMarkerIcon() {
        // Create SVG for active map marker (more visible)
        var svg = '<svg width="57" height="57" viewBox="0 0 57 57" fill="none" xmlns="http://www.w3.org/2000/svg"><circle opacity="0.1" cx="28.314" cy="28.314" r="28.314" fill="#44B9F8"/><circle opacity="0.3" cx="27.8784" cy="28.7496" r="20.9088" fill="#44B9F8"/><circle cx="27.8784" cy="28.7498" r="6.0984" fill="#062943"/></svg>';

        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(57, 57),
            anchor: new google.maps.Point(12.5, 12.5)
        };
    }

    function startPulse(marker, isActive) {
        // Stop any existing pulse for this marker
        stopPulse(marker);

        var position = marker.getPosition();
        var pulseOverlay = new PulseOverlay(position, map, isActive);
        
        // Store overlay
        var markerId = marker.getPosition().toString();
        pulseOverlays[markerId] = pulseOverlay;
        
        // Ensure pulse continues by forcing a redraw after a short delay
        setTimeout(function() {
            if (pulseOverlay && pulseOverlay.div) {
                pulseOverlay.draw();
            }
        }, 100);
    }

    function stopPulse(marker) {
        var markerId = marker.getPosition().toString();
        if (pulseOverlays[markerId]) {
            pulseOverlays[markerId].setMap(null);
            delete pulseOverlays[markerId];
        }
    }

    function setAllMarkersToNormal() {
        var normalIcon = createNormalMarkerIcon();
        markers.forEach(function (markerData) {
            // Stop pulse for all markers
            stopPulse(markerData.marker);
            markerData.marker.setIcon(normalIcon);
        });
        activeMarker = null;
        hoveredMarker = null;
    }

    function setMarkerToActive(marker) {
        // Stop pulse first
        stopPulse(marker);
        
        var activeIcon = createActiveMarkerIcon();
        marker.setIcon(activeIcon);
        
        // Start pulse for active marker
        activeMarker = marker;
        startPulse(marker, true);
    }

    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return (text || '').replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    // Center map on marker with offset for mobile devices
    function centerMapOnMarker(marker, infoWindow) {
        var position = marker.getPosition();
        
        if (isMobileDevice()) {
            // On mobile, center the map with an offset to account for info window
            // The info window appears above the marker, so we need to pan the map down
            // to center the info window in the visible area
            
            // Set zoom first
            map.setZoom(15);
            
            // Wait a moment for map to settle, then adjust position
            setTimeout(function() {
                var mapDiv = document.getElementById('dhr-hotel-map');
                if (!mapDiv) {
                    map.setCenter(position);
                    return;
                }
                
                var mapHeight = mapDiv.offsetHeight;
                
                // Calculate the pixel position of the marker
                var projection = map.getProjection();
                if (!projection) {
                    map.setCenter(position);
                    return;
                }
                
                var markerPixel = projection.fromLatLngToContainerPixel(position);
                
                // We want the marker to be at about 35% from top of map
                // This will center the info window (which appears above marker) in the viewport
                var desiredMarkerY = mapHeight * 0.35;
                var offsetY = markerPixel.y - desiredMarkerY;
                
                // Convert pixel offset to lat/lng offset
                // At zoom 15, approximate conversion: 1 pixel ≈ 0.00001 degrees latitude
                var currentZoom = map.getZoom();
                var degreesPerPixel = 360 / (256 * Math.pow(2, currentZoom));
                var latOffset = offsetY * degreesPerPixel;
                
                // Pan to adjusted position
                var adjustedPosition = new google.maps.LatLng(
                    position.lat() - latOffset,
                    position.lng()
                );
                
                map.panTo(adjustedPosition);
            }, 100);
        } else {
            // On desktop, just center normally
            map.setCenter(position);
            map.setZoom(15);
        }
    }

    // Handle window resize for mobile devices
    var resizeTimeout;
    $(window).on('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            // If a marker is active and we're on mobile, recenter it
            if (isMobileDevice() && activeMarker && map) {
                var markerData = markers.find(function(m) {
                    return m.marker === activeMarker;
                });
                if (markerData && markerData.infoWindow) {
                    // Check if info window is open
                    if (markerData.infoWindow.getMap()) {
                        centerMapOnMarker(activeMarker, markerData.infoWindow);
                    }
                }
            }
        }, 250);
    });

    // Initialize map when DOM is ready
    $(document).ready(function () {
        // Wait for Google Maps API to load
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
            initMap();
        } else {
            // Wait for Google Maps API
            window.addEventListener('load', function () {
                setTimeout(initMap, 1000);
            });
        }
    });

})(jQuery);


