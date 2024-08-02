<?php
/*
Plugin Name: Chrome Web Store Rating (Customizable Daily Update)
Description: Displays the Chrome Web Store rating for a specified extension. Updates daily at 12 PM if the site is visited, showing the previously fetched value to ensure fast page load times. GDPR compliant with server-side scraping.
Version: 3.0
Author: Maximilian Löser / mxlsr.io (optimized by Claude)
*/

class Chrome_Store_Rating_Plugin {
    private $options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('wp_head', array($this, 'add_css'));
        add_shortcode('chrome_store_rating', array($this, 'chrome_store_rating_shortcode'));
        add_action('chrome_store_rating_cron', array($this, 'scrape_chrome_store_rating'));
        
        if (!wp_next_scheduled('chrome_store_rating_cron')) {
            wp_schedule_event(strtotime('today 12:00:00'), 'daily', 'chrome_store_rating_cron');
        }
    }

    public function add_plugin_page() {
        add_options_page(
            'Chrome Store Rating Settings',
            'Chrome Store Rating',
            'manage_options',
            'chrome-store-rating',
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page() {
        $this->options = get_option('chrome_store_rating_options');
        ?>
        <div class="wrap">
            <h1>Chrome Store Rating Settings</h1>
            <form method="post" action="options.php">
            <?php
                settings_fields('chrome_store_rating_option_group');
                do_settings_sections('chrome-store-rating-admin');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'chrome_store_rating_option_group',
            'chrome_store_rating_options',
            array($this, 'sanitize')
        );

        add_settings_section(
            'chrome_store_rating_setting_section',
            'Settings',
            array($this, 'print_section_info'),
            'chrome-store-rating-admin'
        );

        add_settings_field(
            'chrome_store_url',
            'Chrome Web Store URL',
            array($this, 'chrome_store_url_callback'),
            'chrome-store-rating-admin',
            'chrome_store_rating_setting_section'
        );

        add_settings_field(
            'display_text',
            'Display Text',
            array($this, 'display_text_callback'),
            'chrome-store-rating-admin',
            'chrome_store_rating_setting_section'
        );
    }

    public function sanitize($input) {
        $new_input = array();
        if(isset($input['chrome_store_url']))
            $new_input['chrome_store_url'] = sanitize_text_field($input['chrome_store_url']);
        if(isset($input['display_text']))
            $new_input['display_text'] = sanitize_text_field($input['display_text']);
        return $new_input;
    }

    public function print_section_info() {
        print 'Enter your settings below:';
    }

    public function chrome_store_url_callback() {
        printf(
            '<input type="text" id="chrome_store_url" name="chrome_store_rating_options[chrome_store_url]" value="%s" />',
            isset($this->options['chrome_store_url']) ? esc_attr($this->options['chrome_store_url']) : ''
        );
    }

    public function display_text_callback() {
        printf(
            '<input type="text" id="display_text" name="chrome_store_rating_options[display_text]" value="%s" />',
            isset($this->options['display_text']) ? esc_attr($this->options['display_text']) : 'Chrome Web Store Rating'
        );
    }

    public function scrape_chrome_store_rating() {
        $options = get_option('chrome_store_rating_options');
        $url = $options['chrome_store_url'] ?? '';

        if (empty($url)) {
            error_log('Chrome Web Store URL is not set');
            return;
        }

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            error_log('Error fetching Chrome Web Store page: ' . $response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($body);
        $xpath = new DOMXPath($doc);

        $rating_node = $xpath->query("//span[@class='Vq0ZA']")->item(0);
        $review_count_node = $xpath->query("//span[@class='qwG2Hd']")->item(0);

        if ($rating_node && $review_count_node) {
            $rating = floatval($rating_node->textContent);
            preg_match('/\d+/', $review_count_node->textContent, $matches);
            $review_count = intval($matches[0]);

            $data = [
                'rating' => $rating,
                'reviewCount' => $review_count,
                'timestamp' => current_time('mysql')
            ];

            update_option('chrome_store_rating_data', $data);
        } else {
            error_log('Failed to extract rating data from Chrome Web Store page');
        }
    }

    public function get_chrome_store_rating() {
        $rating_data = get_option('chrome_store_rating_data');
        if (!$rating_data) {
            return [
                'rating' => 0,
                'reviewCount' => 0,
                'timestamp' => current_time('mysql')
            ];
        }
        return $rating_data;
    }

    public function chrome_store_rating_shortcode() {
        $options = get_option('chrome_store_rating_options');
        $rating_data = $this->get_chrome_store_rating();
        $rating = $rating_data['rating'];
        $store_url = $options['chrome_store_url'] ?? '#';
        $display_text = $options['display_text'] ?? 'Chrome Web Store Rating';

        $stars_html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $stars_html .= '<span class="star full">★</span>';
            } elseif ($i - 0.5 <= $rating) {
                $stars_html .= '<span class="star half">★</span>';
            } else {
                $stars_html .= '<span class="star empty">☆</span>';
            }
        }

        return "<a href='{$store_url}' target='_blank' class='chrome-store-rating'>
                    <div class='stars'>{$stars_html}</div>
                    <span class='rating'>{$rating}</span>
                    <span class='review-count'>{$display_text}</span>
                </a>";
    }

    public function add_css() {
        echo '<style>
            .chrome-store-rating {
                display: inline-flex;
                align-items: center;
                font-family: "Arial", sans-serif;
                text-decoration: none;
                background-color: rgba(255, 255, 255, 0.1);
                padding: 10px 15px;
                border-radius: 20px;
                margin: 20px 0;
            }
            .chrome-store-rating:hover {
                background-color: rgba(255, 255, 255, 0.2);
            }
            .chrome-store-rating .stars {
                color: #FFD700;
                font-size: 24px;
                margin-right: 10px;
            }
            .chrome-store-rating .star.half {
                position: relative;
            }
            .chrome-store-rating .star.half:after {
                content: "☆";
                position: absolute;
                left: 0;
                top: 0;
                width: 50%;
                overflow: hidden;
            }
            .chrome-store-rating .rating,
            .chrome-store-rating .review-count {
                font-size: 16px;
                color: #ffffff;
            }
            .chrome-store-rating .rating {
                margin-right: 5px;
                font-weight: bold;
            }
        </style>';
    }
}

if (class_exists('Chrome_Store_Rating_Plugin')) {
    $chrome_store_rating_plugin = new Chrome_Store_Rating_Plugin();
}

// Activation
register_activation_hook(__FILE__, 'chrome_store_rating_activate');
function chrome_store_rating_activate() {
    $plugin = new Chrome_Store_Rating_Plugin();
    $plugin->scrape_chrome_store_rating();
}

// Deactivation
register_deactivation_hook(__FILE__, 'chrome_store_rating_deactivate');
function chrome_store_rating_deactivate() {
    wp_clear_scheduled_hook('chrome_store_rating_cron');
}
