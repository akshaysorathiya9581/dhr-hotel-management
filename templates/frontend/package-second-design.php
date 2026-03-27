<?php
/**
 * Second Package Design Template
 * Shortcode: [dhr_packages categories="1,2,3,..." design="second_design"]
 * Slider: use shortcode as-is. Grid: wrap in <div class="package-design-grid">[dhr_packages ...]</div>
 * JS adds .bys-packages--grid when inside .package-design-grid; CSS styles grid via that nested class.
 */

if (!defined('ABSPATH')) {
    exit;
}

$plugin_url = isset($plugin_url) ? $plugin_url : DHR_HOTEL_PLUGIN_URL;
$packages = isset($packages) ? $packages : array();
$channel_id = (int) get_option('dhr_shr_channel_id', '30');
$book_now_checkin = function_exists('wp_date') ? wp_date('Y-m-d') : date('Y-m-d', current_time('timestamp'));
$book_now_checkout = function_exists('wp_date') ? wp_date('Y-m-d', current_time('timestamp') + 2 * DAY_IN_SECONDS) : date('Y-m-d', strtotime('+2 days', current_time('timestamp')));
?>

<!-- Second Package Design (slider by default; grid when inside .package-design-grid) -->
<div class="bys-packages bys-packages__second-main">
    <div class="second-packages__design swiper package-swiper">
        <div class="swiper-wrapper">
            <?php if (empty($packages)) : ?>
                <div class="swiper-slide"><p class="dhr-no-packages"><?php esc_html_e('No packages available at the moment.', 'dhr-hotel-management'); ?></p></div>
            <?php else : ?>
                <?php foreach ($packages as $item) :
                    $pkg = $item['package'];
                    $details = $item['details'];
                    $hotel = $item['hotel'];
                    $title = $details && !empty($details->name) ? $details->name : $pkg->package_code;
                    $desc = $details && !empty($details->description) ? wp_kses_post(wp_unslash((string) $details->description)) : '';
                    $short_desc = $desc ? wp_trim_words(wp_strip_all_tags($desc), 25) : '';
                    $img_url = $plugin_url . 'assets/images/package/1.png';
                    if ($details && !empty($details->images) && is_array($details->images)) {
                        $first = reset($details->images);
                        if (is_array($first) && !empty($first['fileName'])) {
                            $img_url = esc_url($first['fileName']);
                        } elseif (is_object($first) && !empty($first->fileName)) {
                            $img_url = esc_url($first->fileName);
                        }
                    }
                    $hotel_name = $hotel && !empty($hotel->name) ? $hotel->name : $pkg->hotel_code;
                    $location_line = $hotel ? trim($hotel->city . ($hotel->province ? ', ' . $hotel->province : '') . ($hotel->country ? ', ' . $hotel->country : '')) : '';
                    $category_label = !empty($pkg->category_title) ? wp_unslash((string) $pkg->category_title) : __('Package Experience', 'dhr-hotel-management');
                    $booking_url = !empty($pkg->hotel_code) ? add_query_arg(array('hotel_code' => $pkg->hotel_code, 'channel_id' => $channel_id), home_url('/')) : '#';
                    $included_list = array();
                    if ($details && !empty($details->description)) {
                        $text = wp_strip_all_tags(wp_unslash((string) $details->description));
                        $lines = preg_split('/\n|\r\n?|\.\s+/', $text, 5, PREG_SPLIT_NO_EMPTY);
                        $included_list = array_slice(array_filter(array_map('trim', $lines)), 0, 4);
                    }
                    if (empty($included_list)) {
                        $included_list = array(__('Package details available on request.', 'dhr-hotel-management'));
                    }
                ?>
                <div class="swiper-slide">
                    <div class="bys-packages-card">
                        <div class="bys-packages-card__top">
                            <div class="bys-packages-card__frature-img" style="background-image: url('<?php echo esc_url($img_url); ?>')"></div>
                            <div class="card__top-content">
                                <?php
                                $pkg_icon_type = isset($pkg->category_icon_type) ? $pkg->category_icon_type : 'url';
                                if ($pkg_icon_type === 'svg' && !empty($pkg->category_icon_svg)) : ?>
                                    <span class="top-left__icon top-left__icon--svg"><?php echo $pkg->category_icon_svg; ?></span>
                                <?php elseif (!empty($pkg->category_icon_url)) : ?>
                                    <img class="top-left__icon" src="<?php echo esc_url($pkg->category_icon_url); ?>" alt="">
                                <?php else : ?>
                                    <img class="top-left__icon" src="<?php echo esc_url($plugin_url . 'assets/images/icons/experience-icon.svg'); ?>" alt="">
                                <?php endif; ?>
                            </div>
                            <div class="package-overlay">
                                <span class="package-overlay__label"><?php echo esc_html($category_label); ?></span>
                                <h3 class="package-overlay__title"><?php echo esc_html($title); ?></h3>
                                <div class="package-overlay__divider"><span></span></div>
                                <div class="package-overlay__location">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="18" viewBox="0 0 12 18">
                                        <path d="M6 0.75C3.1084 0.75 0.75 3.1084 0.75 6C0.75 8.63672 2.71289 10.8135 5.25 11.1797V18H6.75V11.1797C9.28711 10.8135 11.25 8.63672 11.25 6C11.25 3.1084 8.8916 0.75 6 0.75ZM6 2.25C8.08008 2.25 9.75 3.91992 9.75 6C9.75 8.08008 8.08008 9.75 6 9.75C3.91992 9.75 2.25 8.08008 2.25 6C2.25 3.91992 3.91992 2.25 6 2.25ZM6 3C4.35059 3 3 4.35059 3 6H4.5C4.5 5.16211 5.16211 4.5 6 4.5V3Z"></path>
                                    </svg>
                                    <div>
                                        <h6><?php echo esc_html($hotel_name); ?></h6>
                                        <p><?php echo esc_html($location_line); ?></p>
                                    </div>
                                </div>
                                <div class="mobile-view-package-button">
                                    <a href="javascript:void(0)" class="bys-package-button bys-book-now-link"
                                        data-hotel-code="<?php echo esc_attr($pkg->hotel_code); ?>"
                                        data-channel-id="<?php echo esc_attr($channel_id); ?>"
                                        data-checkin="<?php echo esc_attr($book_now_checkin); ?>"
                                        data-checkout="<?php echo esc_attr($book_now_checkout); ?>"
                                        data-adults="2"
                                        data-children="0"
                                        data-rooms="1"><?php esc_html_e('View Package', 'dhr-hotel-management'); ?></a>
                                </div>
                            </div>
                        </div>
                        <div class="bys-package-content desktop-view-package">
                            <h4 class="package-content__title"><?php esc_html_e('INCLUDED', 'dhr-hotel-management'); ?></h4>
                            <div>
                                <?php if ($short_desc) : ?><p class="package-content__description"><?php echo esc_html($short_desc); ?></p><?php endif; ?>
                                <ul class="bys-package-features">
                                    <?php foreach ($included_list as $line) : ?><li><?php echo esc_html($line); ?></li><?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="package-content__bottom">
                                <div class="package-content__divider"></div>
                                <a href="javascript:void(0)" class="bys-package-button button--theme-2 bys-book-now-link"
                                    data-hotel-code="<?php echo esc_attr($pkg->hotel_code); ?>"
                                    data-channel-id="<?php echo esc_attr($channel_id); ?>"
                                    data-checkin="<?php echo esc_attr($book_now_checkin); ?>"
                                    data-checkout="<?php echo esc_attr($book_now_checkout); ?>"
                                    data-adults="2"
                                    data-children="0"
                                    data-rooms="1"><?php esc_html_e('View Package', 'dhr-hotel-management'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="package-swiper-pagination second-package-pagination"></div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var containers = document.querySelectorAll('.bys-packages .package-swiper');
        containers.forEach(function (el) {
            var block = el.closest('.bys-packages');
            if (block && block.closest('.package-design-grid')) {
                block.classList.add('bys-packages--grid');
                return;
            }
            new Swiper(el, {
                slidesPerView: 1,
                spaceBetween: 15,
                loop: false,
                navigation: false,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                speed: 1500,
                pagination: {
                    el: block.querySelector('.second-package-pagination'),
                    clickable: true,
                    bulletClass: 'package-swiper-pagination-bullet',
                    bulletActiveClass: 'package-swiper-pagination-bullet-active',
                },
                breakpoints: {
                    0: {
                        slidesPerView: "auto",
                        spaceBetween: 15,
                    },
                    767: { slidesPerView: 2, spaceBetween: 20, pagination: false },
                    1024: { slidesPerView: 3, spaceBetween: 25, pagination: false },
                    1280: { slidesPerView: 3, spaceBetween: 32, pagination: false }
                }
            });
        });
    });
</script>
