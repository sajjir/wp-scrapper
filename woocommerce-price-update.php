<?php
/*
 * Plugin Name: WooCommerce Price Scraper
 * Description: اسکرپ قیمت محصولات از سایت مرجع، ساخت اتو واریشن‌ها، تنظیمات فیلتر گارانتی و دسته‌بندی استثنا
 * Version: 4.0 - Fully Refactored & Patched
 * Author: سج - SAJJ.IR
 * Text Domain: wc-price-scraper
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

define('WC_PRICE_SCRAPER_VERSION', '4.0');
define('WC_PRICE_SCRAPER_PATH', plugin_dir_path(__FILE__));
define('WC_PRICE_SCRAPER_URL', plugin_dir_url(__FILE__));
define('WC_PRICE_SCRAPER_API_ENDPOINT', 'http://45.156.185.161/scrape');
define('WC_PRICE_SCRAPER_DEBUG', true);

if (!class_exists('WC_Price_Scraper')) {
    final class WC_Price_Scraper {

        private static $instance;
        protected $debug_log_path;

        public $n8n_integration;
        public $admin;
        public $core;
        public $ajax_cron;

        public static function instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
                self::$instance->setup_classes();
                self::$instance->add_hooks();
            }
            return self::$instance;
        }

        private function __construct() {
            // تغییر مسیر لاگ به wp-content/debug.log برای اطمینان از دسترسی
            $this->debug_log_path = WP_CONTENT_DIR . '/debug.log';
            $this->includes();
        }

        private function includes() {
            require_once WC_PRICE_SCRAPER_PATH . 'includes/class-wcps-admin.php';
            require_once WC_PRICE_SCRAPER_PATH . 'includes/class-wcps-core.php';
            require_once WC_PRICE_SCRAPER_PATH . 'includes/class-wcps-ajax-cron.php';
            
            if (file_exists(WC_PRICE_SCRAPER_PATH . 'includes/n8n.php')) {
                require_once WC_PRICE_SCRAPER_PATH . 'includes/n8n.php';
            }
        }
        
        private function setup_classes() {
            $this->core = new WCPS_Core($this);
            $this->admin = new WCPS_Admin($this);
            $this->ajax_cron = new WCPS_Ajax_Cron($this, $this->core);

            if (class_exists('WC_Price_Scraper_N8N_Integration')) {
                $this->n8n_integration = new WC_Price_Scraper_N8N_Integration($this);
            }
        }

        private function add_hooks() {
            add_action('init', [$this, 'init_global_attributes']);
            
            // Admin Hooks
            add_action('admin_menu', [$this->admin, 'add_settings_page']);
            add_action('admin_init', [$this->admin, 'register_settings']);
            add_action('admin_enqueue_scripts', [$this->admin, 'enqueue_admin_scripts']);
            add_action('wp_dashboard_setup', [$this->admin, 'setup_dashboard_widget']);
            add_action('woocommerce_product_options_general_product_data', [$this->admin, 'add_scraper_fields']);
            add_action('woocommerce_process_product_meta', [$this->admin, 'save_scraper_fields'], 10, 2);
            add_action('woocommerce_variation_options_pricing', [$this->admin, 'add_protected_variation_checkbox'], 20, 3);
            add_action('woocommerce_save_product_variation', [$this->admin, 'save_protected_variation_checkbox'], 10, 2);
            add_action('woocommerce_save_product_variation', [$this->admin, 'set_variation_sku_on_manual_save'], 99, 2);

            // AJAX & Cron Hooks
            add_action('wp_ajax_scrape_price', [$this->ajax_cron, 'scrape_price_callback']);
            add_action('wp_ajax_update_variation_price', [$this->ajax_cron, 'update_variation_price_callback']);
            add_action('wp_ajax_wc_price_scraper_next_cron', [$this->ajax_cron, 'ajax_next_cron']);
            add_filter('cron_schedules', [$this->ajax_cron, 'add_cron_interval']);
            add_action('wc_price_scraper_cron_event', [$this->ajax_cron, 'cron_update_all_prices']);
            register_activation_hook(__FILE__, [$this->ajax_cron, 'activate']);
            register_deactivation_hook(__FILE__, [$this->ajax_cron, 'deactivate']);
            add_action('update_option_wc_price_scraper_cron_interval', [$this->ajax_cron, 'handle_settings_save'], 10, 3);
            add_action('update_option_wcps_high_frequency_pids', [$this->ajax_cron, 'handle_settings_save'], 10, 3);
            add_action('update_option_wcps_high_frequency_interval', [$this->ajax_cron, 'handle_settings_save'], 10, 3);
            add_action('wp_ajax_wcps_force_reschedule', [$this->ajax_cron, 'ajax_force_reschedule_callback']);
            add_action('wp_ajax_wcps_force_stop', [$this->ajax_cron, 'ajax_force_stop_all_crons']);
            add_action('wp_ajax_wcps_run_scrape_task', [$this->ajax_cron, 'run_scrape_task_handler']);
            add_action('wp_ajax_nopriv_wcps_run_scrape_task', [$this->ajax_cron, 'run_scrape_task_handler']);
            add_action('wcps_force_run_all_event', [$this->ajax_cron, 'cron_update_all_prices']);

            // High-Frequency Hooks
            add_action('wcps_high_frequency_cron_event', [$this->ajax_cron, 'run_high_frequency_scrape']);
            add_action('wp_ajax_wcps_force_hf_scrape', [$this->ajax_cron, 'ajax_force_high_frequency_scrape']);

            add_action('wcps_scrape_single_product_task', [$this->ajax_cron, 'scrape_single_product_handler'], 10, 1);

            add_action('wp_ajax_wcps_clear_failed_log', [
                $this->ajax_cron, 'ajax_clear_failed_log_callback'
            ]);
        }

        // --- Utility Functions ---
        /**
         * Gets the path for the log file.
         *
         * @return string The full path to the log file.
         */
        public function get_log_path() {
            $upload_dir = wp_upload_dir();
            return $upload_dir['basedir'] . '/wc-price-scraper.log';
        }

        /**
         * Logs messages to a dedicated file with structured data.
         *
         * @param string $message The log message.
         * @param string $type The type of log entry (e.g., INFO, ERROR, CRON_START).
         * @param array|null $data Optional data to include in the log.
         */
        public function debug_log($message, $type = 'INFO', $data = null) {
            if (!WC_PRICE_SCRAPER_DEBUG && $type === 'INFO') {
                return;
            }
            
            $log_path = $this->get_log_path();
            $timestamp = current_time('timestamp');
            $datetime = date_i18n('Y-m-d H:i:s', $timestamp);
            
            $log_entry = "[$datetime] [$type] - $message";
            if ($data !== null) {
                $log_entry .= " - Data: " . wp_json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            $log_entry .= "\n";
            
            file_put_contents($log_path, $log_entry, FILE_APPEND);
        }

        public function make_api_call($url, $attempts = 3) {
            for ($i = 1; $i <= $attempts; $i++) {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL            => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 380,
                    CURLOPT_CONNECTTIMEOUT => 60,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_FOLLOWLOCATION => true,
                ]);
                $body      = curl_exec($ch);
                $error     = curl_error($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if (!$error && $http_code >= 200 && $http_code < 300) {
                    return $body;
                }
                $this->debug_log("API Call Attempt {$i} failed (HTTP: {$http_code}, Error: {$error}). Retrying...");
                if ($i < $attempts) sleep(2);
            }
            return new WP_Error('api_timeout', "cURL Error after {$attempts} attempts: {$error} (HTTP Code: {$http_code})");
        }

        public function init_global_attributes() {
            if (!taxonomy_exists('pa_color')) {
                wc_create_attribute(['name' => __('رنگ', 'wc-price-scraper'), 'slug' => 'color', 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => false]);
            }
            if (!taxonomy_exists('pa_guarantee')) {
                wc_create_attribute(['name' => __('گارانتی', 'wc-price-scraper'), 'slug' => 'guarantee', 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => false]);
            }
        }
    }
}

// Initialize the plugin and load textdomain in a standard way
function wc_price_scraper_init() {
    // Load plugin textdomain correctly on init
    load_plugin_textdomain('wc-price-scraper', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    
    // Return instance
    WC_Price_Scraper::instance();
}
add_action('init', 'wc_price_scraper_init', 10);