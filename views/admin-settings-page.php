<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap wcps-settings-wrap">
    <h1><?php esc_html_e('تنظیمات اسکرپر قیمت ووکامرس', 'wc-price-scraper'); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields('wc_price_scraper_group'); ?>

        <h2 style="margin-top: 20px;"><?php esc_html_e('بخش ۱: پنهان‌سازی و ادغام متغیرها', 'wc-price-scraper'); ?></h2>
        <p class="description">
            <?php esc_html_e('اتروبیوت‌هایی که در اینجا وارد می‌کنید، همیشه از دید کاربر پنهان می‌شوند. اگر دو متغیر پس از پنهان شدن این اتربیوت‌ها کاملاً یکسان شوند، فقط یکی از آن‌ها باقی می‌ماند. (برای اتربیوت‌هایی مثل گارانتی، کد داخلی و ...)', 'wc-price-scraper'); ?>
        </p>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wcps_always_hide_keys"><?php esc_html_e('کلید اتربیوت‌ها برای پنهان‌سازی', 'wc-price-scraper'); ?></label></th>
                <td>
                    <textarea id="wcps_always_hide_keys" name="wcps_always_hide_keys" rows="4" class="large-text code" placeholder="<?php esc_attr_e('هر کلید را در یک خط وارد کنید. مثال: pa_guarantee', 'wc-price-scraper'); ?>"><?php echo esc_textarea(get_option('wcps_always_hide_keys', '')); ?></textarea>
                </td>
            </tr>
        </table>

        <h2 style="margin-top: 30px;"><?php esc_html_e('بخش ۲: قوانین حذف شرطی متغیرها', 'wc-price-scraper'); ?></h2>
        <p class="description">
            <?php esc_html_e('قوانینی برای حذف متغیرها بر اساس مقدار یک اتربیوت خاص تعریف کنید.', 'wc-price-scraper'); ?><br>
            <strong style="color: #d63638;"><?php esc_html_e('نکته مهم:', 'wc-price-scraper'); ?></strong> <?php esc_html_e('اگر قانونی باعث شود هیچ متغیری باقی نماند، اجرا نشده و صرفاً اتربیوت مربوطه از صفحه محصول پنهان خواهد شد.', 'wc-price-scraper'); ?>
        </p>
        <table class="form-table" id="wcps-conditional-rules-table">
            <tbody id="wcps-rules-container">
                <?php
                $conditional_rules = get_option('wcps_conditional_rules', []);
                if (empty($conditional_rules)) { $conditional_rules[] = ['key' => '', 'value' => '']; }
                foreach ($conditional_rules as $i => $rule) :
                ?>
                <tr valign="top" class="wcps-rule-row">
                    <td>
                        <label><?php esc_html_e('اگر اتربیوت', 'wc-price-scraper'); ?></label>
                        <input type="text" name="wcps_conditional_rules[<?php echo $i; ?>][key]" value="<?php echo esc_attr($rule['key']); ?>" placeholder="مثال: pa_location-inventory" class="regular-text" />
                    </td>
                    <td>
                        <label><?php esc_html_e('برابر بود با', 'wc-price-scraper'); ?></label>
                        <input type="text" name="wcps_conditional_rules[<?php echo $i; ?>][value]" value="<?php echo esc_attr($rule['value']); ?>" placeholder="مثال: فروشگاه مشهد" class="regular-text" />
                    </td>
                    <td>
                        <button type="button" class="button button-link-delete wcps-remove-rule" style="color: #d63638;"><?php esc_html_e('حذف', 'wc-price-scraper'); ?></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="padding-top: 10px;">
                        <button type="button" class="button" id="wcps-add-rule"><?php esc_html_e('+ افزودن قانون جدید', 'wc-price-scraper'); ?></button>
                    </td>
                </tr>
            </tfoot>
        </table>

        <h2 style="margin-top: 30px;"><?php esc_html_e('تنظیمات عمومی و کرون‌جاب', 'wc-price-scraper'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="wc_price_scraper_cron_interval"><?php esc_html_e('فاصله به‌روزرسانی (ساعت)', 'wc-price-scraper'); ?></label></th>
                <td>
                    <input type="number" id="wc_price_scraper_cron_interval" name="wc_price_scraper_cron_interval" value="<?php echo esc_attr(get_option('wc_price_scraper_cron_interval', 12)); ?>" min="1" class="small-text">
                    <p class="description"><?php esc_html_e('هر چند ساعت یکبار قیمت تمام محصولات به صورت خودکار به‌روز شود.', 'wc-price-scraper'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('عملیات کرون جاب', 'wc-price-scraper'); ?></th>
                <td>
                    <?php
                    $next_cron = wp_next_scheduled('wc_price_scraper_cron_event');
                    $now = current_time('timestamp');
                    $default_interval = intval(get_option('wc_price_scraper_cron_interval', 12)) * 3600;
                    $diff = $next_cron ? max(0, $next_cron - $now) : $default_interval;
                    $next_cron_str = $next_cron ? date_i18n(get_option('date_format') . ' @ ' . get_option('time_format'), $next_cron) : __('برنامه‌ریزی نشده', 'wc-price-scraper');
                    ?>
                    <span id="cron_countdown" data-seconds-left="<?php echo esc_attr($diff); ?>">--:--</span>
                    <span style="margin-right:10px; color:#0073aa;"><b><?php esc_html_e('زمان اجرای بعدی:', 'wc-price-scraper'); ?></b> <?php echo esc_html($next_cron_str); ?></span>
                    
                    <p style="margin-top: 15px;">
                        <button type="button" class="button button-primary" id="force_reschedule_button">شروع فوری کران جاب (در پس‌زمینه)</button>
                        <span id="reschedule_status" style="margin-right: 10px; font-weight: bold;"></span>
                        <span class="spinner" style="float: none; margin-top: 4px;"></span>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wc_price_scraper_ignore_guarantee"><?php esc_html_e('نادیده گرفتن گارانتی‌ها', 'wc-price-scraper'); ?></label></th>
                <td>
                    <textarea id="wc_price_scraper_ignore_guarantee" name="wc_price_scraper_ignore_guarantee" rows="5" class="large-text code" placeholder="<?php esc_attr_e('هر نام گارانتی را در یک خط وارد کنید', 'wc-price-scraper'); ?>"><?php echo esc_textarea(get_option('wc_price_scraper_ignore_guarantee', '')); ?></textarea>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('اولویت‌بندی دسته‌بندی‌ها', 'wc-price-scraper'); ?>
                    <p class="description" style="font-weight:normal;"><?php esc_html_e('دسته‌هایی که انتخاب می‌کنید، در ابتدای صف اسکرپ قرار می‌گیرند.', 'wc-price-scraper'); ?></p>
                </th>
                <td>
                    <?php
                    $priority_cats_val = (array)get_option('wc_price_scraper_priority_cats', []);
                    $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
                    if (!empty($cats) && !is_wp_error($cats)) {
                        echo '<div class="category-checklist">';
                        foreach ($cats as $c) {
                            $checked = in_array($c->term_id, $priority_cats_val) ? 'checked' : '';
                            echo '<label><input type="checkbox" name="wc_price_scraper_priority_cats[]" value="' . esc_attr($c->term_id) . '" ' . $checked . '> ' . esc_html($c->name) . '</label><br>';
                        }
                        echo '</div>';
                    }
                    ?>
                </td>
            </tr>
        </table>
        
        <h2><?php esc_html_e('تنظیمات یکپارچه‌سازی با N8N', 'wc-price-scraper'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('فعال‌سازی N8N', 'wc-price-scraper'); ?></th>
                <td>
                    <label for="wc_price_scraper_n8n_enable">
                        <input name="wc_price_scraper_n8n_enable" type="checkbox" id="wc_price_scraper_n8n_enable" value="yes" <?php checked('yes', get_option('wc_price_scraper_n8n_enable', 'no')); ?> />
                        <?php esc_html_e('ارسال داده به N8N پس از همگام‌سازی موفق.', 'wc-price-scraper'); ?>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wc_price_scraper_n8n_webhook_url"><?php esc_html_e('URL وب‌هوک N8N', 'wc-price-scraper'); ?></label></th>
                <td>
                    <input type="url" id="wc_price_scraper_n8n_webhook_url" name="wc_price_scraper_n8n_webhook_url" value="<?php echo esc_attr(get_option('wc_price_scraper_n8n_webhook_url', '')); ?>" class="large-text" placeholder="https://n8n.example.com/webhook/your-hook-id" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="wc_price_scraper_n8n_model_slug"><?php esc_html_e('نامک ویژگی برای "مدل"', 'wc-price-scraper'); ?></label></th>
                <td>
                    <input type="text" id="wc_price_scraper_n8n_model_slug" name="wc_price_scraper_n8n_model_slug" value="<?php echo esc_attr(get_option('wc_price_scraper_n8n_model_slug', '')); ?>" class="regular-text" placeholder="<?php esc_attr_e('مثال: model یا size', 'wc-price-scraper'); ?>" />
                    <p class="description"><?php esc_html_e('نامک (slug) ویژگی که می‌خواهید به عنوان "مدل" در داده‌های ارسالی به N8N استفاده شود (بدون پیشوند pa_).', 'wc-price-scraper'); ?></p>
                </td>
            </tr>
             <tr valign="top">
                <th scope="row"><label for="wc_price_scraper_n8n_purchase_link_text"><?php esc_html_e('متن لینک خرید برای شیت', 'wc-price-scraper'); ?></label></th>
                <td>
                    <input type="text" id="wc_price_scraper_n8n_purchase_link_text" name="wc_price_scraper_n8n_purchase_link_text" value="<?php echo esc_attr(get_option('wc_price_scraper_n8n_purchase_link_text', 'Buy Now')); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        
        <h2><?php esc_html_e('عملیات اضطراری', 'wc-price-scraper'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('توقف کامل', 'wc-price-scraper'); ?></th>
                <td>
                    <button type="button" class="button button-danger" id="force_stop_button">توقف تمام عملیات و پاک‌سازی کامل صف</button>
                    <p class="description"><?php esc_html_e('اگر احساس می‌کنید فرآیندی گیر کرده و متوقف نمی‌شود، از این دکمه استفاده کنید. این دکمه تمام کران‌جاب‌های این پلاگین (چه در حال اجرا و چه زمان‌بندی شده) را فوراً حذف می‌کند.', 'wc-price-scraper'); ?></p>
                    <span id="stop_status" style="margin-left: 10px; font-weight: bold;"></span>
                </td>
            </tr>
        </table>
        <style>.button-danger { background: #d63638; border-color: #b62b2d; color: #fff; } .button-danger:hover { background: #b62b2d; border-color: #9f2628; }</style>
        
        <?php submit_button(); ?>
    </form>
    <p><b>پلاگین توسعه داده شده توسط <a href="https://sajj.ir/" target="_blank">sajj.ir</a></b></p>
</div>
<style>.category-checklist { max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; background: #fff; }</style>
<?php
file_put_contents(WP_CONTENT_DIR . '/test_cron_wcps.txt', date('Y-m-d H:i:s') . " CRON WCPS\n", FILE_APPEND);
?>