<?php
/**
 * Where To Find Us Map Template (Map 9)
 * Single-hotel map with location details, image card, and contact info.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Only activate a default hotel marker if explicitly selected in Map Settings admin panel.
$default_hotel_code = '';
if (isset($settings['default_hotel_code']) && $settings['default_hotel_code'] !== '') {
    $default_hotel_code = trim((string) $settings['default_hotel_code']);
}

$hotel = null;
if (!empty($hotels) && is_array($hotels)) {
    // Try to match the "Book Your Stay" / default hotel code first (same logic as wedding-venue-map)
    if ($default_hotel_code !== '') {
        foreach ($hotels as $h) {
            $code = isset($h->hotel_code) ? trim((string) $h->hotel_code) : '';
            if ($code !== '' && strcasecmp($code, $default_hotel_code) === 0) {
                $hotel = $h;
                break;
            }
        }
    }

    // Fallback: if no code match, just use the first hotel
    if (!$hotel) {
        $hotel = reset($hotels);
    }
}

// Build JS-friendly hotel list for multi-marker map (all selected hotels on this map)
$wtfu_hotels_js = array();
if (!empty($hotels) && is_array($hotels)) {
    foreach ($hotels as $h) {
        $wtfu_hotels_js[] = array(
            'id'          => isset($h->id) ? (int) $h->id : 0,
            'name'        => isset($h->name) ? $h->name : '',
            'city'        => isset($h->city) ? $h->city : '',
            'province'    => isset($h->province) ? $h->province : '',
            'latitude'    => isset($h->latitude) ? floatval($h->latitude) : 0,
            'longitude'   => isset($h->longitude) ? floatval($h->longitude) : 0,
            'phone'       => isset($h->phone) ? $h->phone : '',
            'image_url'   => isset($h->image_url) ? $h->image_url : '',
            'logo_url'    => isset($h->logo_url) ? $h->logo_url : '',
            'google_maps_url' => isset($h->google_maps_url) ? $h->google_maps_url : '',
            'hotel_code'  => isset($h->hotel_code) ? $h->hotel_code : '',
        );
    }
}

if (!$hotel) {
    return;
}

$heading = isset($settings['main_heading']) ? $settings['main_heading'] : 'Where To Find Us';
$address_text = isset($settings['address_text']) ? $settings['address_text'] : '';
$phone_label = isset($settings['phone_label']) ? $settings['phone_label'] : '';
$phone_number = isset($settings['phone_number']) ? $settings['phone_number'] : '';
$email_address = isset($settings['email_address']) ? $settings['email_address'] : '';
$gps_coordinates = isset($settings['gps_coordinates']) ? $settings['gps_coordinates'] : '';
$enquire_text = isset($settings['enquire_text']) ? $settings['enquire_text'] : 'Enquire now';
$enquire_url = isset($settings['enquire_url']) ? $settings['enquire_url'] : '';
$logo_url = isset($settings['logo_url']) ? $settings['logo_url'] : '';
$bg_color = isset($settings['bg_color']) ? $settings['bg_color'] : '#8FA7BF';

if (empty($address_text) && !empty($hotel->address)) {
    $address_text = $hotel->address;
    if (!empty($hotel->city)) $address_text .= ', ' . $hotel->city;
    if (!empty($hotel->province)) $address_text .= ', ' . $hotel->province;
}
if (empty($phone_number) && !empty($hotel->phone)) {
    $phone_number = $hotel->phone;
}
if (empty($email_address) && !empty($hotel->email)) {
    $email_address = $hotel->email;
}

$lat = isset($hotel->latitude) ? floatval($hotel->latitude) : 0;
$lng = isset($hotel->longitude) ? floatval($hotel->longitude) : 0;
// South Africa default center (same as other maps)
$default_center_lat = -26.2;
$default_center_lng = 28.5;
$has_valid_coords = ($lat && $lng && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180);
$hotel_name = isset($hotel->name) ? $hotel->name : '';
$hotel_image = isset($hotel->image_url) ? $hotel->image_url : '';
$google_maps_url = isset($hotel->google_maps_url) ? $hotel->google_maps_url : '';
if (empty($google_maps_url) && $lat && $lng) {
    $google_maps_url = 'https://www.google.com/maps?q=' . $lat . ',' . $lng;
}
$left_bg_image = !empty($hotel_image) ? $hotel_image : DHR_HOTEL_PLUGIN_URL . 'assets/images/default-hotel.jpg';
$hotel_city = isset($hotel->city) ? $hotel->city : '';
$hotel_province = isset($hotel->province) ? $hotel->province : '';
$book_now_text = !empty($enquire_text) ? $enquire_text : 'Book Now';
?>

<div id="wtfu-info-window-template" style="display: none;">
    <div class="info-window">
        <div class="info-window-content">
            <h3 class="info-window-title">{name}</h3>
            <p class="info-window-location">{city} | {province}</p>
            <div class="info-window-actions">
                <a href="{google_maps_url}" target="_blank" class="btn-info">
                    <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10.4544 1.95996C5.77085 1.95996 1.96021 5.77061 1.96021 10.4542C1.96021 15.1377 5.77085 18.9484 10.4544 18.9484C15.138 18.9484 18.9486 15.1377 18.9486 10.4542C18.9486 5.77061 15.138 1.95996 10.4544 1.95996ZM10.4544 3.26676C14.431 3.26676 17.6418 6.47761 17.6418 10.4542C17.6418 14.4307 14.431 17.6416 10.4544 17.6416C6.47785 17.6416 3.26701 14.4307 3.26701 10.4542C3.26701 6.47761 6.47785 3.26676 10.4544 3.26676ZM9.80101 6.53376V7.84056H11.1078V6.53376H9.80101ZM9.80101 9.14736V14.3746H11.1078V9.14736H9.80101Z" fill="#0B5991"/>
                    </svg>
                </a>
                <a href="tel:{phone}" class="btn-book">{book_now_text}</a>
            </div>
        </div>
    </div>
</div>
<script>
    var dhrWtfuHotel = <?php echo wp_json_encode(array(
        'name' => $hotel_name,
        'city' => $hotel_city,
        'province' => $hotel_province,
        'image_url' => $hotel_image,
        'google_maps_url' => $google_maps_url,
        'phone' => $phone_number,
        'book_now_text' => $book_now_text
    )); ?>;
    var dhrWtfuPluginUrl = <?php echo wp_json_encode(DHR_HOTEL_PLUGIN_URL); ?>;
    var dhrWtfuMapSettings = {
        default_hotel_code: '<?php echo isset($default_hotel_code) ? esc_js($default_hotel_code) : ''; ?>'
    };
    var dhrWtfuMapHotels = <?php echo wp_json_encode($wtfu_hotels_js); ?>;
</script>

<div class="all-maps wtfu-map-container" style="height: <?php echo esc_attr($atts['height']); ?>;">
    <div id="wtfu-map" class="wtfu-map" data-lat="<?php echo esc_attr($lat); ?>" data-lng="<?php echo esc_attr($lng); ?>" data-name="<?php echo esc_attr($hotel_name); ?>" data-default-lat="<?php echo esc_attr($default_center_lat); ?>" data-default-lng="<?php echo esc_attr($default_center_lng); ?>" data-has-coords="<?php echo $has_valid_coords ? '1' : '0'; ?>"></div>
    <div class="wtfu-info-content">
        <div id="wtfu-info-content-left" class="wtfu-info-content__left" style="background-image: url('<?php echo esc_url($left_bg_image); ?>');">
            <!-- <?php //if (!empty($logo_url)): ?>
                <div class="wtfu-info__logo">
                    <img src="<?php //echo esc_url($logo_url); ?>" width="227" height="164" alt="<?php //echo esc_attr($hotel_name); ?> Logo">
                </div>
            <?php //endif; ?> -->
            <?php if (!empty($hotels) && is_array($hotels)): ?>
                <div class="wtfu-info__logo">
                    <?php foreach ($hotels as $h): ?>
                        <?php
                        $logo = '';
                        if (isset($h->logo_url) && !empty($h->logo_url)) {
                            $logo = $h->logo_url;
                        } elseif (isset($h->image_url) && !empty($h->image_url)) {
                            $logo = $h->image_url;
                        }
                        if (empty($logo)) {
                            continue;
                        }
                        $hid = isset($h->id) ? (int) $h->id : 0;
                        if ($hid <= 0) {
                            continue;
                        }
                        ?>
                        <div class="wtfu-hotel-logo-item"
                                data-hotel-id="<?php echo esc_attr($hid); ?>"
                                aria-label="<?php echo esc_attr($h->name); ?>">
                            <img src="<?php echo esc_url($logo); ?>"
                                 alt="<?php echo esc_attr($h->name); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($enquire_text)): ?>
                <a class="wtfu-info__enq-btn" href="<?php echo !empty($enquire_url) ? esc_url($enquire_url) : '#'; ?>" <?php echo !empty($enquire_url) ? 'target="_blank"' : ''; ?>>
                    <?php echo esc_html($enquire_text); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="wtfu-info-content__right">
            <h2 class="map-title"><?php echo esc_html($heading); ?></h2>
            <?php if (!empty($address_text)): ?>
                <p class="map-description"><?php echo esc_html($address_text); ?></p>
            <?php endif; ?>

            <?php if (!empty($phone_number)): ?>
                <p class="map-phone">
                    <a href="tel:<?php echo esc_attr($phone_number); ?>"><?php echo esc_html($phone_number); ?></a>
                    <?php if (!empty($phone_label)): ?>
                        <span><?php echo esc_html($phone_label); ?></span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($email_address)): ?>
                <p class="map-description">
                    <a href="mailto:<?php echo esc_attr($email_address); ?>"><?php echo esc_html($email_address); ?></a>
                </p>
            <?php endif; ?>

            <?php if (!empty($gps_coordinates)): ?>
                <p class="map-description">GPS: <?php echo esc_html($gps_coordinates); ?></p>
            <?php endif; ?>

            <?php if (!empty($google_maps_url)): ?>
                <div class="map-btn">
                    <a class="map-btn__link" href="<?php echo esc_url($google_maps_url); ?>" target="_blank">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_4637_2271)">
                                <path d="M23.4059 10.1507L23.2848 9.63696H12.1184V14.363H18.7902C18.0975 17.6523 14.8832 19.3837 12.2577 19.3837C10.3473 19.3837 8.33357 18.5802 7.00071 17.2886C6.2975 16.5962 5.73774 15.772 5.3535 14.863C4.96925 13.9541 4.76805 12.9782 4.76143 11.9914C4.76143 10.0007 5.65607 8.00946 6.95786 6.69964C8.25964 5.38982 10.2257 4.65696 12.1805 4.65696C14.4193 4.65696 16.0237 5.84571 16.6237 6.38786L19.9821 3.04714C18.997 2.18143 16.2905 0 12.0723 0C8.81786 0 5.69732 1.24661 3.41625 3.52018C1.16518 5.75893 0 8.99625 0 12C0 15.0038 1.1025 18.0793 3.28393 20.3357C5.61482 22.7421 8.91589 24 12.315 24C15.4077 24 18.3391 22.7882 20.4284 20.5896C22.4823 18.4254 23.5446 15.4307 23.5446 12.2914C23.5446 10.9698 23.4118 10.185 23.4059 10.1507Z" fill="currentColor"></path>
                            </g>
                            <defs>
                                <clipPath id="clip0_4637_2271">
                                    <rect width="24" height="24" fill="white"></rect>
                                </clipPath>
                            </defs>
                        </svg>
                        Google Maps
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var map;
    var markers = [];
    var infoWindows = [];
    var pulseOverlays = {};
    var activeMarker = null;
    var PulseOverlay;

    function createNormalMarkerIcon() {
        var svg = '<svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg"><circle opacity="0.1" cx="13.068" cy="13.068" r="13.068" fill="#44B9F8"/><circle opacity="0.3" cx="13.068" cy="13.0681" r="6.0984" fill="#44B9F8"/><circle cx="13.068" cy="13.0681" r="6.0984" fill="#062943"/></svg>';
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(27, 27),
            anchor: new google.maps.Point(13.068, 13.0681)
        };
    }

    function createActiveMarkerIcon() {
        var svg = '<svg width="57" height="57" viewBox="0 0 57 57" fill="none" xmlns="http://www.w3.org/2000/svg"><circle opacity="0.1" cx="28.314" cy="28.314" r="28.314" fill="#44B9F8"/><circle opacity="0.3" cx="27.8784" cy="28.7496" r="20.9088" fill="#44B9F8"/><circle cx="27.8784" cy="28.7498" r="6.0984" fill="#062943"/></svg>';
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(57, 57),
            anchor: new google.maps.Point(27.8784, 28.7498)
        };
    }

    function definePulseOverlay() {
        PulseOverlay = function (position, mapInstance, isActive) {
            this.position = position;
            this.map = mapInstance;
            this.isActive = isActive;
            this.div = null;
            this.setMap(mapInstance);
        };
        PulseOverlay.prototype = new google.maps.OverlayView();
        PulseOverlay.prototype.onAdd = function () {
            var div = document.createElement('div');
            div.className = 'dhr-marker-pulse';
            if (this.isActive) div.classList.add('dhr-marker-pulse-active');
            else div.classList.add('dhr-marker-pulse-hover');
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
                var oc = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                oc.setAttribute('cx', '28.314'); oc.setAttribute('cy', '28.314'); oc.setAttribute('r', '28.314');
                oc.setAttribute('fill', '#44B9F8'); oc.setAttribute('opacity', '0.1'); oc.classList.add('pulse-outer-circle');
                var mc = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                mc.setAttribute('cx', '27.8784'); mc.setAttribute('cy', '28.7496'); mc.setAttribute('r', '20.9088');
                mc.setAttribute('fill', '#44B9F8'); mc.setAttribute('opacity', '0.3'); mc.classList.add('pulse-middle-circle');
                svg.appendChild(oc); svg.appendChild(mc);
            } else {
                var oc = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                oc.setAttribute('cx', '13.068'); oc.setAttribute('cy', '13.068'); oc.setAttribute('r', '13.068');
                oc.setAttribute('fill', '#44B9F8'); oc.setAttribute('opacity', '0.1'); oc.classList.add('pulse-outer-circle');
                var mc = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                mc.setAttribute('cx', '13.068'); mc.setAttribute('cy', '13.0681'); mc.setAttribute('r', '6.0984');
                mc.setAttribute('fill', '#44B9F8'); mc.setAttribute('opacity', '0.3'); mc.classList.add('pulse-middle-circle');
                svg.appendChild(oc); svg.appendChild(mc);
            }
            div.appendChild(svg);
            this.div = div;
            this.getPanes().overlayLayer.appendChild(div);
            this.draw();
        };
        PulseOverlay.prototype.draw = function () {
            var proj = this.getProjection();
            if (!proj || !this.div) return;
            var pos = proj.fromLatLngToDivPixel(this.position);
            var size = this.isActive ? 57 : 27;
            var ax = this.isActive ? 27.8784 : 13.068, ay = this.isActive ? 28.7498 : 13.0681;
            this.div.style.left = (pos.x - ax) + 'px';
            this.div.style.top = (pos.y - ay) + 'px';
            this.div.style.width = size + 'px';
            this.div.style.height = size + 'px';
            this.div.style.margin = '0';
            this.div.style.padding = '0';
            this.div.style.border = 'none';
            this.div.style.outline = 'none';
            if (this.isActive && !this.div.classList.contains('dhr-marker-pulse-active')) this.div.classList.add('dhr-marker-pulse-active');
        };
        PulseOverlay.prototype.onRemove = function () {
            if (this.div && this.div.parentNode) this.div.parentNode.removeChild(this.div);
            this.div = null;
        };
    }

    function startPulse(marker, isActive) {
        var id = marker.getPosition().toString();
        if (pulseOverlays[id]) {
            pulseOverlays[id].setMap(null);
            delete pulseOverlays[id];
        }
        var overlay = new PulseOverlay(marker.getPosition(), map, isActive);
        pulseOverlays[id] = overlay;
        setTimeout(function () {
            if (overlay && overlay.div) {
                overlay.draw();
            }
        }, 100);
    }

    function stopPulse(marker) {
        var id = marker.getPosition().toString();
        if (pulseOverlays[id]) {
            pulseOverlays[id].setMap(null);
            delete pulseOverlays[id];
        }
    }

    function setAllMarkersToNormal() {
        var normalIcon = createNormalMarkerIcon();
        markers.forEach(function (markerData) {
            stopPulse(markerData.marker);
            markerData.marker.setIcon(normalIcon);
        });
        activeMarker = null;
    }

    function setMarkerToActive(marker) {
        stopPulse(marker);
        marker.setIcon(createActiveMarkerIcon());
        startPulse(marker, true);
        activeMarker = marker;
    }

    function escapeHtml(text) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return (text || '').replace(/[&<>"']/g, function (ch) { return map[ch]; });
    }

    function getInfoWindowContent(hotel) {
        var templateEl = document.getElementById('wtfu-info-window-template');
        if (!templateEl) return '<div class="info-window-content"><h3 class="info-window-title">' + escapeHtml(hotel.name) + '</h3></div>';
        var template = templateEl.innerHTML;
        var pluginUrl = (typeof dhrWtfuPluginUrl !== 'undefined') ? dhrWtfuPluginUrl : '';
        var imgUrl = hotel.image_url || (pluginUrl + 'assets/images/default-hotel.jpg');
        var gmUrl = hotel.google_maps_url || ('https://www.google.com/maps?q=' + (mapEl.getAttribute('data-lat') || '') + ',' + (mapEl.getAttribute('data-lng') || ''));
        return template
            .replace(/{name}/g, escapeHtml(hotel.name))
            .replace(/{city}/g, escapeHtml(hotel.city || ''))
            .replace(/{province}/g, escapeHtml(hotel.province || ''))
            .replace(/{image_url}/g, imgUrl)
            .replace(/{plugin_url}/g, pluginUrl)
            .replace(/{google_maps_url}/g, gmUrl)
            .replace(/{phone}/g, escapeHtml(hotel.phone || ''))
            .replace(/{book_now_text}/g, escapeHtml(hotel.book_now_text || 'Book Now'));
    }

    function updateLeftPanelForHotel(hotel) {
        var leftEl = document.getElementById('wtfu-info-content-left');
        if (!leftEl || !hotel) return;
        var pluginUrl = (typeof dhrWtfuPluginUrl !== 'undefined') ? dhrWtfuPluginUrl : '';
        var imgUrl = (hotel.image_url && hotel.image_url.trim()) ? hotel.image_url : (pluginUrl + 'assets/images/default-hotel.jpg');
        leftEl.style.backgroundImage = 'url("' + String(imgUrl).replace(/"/g, '\\"') + '")';
    }

    var mapEl;

    function initWhereToFindUsMap() {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') return;
        mapEl = document.getElementById('wtfu-map');
        if (!mapEl) return;

        var defaultLat = parseFloat(mapEl.getAttribute('data-default-lat')) || -26.2;
        var defaultLng = parseFloat(mapEl.getAttribute('data-default-lng')) || 28.5;

        // Prefer full hotel list (all checked hotels on this map). Fallback to single marker if needed.
        var hotels = [];
        try {
            if (Array.isArray(dhrWtfuMapHotels) && dhrWtfuMapHotels.length) {
                hotels = dhrWtfuMapHotels;
            }
        } catch (e) {
            hotels = [];
        }

        var hasMultipleHotels = hotels && hotels.length > 0;

        // If no hotel list is available, keep legacy single-marker behaviour.
        if (!hasMultipleHotels) {
            var lat = parseFloat(mapEl.getAttribute('data-lat')) || 0;
            var lng = parseFloat(mapEl.getAttribute('data-lng')) || 0;
            var hasCoords = mapEl.getAttribute('data-has-coords') === '1';
            var name = mapEl.getAttribute('data-name') || '';

            var southAfricaCenter = { lat: defaultLat, lng: defaultLng };
            var initialCenter = hasCoords ? { lat: lat, lng: lng } : southAfricaCenter;
            var initialZoom = hasCoords ? 14 : 5;

            definePulseOverlay();

            map = new google.maps.Map(mapEl, {
                zoom: initialZoom,
                center: initialCenter,
                minZoom: 2,
                maxZoom: 18,
                disableDefaultUI: false,
                zoomControl: true,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
                styles: [
                    { featureType: 'all', elementType: 'geometry', stylers: [{ color: '#f5f5f5' }] },
                    { featureType: 'water', elemaddListenerOnceentType: 'geometry', stylers: [{ color: '#A0B6CB' }] },
                    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
                    { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#9e9e9e' }] }
                ]
            });

            if (hasCoords) {
                var position = { lat: lat, lng: lng };
                var singleMarker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: name,
                    icon: createActiveMarkerIcon()
                });
                startPulse(singleMarker, true);
                var hotel = (typeof dhrWtfuHotel !== 'undefined') ? dhrWtfuHotel : { name: name, city: '', province: '', image_url: '', google_maps_url: '', phone: '', book_now_text: 'Book Now' };
                var infoWindowContent = getInfoWindowContent(hotel);
                var infoWindow = new google.maps.InfoWindow({ content: infoWindowContent });
                singleMarker.addListener('click', function () { infoWindow.open(map, singleMarker); });
                google.maps.event.addListenerOnce(map, 'idle', function () {
                    infoWindow.open(map, singleMarker);
                });
            }

            return;
        }

        // Multi-marker behaviour (similar to wedding-venue-map)
        function isValidLat(val) {
            var n = parseFloat(val);
            return isFinite(n) && n >= -90 && n <= 90;
        }

        function isValidLng(val) {
            var n = parseFloat(val);
            return isFinite(n) && n >= -180 && n <= 180;
        }

        var validHotels = hotels.filter(function (hotel) {
            return isValidLat(hotel.latitude) && isValidLng(hotel.longitude);
        });

        var southAfricaCenterMulti = { lat: defaultLat, lng: defaultLng };
        var southAfricaZoom = 4;

        definePulseOverlay();

        map = new google.maps.Map(mapEl, {
            zoom: southAfricaZoom,
            center: southAfricaCenterMulti,
            minZoom: 2,
            maxZoom: 18,
            disableDefaultUI: false,
            zoomControl: true,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
            styles: [
                { featureType: 'all', elementType: 'geometry', stylers: [{ color: '#f5f5f5' }] },
                { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#A0B6CB' }] },
                { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
                { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#9e9e9e' }] }
            ]
        });

        var bounds = new google.maps.LatLngBounds();
        validHotels.forEach(function (hotel) {
            var latLng = new google.maps.LatLng(parseFloat(hotel.latitude), parseFloat(hotel.longitude));
            bounds.extend(latLng);
        });

        // Create markers and info windows
        validHotels.forEach(function (hotel, index) {
            var position = {
                lat: parseFloat(hotel.latitude),
                lng: parseFloat(hotel.longitude)
            };

            var marker = new google.maps.Marker({
                position: position,
                map: map,
                title: hotel.name,
                icon: createNormalMarkerIcon()
            });

            var infoWindowContent = getInfoWindowContent(hotel);
            var infoWindow = new google.maps.InfoWindow({ content: infoWindowContent });

            infoWindow.addListener('closeclick', function () {
                setAllMarkersToNormal();
            });

            marker.addListener('click', function () {
                setAllMarkersToNormal();
                setMarkerToActive(marker);

                infoWindows.forEach(function (iw) {
                    iw.close();
                });

                infoWindow.open(map, marker);

                // Update left panel to show active marker hotel image
                updateLeftPanelForHotel(hotel);
            });

            markers.push({
                marker: marker,
                infoWindow: infoWindow,
                hotel: hotel
            });
            infoWindows.push(infoWindow);
        });

        function activateDefaultHotelMarker() {
            var defaultCode = '';
            if (typeof dhrWtfuMapSettings !== 'undefined' && dhrWtfuMapSettings.default_hotel_code) {
                defaultCode = String(dhrWtfuMapSettings.default_hotel_code || '').trim().toUpperCase();
            }

            var markerToActivate = null;

            if (defaultCode) {
                for (var i = 0; i < validHotels.length; i++) {
                    var hCode = String(validHotels[i].hotel_code || '').trim().toUpperCase();
                    if (hCode && hCode === defaultCode) {
                        var m = markers[i];
                        if (m && m.marker) markerToActivate = m;
                        break;
                    }
                }
            }

            // If no default match, activate first hotel so one marker is always active (same as other map types)
            if (!markerToActivate && markers.length > 0 && markers[0].marker) {
                markerToActivate = markers[0];
            }

            if (markerToActivate) {
                (function (markerData) {
                    setTimeout(function () {
                        google.maps.event.trigger(markerData.marker, 'click');
                    }, 50);
                })(markerToActivate);
            }
        }

        // Device type for padding (same as property-portfolio-map)
        function getDeviceType() {
            var width = window.innerWidth;
            if (width < 768) return 'mobile';
            if (width < 991) return 'tablet';
            return 'desktop';
        }
        var deviceType = getDeviceType();

        // After map loads, fit to markers with 2% zoom-in and shift to left-bottom (same as property-portfolio-map)
        google.maps.event.addListenerOnce(map, 'idle', function () {
            if (validHotels.length > 0 && !bounds.isEmpty()) {
                var padding = deviceType === 'mobile' ? 40 : (deviceType === 'tablet' ? 60 : 80);
                var ne = bounds.getNorthEast();
                var sw = bounds.getSouthWest();
                var center = bounds.getCenter();
                var latSpan = ne.lat() - sw.lat();
                var lngSpan = ne.lng() - sw.lng();
                var expandFactor = 0.99;
                var expandedBounds = new google.maps.LatLngBounds(
                    new google.maps.LatLng(center.lat() - (latSpan * expandFactor) / 2, center.lng() - (lngSpan * expandFactor) / 2),
                    new google.maps.LatLng(center.lat() + (latSpan * expandFactor) / 2, center.lng() + (lngSpan * expandFactor) / 2)
                );
                map.fitBounds(expandedBounds, padding);
                setTimeout(function () {
                    var mapDiv = document.getElementById('wtfu-map');
                    if (mapDiv) {
                        var w = mapDiv.offsetWidth;
                        var h = mapDiv.offsetHeight;
                        // Pan so content sits toward left and bottom (opposite of right-bottom: pan right + up)
                        map.panBy(Math.round(w * 0.30), -Math.round(h * 0.14));
                    }
                    activateDefaultHotelMarker();
                    setTimeout(activateDefaultHotelMarker, 500);
                }, 400);
            } else {
                activateDefaultHotelMarker();
                setTimeout(activateDefaultHotelMarker, 500);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') initWhereToFindUsMap();
            else window.addEventListener('load', function () { setTimeout(initWhereToFindUsMap, 1000); });
        });
    } else {
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') initWhereToFindUsMap();
        else window.addEventListener('load', function () { setTimeout(initWhereToFindUsMap, 1000); });
    }
})();
</script>
