<?php
/**
 * Fourth Package Design (Early Bird) Template
 * Shortcode: [dhr_package_early_bird_design]
 * Displays packages from database with same design.
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

<!-- Fourth Package Design (Early Bird) -->
<div class="bys-packages bys-packages__bird-main">
    <!-- <div class="early-bird-packages-grid"> -->
    <div class="bird-packages__design swiper">
        <?php if (empty($packages)) : ?>
            <p class="dhr-no-packages"><?php esc_html_e('No packages available at the moment.', 'dhr-hotel-management'); ?></p>
        <?php else : ?>
        <div class="swiper-wrapper">
            <?php foreach ($packages as $item) :
                $pkg = $item['package'];
                $details = $item['details'];
                $title = $details && !empty($details->name) ? $details->name : $pkg->package_code;
                $short_desc = '';
                if ($details && !empty($details->description)) {
                    $short_desc = wp_trim_words(wp_strip_all_tags(wp_unslash((string) $details->description)), 12);
                }
                $img_url = $plugin_url . 'assets/images/package/2.png';
                if ($details && !empty($details->images) && is_array($details->images)) {
                    $first = reset($details->images);
                    if (is_array($first) && !empty($first['fileName'])) {
                        $img_url = esc_url($first['fileName']);
                    } elseif (is_object($first) && !empty($first->fileName)) {
                        $img_url = esc_url($first->fileName);
                    }
                }
                $category_label = !empty($pkg->category_title) ? strtoupper(wp_unslash((string) $pkg->category_title)) : __('PACKAGE', 'dhr-hotel-management');
                $valid_from_ts = !empty($pkg->valid_from) ? strtotime($pkg->valid_from) : null;
                $valid_to_ts = !empty($pkg->valid_to) ? strtotime($pkg->valid_to) : null;
                $valid_text = '';
                if ($valid_from_ts && $valid_to_ts) {
                    $valid_text = sprintf(
                        __('Valid: %s - %s', 'dhr-hotel-management'),
                        function_exists('wp_date') ? wp_date('j M Y', $valid_from_ts) : date('j M Y', $valid_from_ts),
                        function_exists('wp_date') ? wp_date('j M Y', $valid_to_ts) : date('j M Y', $valid_to_ts)
                    );
                }
                $booking_url = !empty($pkg->hotel_code) ? add_query_arg(array('hotel_code' => $pkg->hotel_code, 'channel_id' => $channel_id), home_url('/')) : '#';
            ?>
            <div class="swiper-slide">
                <div class="bird-packages-card">
                    <div class="bird-packages-card__frature-img" style="background-image: url('<?php echo esc_url($img_url); ?>')"></div>
                    <div class="bird-packages-card__premium-img" style="background-image: url('<?php echo esc_url($plugin_url . 'assets/images/package/primium-img.png'); ?>')"></div>
                    <div class="card__bottom-content">
                        <div class="card__top-badge">
                            <p class="package-overlay__tag"><?php echo esc_html($category_label); ?></p>
                        </div>
                        <div class="package-overlay__content">
                            <div class="package-overlay__content__inner">
                                <h3 class="package-overlay__main-title"><?php echo esc_html($title); ?></h3>
                                <?php if ($short_desc) : ?><p class="package-overlay__subtitle"><?php echo esc_html($short_desc); ?></p><?php endif; ?>
                                <?php if ($valid_text) : ?><span class="package-overlay__valid"><?php echo esc_html($valid_text); ?></span><?php endif; ?>
                            </div>
                            <a href="javascript:void(0)" class="package-overlay__link bys-book-now-link"
                                data-hotel-code="<?php echo esc_attr($pkg->hotel_code); ?>"
                                data-channel-id="<?php echo esc_attr($channel_id); ?>"
                                data-checkin="<?php echo esc_attr($book_now_checkin); ?>"
                                data-checkout="<?php echo esc_attr($book_now_checkout); ?>"
                                data-adults="2"
                                data-children="0"
                                data-rooms="1">
                                <?php esc_html_e('View Package', 'dhr-hotel-management'); ?>
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.67578 1.87305L5.04688 2.50195L9.54492 7L5.04688 11.498L5.67578 12.127L10.4883 7.31445L10.7891 7L10.4883 6.68555L5.67578 1.87305Z" fill="#EFF8FD" /><path d="M5.67578 1.87305L6.38289 1.16594L5.67578 0.458833L4.96867 1.16594L5.67578 1.87305Z" fill="#D3AA74" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="package-swiper-pagination bird-package-pagination"></div>
        <?php endif; ?>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var packageSwiper = new Swiper('.bird-packages__design', {
            slidesPerView: 3,
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
                el: '.bird-package-pagination',
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
</script>