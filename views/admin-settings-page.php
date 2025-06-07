<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap wcps-settings-wrap">
    <h1><?php esc_html_e('تنظیمات اسکرپر قیمت ووکامرس', 'wc-price-scraper'); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields('wc_price_scraper_group'); ?>

        <div class="postbox">
            <h2 class="hndle"><span><?php esc_html_e('بخش ۱: پنهان‌سازی و ادغام ویژگی‌ها', 'wc-price-scraper'); ?></span></h2>
            <div class="inside">
                <p class="description">
                    <?php esc_html_e('ویژگی‌هایی که در اینجا وارد می‌کنید (مثل گارانتی، کد داخلی و...) همیشه از دید کاربر پنهان شده و در فرآیند ادغام متغیرهای مشابه استفاده می‌شوند.', 'wc-price-scraper'); ?>
                </p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="wcps_always_hide_keys"><?php esc_html_e('کلید ویژگی‌ها برای پنهان‌سازی', 'wc-price-scraper'); ?></label></th>
                        <td>
                            <textarea id="wcps_always_hide_keys" name="wcps_always_hide_keys" rows="4" class="large-text code" placeholder="<?php esc_attr_e('هر کلید را در یک خط وارد کنید. مثال: pa_guarantee', 'wc-price-scraper'); ?>"><?php echo esc_textarea(get_option('wcps_always_hide_keys', '')); ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><span><?php esc_html_e('بخش ۲: قوانین حذف شرطی متغیرها', 'wc-price-scraper'); ?></span></h2>
            <div class="inside">
                <p class="description">
                    <?php esc_html_e('قوانینی برای حذف متغیرها بر اساس مقدار یک ویژگی خاص تعریف کنید.', 'wc-price-scraper'); ?><br>
                    <strong><?php esc_html_e('نکته مهم:', 'wc-price-scraper'); ?></strong> <?php esc_html_e('اگر قانونی باعث شود هیچ متغیری باقی نماند، اجرا نشده و صرفاً ویژگی مربوطه از صفحه محصول پنهان خواهد شد.', 'wc-price-scraper'); ?>
                </p>
                <table class="form-table wcps-repeater-table" id="wcps-conditional-rules-table">
                    <tbody id="wcps-rules-container">
                        <?php
                        $conditional_rules = get_option('wcps_conditional_rules', []);
                        if (empty($conditional_rules)) { $conditional_rules[] = ['key' => '', 'value' => '']; }
                        foreach ($conditional_rules as $i => $rule) :
                        ?>
                        <tr valign="top" class="wcps-rule-row">
                            <td>
                                <label><?php esc_html_e('اگر ویژگی', 'wc-price-scraper'); ?></label>
                                <input type="text" name="wcps_conditional_rules[<?php echo $i; ?>][key]" value="<?php echo esc_attr($rule['key']); ?>" placeholder="مثال: pa_location-inventory" class="regular-text" />
                            </td>
                            <td>
                                <label><?php esc_html_e('برابر بود با', 'wc-price-scraper'); ?></label>
                                <input type="text" name="wcps_conditional_rules[<?php echo $i; ?>][value]" value="<?php echo esc_attr($rule['value']); ?>" placeholder="مثال: فروشگاه مشهد" class="regular-text" />
                            </td>
                            <td class="wcps-repeater-action">
                                <button type="button" class="button button-link-delete wcps-remove-rule" title="<?php esc_attr_e('حذف این قانون', 'wc-price-scraper'); ?>"><span class="dashicons dashicons-trash"></span></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">
                                <button type="button" class="button" id="wcps-add-rule"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('افزودن قانون جدید', 'wc-price-scraper'); ?></button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><span><?php esc_html_e('تنظیمات عمومی و کرون‌جاب', 'wc-price-scraper'); ?></span></h2>
            <div class="inside">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="wc_price_scraper_cron_interval"><?php esc_html_e('فاصله به‌روزرسانی (ساعت)', 'wc-price-scraper'); ?></label></th>
                        <td>
                            <input type="number" id="wc_price_scraper_cron_interval" name="wc_price_scraper_cron_interval" value="<?php echo esc_attr(get_option('wc_price_scraper_cron_interval', 12)); ?>" min="1" class="small-text">
                            <p class="description"><?php esc_html_e('هر چند ساعت یکبار قیمت تمام محصولات به صورت خودکار به‌روز شود.', 'wc-price-scraper'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('وضعیت کرون جاب', 'wc-price-scraper'); ?></th>
                        <td>
                            <?php
                            $next_cron = wp_next_scheduled('wc_price_scraper_cron_event');
                            $now = current_time('timestamp');
                            $default_interval = intval(get_option('wc_price_scraper_cron_interval', 12)) * 3600;
                            $diff = $next_cron ? max(0, $next_cron - $now) : $default_interval;
                            ?>
                            <div class="wcps-cron-status">
                                <div>
                                    <span class="wcps-cron-label"><?php esc_html_e('زمان اجرای بعدی:', 'wc-price-scraper'); ?></span>
                                    <span id="cron_countdown" data-seconds-left="<?php echo esc_attr($diff); ?>">--:--</span>
                                </div>
                                <button type="button" class="button button-primary" id="force_reschedule_button">
                                    <span class="dashicons dashicons-controls-play"></span> <?php esc_html_e('شروع فوری کران جاب', 'wc-price-scraper'); ?>
                                </button>
                                <span class="spinner"></span>
                            </div>
                            <span id="reschedule_status" class="wcps-status-text"></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php submit_button(); ?>
    </form>
    <p class="wcps-footer"><b>پلاگین توسعه داده شده توسط <a href="https://sajj.ir/" target="_blank">sajj.ir</a></b></p>
</div>