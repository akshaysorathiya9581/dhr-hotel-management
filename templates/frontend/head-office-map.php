<?php
/**
 * Head Office Map Template (Map 2)
 */

if (!defined('ABSPATH')) {
    exit;
}

$hotels_js = array();
if (!empty($hotels)) {
    foreach ($hotels as $h) {
        $hotels_js[] = array(
            'id' => (int) $h->id, 'name' => isset($h->name) ? wp_unslash((string) $h->name) : '', 'description' => isset($h->description) ? wp_unslash((string) $h->description) : '',
            'address' => isset($h->address) ? $h->address : '', 'city' => isset($h->city) ? $h->city : '', 'province' => isset($h->province) ? $h->province : '',
            'country' => isset($h->country) ? $h->country : '', 'latitude' => isset($h->latitude) ? floatval($h->latitude) : 0, 'longitude' => isset($h->longitude) ? floatval($h->longitude) : 0,
            'phone' => isset($h->phone) ? $h->phone : '', 'email' => isset($h->email) ? $h->email : '', 'website' => isset($h->website) ? $h->website : '',
            'image_url' => isset($h->image_url) ? $h->image_url : '', 'google_maps_url' => isset($h->google_maps_url) ? $h->google_maps_url : '', 'status' => isset($h->status) ? $h->status : 'active',
            'hotel_code' => isset($h->hotel_code) ? $h->hotel_code : ''
        );
    }
}
// Only activate a default hotel marker if explicitly selected in Map Settings admin panel.
$default_hotel_code = '';
if (isset($settings['default_hotel_code']) && $settings['default_hotel_code'] !== '') {
    $default_hotel_code = trim((string) $settings['default_hotel_code']);
}

$title = isset($settings['title']) ? $settings['title'] : 'Head Office';
$address = isset($settings['address']) ? $settings['address'] : '310 Main Road, Bryanston 2021, Gauteng, South Africa';
$phone1 = isset($settings['phone1']) ? $settings['phone1'] : '+27 (0) 11 267 8300';
$phone2 = isset($settings['phone2']) ? $settings['phone2'] : '+27 861 010 347';
$po_box = isset($settings['po_box']) ? $settings['po_box'] : '86027, Sandton 2146, Gauteng, South Africa';
$email = isset($settings['email']) ? $settings['email'] : 'info@dreamresorts.co.za';
$latitude = isset($settings['latitude']) ? $settings['latitude'] : '';
$longitude = isset($settings['longitude']) ? $settings['longitude'] : '';
$google_maps_url = isset($settings['google_maps_url']) ? $settings['google_maps_url'] : '';
?>

<div class="all-maps head-office-map-container" style="height: <?php echo esc_attr($atts['height']); ?>;">
    <div class="head-office-map-area">
        <div id="head-office-map" class="head-office-map" data-hotels="<?php echo esc_attr(wp_json_encode($hotels_js)); ?>" data-default-hotel-code="<?php echo esc_attr($default_hotel_code); ?>"></div>
    </div>
</div>

<div id="head-office-info-window-template" style="display: none;">
    <div class="info-window">
        <div class="info-window-image">
            <img src="{image_url}" alt="{name}"
                onerror="this.onerror=null; this.src='{pluginUrl}assets/images/default-hotel.jpg';">
        </div>
        <div class="info-window-content">
            <h3 class="info-window-title">{name}</h3>
            <p class="info-window-location">{city} | {province}</p>
            <div class="info-window-actions">
                <a href="{google_maps_url}" target="_blank" class="btn-info">
                    <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10.4544 1.95996C5.77085 1.95996 1.96021 5.77061 1.96021 10.4542C1.96021 15.1377 5.77085 18.9484 10.4544 18.9484C15.138 18.9484 18.9486 15.1377 18.9486 10.4542C18.9486 5.77061 15.138 1.95996 10.4544 1.95996ZM10.4544 3.26676C14.431 3.26676 17.6418 6.47761 17.6418 10.4542C17.6418 14.4307 14.431 17.6416 10.4544 17.6416C6.47785 17.6416 3.26701 14.4307 3.26701 10.4542C3.26701 6.47761 6.47785 3.26676 10.4544 3.26676ZM9.80101 6.53376V7.84056H11.1078V6.53376H9.80101ZM9.80101 9.14736V14.3746H11.1078V9.14736H9.80101Z"
                            fill="#0B5991" />
                    </svg>
                </a>
                <a href="tel:{phone}" class="btn-book">
                    {book_now_text}
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    var dhrHeadOfficeMapSettings = { default_hotel_code: '<?php echo esc_js($default_hotel_code); ?>' };
    var dhrHeadOfficeMapHotels = <?php echo wp_json_encode($hotels_js); ?>;
</script>
<script>
    (function () {
        'use strict';

        var map;
        var mapEl;
        var markers = [];
        var infoWindows = [];
        var pulseOverlays = {}; // Store pulse overlay elements for each marker
        var activeMarker = null; // Track currently active marker
        var hoveredMarker = null; // Track currently hovered marker
        var PulseOverlay; // Will be defined after Google Maps loads
        var fitMapBounds;
        // Shared map styles: geometry colors + all labels hidden
        var mapStyles = [
            { featureType: 'all', elementType: 'geometry', stylers: [{ color: '#f5f5f5' }] },
            { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#A0B6CB' }] },
            { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
            { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#9e9e9e' }] },
            { featureType: 'all', elementType: 'labels', stylers: [{ visibility: 'off' }] },
            { featureType: 'road', elementType: 'labels', stylers: [{ visibility: 'off' }] },
            { featureType: 'administrative', elementType: 'labels', stylers: [{ visibility: 'off' }] },
            { featureType: 'poi', elementType: 'labels', stylers: [{ visibility: 'off' }] },
            { featureType: 'transit', elementType: 'labels', stylers: [{ visibility: 'off' }] },
            { featureType: 'landscape', elementType: 'labels', stylers: [{ visibility: 'off' }] },
            { featureType: 'water', elementType: 'labels', stylers: [{ visibility: 'off' }] }
        ];

        // Detect if device is mobile
        function isMobileDevice() {
            return window.innerWidth <= 991;
        }

        // Detect device type for responsive adjustments
        function getDeviceType() {
            var width = window.innerWidth;
            if (width < 768) {
                return 'mobile';
            } else if (width < 991) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }

        // Function to define PulseOverlay class (called after Google Maps loads)
        function definePulseOverlay() {
            // Custom Overlay for Pulse Effect
            PulseOverlay = function (position, map, isActive) {
                this.position = position;
                this.map = map;
                this.isActive = isActive;
                this.div = null;
                this.setMap(map);
            };

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
                    // Active marker structure - EXACT match with lighter shades
                    // Outer circle (pulsing)
                    var outerCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    outerCircle.setAttribute('cx', '28.314');
                    outerCircle.setAttribute('cy', '28.314');
                    outerCircle.setAttribute('r', '28.314');
                    outerCircle.setAttribute('fill', '#B8E3FF');
                    outerCircle.setAttribute('opacity', '0.1');
                    outerCircle.classList.add('pulse-outer-circle');
                    svg.appendChild(outerCircle);

                    // Middle circle (pulsing)
                    var middleCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    middleCircle.setAttribute('cx', '27.8784');
                    middleCircle.setAttribute('cy', '28.7496');
                    middleCircle.setAttribute('r', '20.9088');
                    middleCircle.setAttribute('fill', '#7BC9FF');
                    middleCircle.setAttribute('opacity', '0.3');
                    middleCircle.classList.add('pulse-middle-circle');
                    svg.appendChild(middleCircle);
                } else {
                    // Normal marker structure - EXACT match with lighter shades
                    // Outer circle (pulsing)
                    var outerCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    outerCircle.setAttribute('cx', '13.068');
                    outerCircle.setAttribute('cy', '13.068');
                    outerCircle.setAttribute('r', '13.068');
                    outerCircle.setAttribute('fill', '#B8E3FF');
                    outerCircle.setAttribute('opacity', '0.6');
                    outerCircle.classList.add('pulse-outer-circle');
                    svg.appendChild(outerCircle);

                    // Middle circle (pulsing)
                    var middleCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    middleCircle.setAttribute('cx', '13.068');
                    middleCircle.setAttribute('cy', '13.0681');
                    middleCircle.setAttribute('r', '6.0984');
                    middleCircle.setAttribute('fill', '#7BC9FF');
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

                    // Match the anchor points of the marker icons exactly
                    var anchorOffsetX = this.isActive ? 27.8784 : 13.068;
                    var anchorOffsetY = this.isActive ? 28.7498 : 13.0681;
                    this.div.style.left = (position.x - anchorOffsetX) + 'px';
                    this.div.style.top = (position.y - anchorOffsetY) + 'px';
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
        }

        function initHeadOfficeMap() {
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                console.error('Google Maps API not loaded');
                return;
            }

            // Define PulseOverlay class now that Google Maps is loaded
            definePulseOverlay();

            // Check if the map element exists
            mapEl = document.getElementById('head-office-map');
            if (!mapEl) {
                // Map element doesn't exist, this script is not needed
                return;
            }

            var hotels = [];
            try {
                var dataHotels = mapEl.getAttribute('data-hotels');
                if (dataHotels) {
                    var parsed = JSON.parse(dataHotels);
                    if (Array.isArray(parsed) && parsed.length > 0) hotels = parsed;
                }
                if (hotels.length === 0 && typeof dhrHeadOfficeMapHotels !== 'undefined' && dhrHeadOfficeMapHotels.length) hotels = dhrHeadOfficeMapHotels;
                if (hotels.length === 0 && dhrHotelsData && dhrHotelsData.hotels && dhrHotelsData.hotels.length) hotels = dhrHotelsData.hotels;
            } catch (e) {
                if (typeof dhrHeadOfficeMapHotels !== 'undefined' && dhrHeadOfficeMapHotels.length) hotels = dhrHeadOfficeMapHotels;
                else if (dhrHotelsData && dhrHotelsData.hotels) hotels = dhrHotelsData.hotels;
            }
            if (!hotels || hotels.length === 0) {
                console.warn('No hotels data available');
                return;
            }

            // Filter to hotels with valid coordinates
            var validHotels = hotels.filter(function (hotel) {
                var lat = parseFloat(hotel.latitude);
                var lng = parseFloat(hotel.longitude);
                return isFinite(lat) && lat >= -90 && lat <= 90 && isFinite(lng) && lng >= -180 && lng <= 180;
            });

            var selectedHotel = null;
            var defaultCode = ((typeof dhrHeadOfficeMapSettings !== 'undefined' && dhrHeadOfficeMapSettings.default_hotel_code) || mapEl.getAttribute('data-default-hotel-code') || '').trim().toUpperCase();

            if (defaultCode) {
                selectedHotel = validHotels.find(function (hotel) {
                    var hotelCode = String(hotel.hotel_code || '').trim().toUpperCase();
                    return hotelCode && hotelCode === defaultCode;
                }) || null;
            }

            // Fallback to first valid hotel if no explicit selected code match is found.
            if (!selectedHotel && validHotels.length > 0) {
                selectedHotel = validHotels[0];
            }

            if (!selectedHotel) {
                console.warn('No selected hotel with valid coordinates available');
                return;
            }

            var selectedLat = parseFloat(selectedHotel.latitude);
            var selectedLng = parseFloat(selectedHotel.longitude);
            var selectedCenter = { lat: selectedLat, lng: selectedLng };

            map = new google.maps.Map(mapEl, {
                zoom: 14,
                center: selectedCenter,
                styles: mapStyles
            });

            createMarker(selectedHotel, 0);

            google.maps.event.addListenerOnce(map, 'idle', function () {
                var markerData = markers[0];
                if (markerData && markerData.marker) {
                    google.maps.event.trigger(markerData.marker, 'click');
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
                icon: normalIcon
            });

            // Create info window content
            var infoWindowContent = getInfoWindowContent(hotel);

            // Create info window
            var infoWindow = new google.maps.InfoWindow({
                content: infoWindowContent
            });

            // Add close listener to info window
            infoWindow.addListener('closeclick', function () {
                // Remove active state from all markers when info window is closed
                setAllMarkersToNormal();
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

            // Add click listener to marker
            marker.addListener('click', function () {
                // Set all markers to normal
                setAllMarkersToNormal();

                // Set this marker to active (this also sets activeMarker internally)
                setMarkerToActive(marker);

                // Close all other info windows
                infoWindows.forEach(function (iw) {
                    iw.close();
                });

                // Open this info window
                infoWindow.open(map, marker);

                // Center map with mobile offset if needed
                centerMapOnMarker(marker, infoWindow);
            });


            // Store marker and info window
            markers.push({
                marker: marker,
                infoWindow: infoWindow,
                hotelId: hotel.id
            });

            infoWindows.push(infoWindow);
        }

        function getInfoWindowContent(hotel) {
            var templateElement = document.getElementById('head-office-info-window-template');
            var template = templateElement.innerHTML;
            var bookNowText = 'Get A Quote'; // Default text
            var pluginUrl = (typeof dhrHotelsData !== 'undefined' && dhrHotelsData.pluginUrl) ? dhrHotelsData.pluginUrl : '';

            var content = template
                .replace(/{name}/g, escapeHtml(hotel.name))
                .replace(/{city}/g, escapeHtml(hotel.city))
                .replace(/{province}/g, escapeHtml(hotel.province))
                .replace(/{image_url}/g, hotel.image_url || (pluginUrl + 'assets/images/default-hotel.jpg'))
                .replace(/{pluginUrl}/g, pluginUrl)
                .replace(/{google_maps_url}/g, hotel.google_maps_url || 'https://www.google.com/maps?q=' + hotel.latitude + ',' + hotel.longitude)
                .replace(/{phone}/g, escapeHtml(hotel.phone || ''))
                .replace(/{book_now_text}/g, escapeHtml(bookNowText));

            return content;
        }

        function createNormalMarkerIcon() {
            // Create SVG for normal map marker - outer 2 circles with lighter shades
            var svg = '<svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg"><circle opacity="0.6" cx="13.068" cy="13.068" r="13.068" fill="#B8E3FF"/><circle opacity="0.3" cx="13.068" cy="13.0681" r="6.0984" fill="#7BC9FF"/><circle cx="13.068" cy="13.0681" r="6.0984" fill="#062943"/></svg>';

            return {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                scaledSize: new google.maps.Size(27, 27),
                anchor: new google.maps.Point(13.068, 13.0681)
            };
        }

        function createActiveMarkerIcon() {
            // Create SVG for active map marker (more visible) - outer 2 circles with lighter shades
            var svg = '<svg width="57" height="57" viewBox="0 0 57 57" fill="none" xmlns="http://www.w3.org/2000/svg"><circle opacity="0.1" cx="28.314" cy="28.314" r="28.314" fill="#B8E3FF"/><circle opacity="0.3" cx="27.8784" cy="28.7496" r="20.9088" fill="#7BC9FF"/><circle cx="27.8784" cy="28.7498" r="6.0984" fill="#062943"/></svg>';

            return {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                scaledSize: new google.maps.Size(57, 57),
                anchor: new google.maps.Point(27.8784, 28.7498)
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
            setTimeout(function () {
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
            hoveredMarker = null;
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
                setTimeout(function () {
                    var mapDiv = document.getElementById('head-office-map');
                    if (!mapDiv) {
                        map.panTo(position);
                        return;
                    }

                    var mapHeight = mapDiv.offsetHeight;
                    var projection = map.getProjection();
                    if (!projection) {
                        map.panTo(position);
                        return;
                    }

                    var markerPixel = projection.fromLatLngToContainerPixel(position);
                    var desiredMarkerY = mapHeight * 0.40;
                    var offsetY = markerPixel.y - desiredMarkerY;
                    var currentZoom = map.getZoom();
                    var degreesPerPixel = 360 / (256 * Math.pow(2, currentZoom));
                    var latOffset = offsetY * degreesPerPixel;

                    var adjustedPosition = new google.maps.LatLng(
                        position.lat() - latOffset,
                        position.lng()
                    );

                    map.panTo(adjustedPosition);
                }, 100);
            }
            // Desktop (> 991px): Do not center map on marker click
        }

        // Handle window resize for mobile devices
        var resizeTimeout;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function () {
                if (map && fitMapBounds && !activeMarker) {
                    fitMapBounds();
                }

                // If a marker is active and we're on mobile, recenter it
                if (isMobileDevice() && activeMarker && map) {
                    var markerData = markers.find(function (m) {
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
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                // Wait for Google Maps API to load
                if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                    initHeadOfficeMap();
                } else {
                    // Wait for Google Maps API
                    window.addEventListener('load', function () {
                        setTimeout(initHeadOfficeMap, 1000);
                    });
                }
            });
        } else {
            // DOM already loaded
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                initHeadOfficeMap();
            } else {
                // Wait for Google Maps API
                window.addEventListener('load', function () {
                    setTimeout(initHeadOfficeMap, 1000);
                });
            }
        }

    })();
</script>