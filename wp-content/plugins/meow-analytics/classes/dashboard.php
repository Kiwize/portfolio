<?php

class Meow_Analytics_Dashboard
{

    public function __construct()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_ajax_mga_refresh_token', array($this, 'ajax_refresh_token'));
    }

    function refresh_token()
    {
        $auth = new Meow_Analytics_Auth();
        $auth->getRefreshToken();
    }

    function admin_init()
    {

        // Token is expiring or has expired? Let's renew it. 
        $expires_at = get_option('mga_expires_at');
        if ($expires_at - time() <= 5) {
            $this->refresh_token();
        }

        // Let's load the dashboard and its files
        if (!wp_doing_ajax()) {
            add_action('wp_dashboard_setup', array($this, 'wp_dashboard_setup'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }
    }

    function ajax_refresh_token()
    {
        $this->refresh_token();
        echo $this->get_analytics_environment();
        die();
    }

    function admin_enqueue_scripts()
    {
        $physical_file = MGA_PATH . '/app/index.js';
        $cache_buster = file_exists($physical_file) ? filemtime($physical_file) : MGA_VERSION;
        wp_register_script(
            'mga_meow_analytics-vendor',
            MGA_URL . 'app/vendor.js',
            ['wp-element', 'wp-i18n'],
            $cache_buster
        );
        wp_register_script(
            'mga_meow_analytics',
            MGA_URL . 'app/index.js',
            ['mga_meow_analytics-vendor', 'wp-i18n'],
            $cache_buster
        );
        wp_enqueue_script('mga_meow_analytics');

        $auth = new Meow_Analytics_Auth();
        wp_localize_script( 'mga_meow_analytics', 'mga_meow_analytics', [
			'api_url' => get_rest_url(null, '/meow-analytics/v1/'),
			'rest_url' => get_rest_url(),
			'plugin_url' => MGA_URL,
			'prefix' => MGA_PREFIX,
			'domain' => MGA_DOMAIN,
			'is_pro' => class_exists( 'MeowPro_Analytics_Core' ),
			'is_registered' => !!apply_filters( MGA_PREFIX . '_meowapps_is_registered', false, MGA_PREFIX  ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'site_url' => get_site_url(),
			'redirect_url' => $auth->getRedirectURL(),
			'auth_url' => $auth->getAuthURL(),
		] );
    }

    function wp_dashboard_setup()
    {
        wp_add_dashboard_widget(
            'dashboard_meow_analytics',
            __('Meow Analytics', 'meow-analytics'),
            array($this, 'create_dashboard')
        );
    }

    function get_analytics_environment()
    {
        return json_encode(array(
            'tracking_id' => get_option('mga_tracking_id'),
            'expires_at' => intval(get_option('mga_expires_at')),
            'access_token' => get_option('mga_access_token'),
            'debug_mode' => WP_DEBUG === true
        ));
    }

    function create_google_login()
    {
?>
        <div>
            <script type="text/javascript">
                (function(w, d, s, g, js, fjs) {
                    g = w.gapi || (w.gapi = {});
                    g.analytics = {
                        q: [],
                        ready: function(cb) {
                            this.q.push(cb)
                        }
                    };
                    js = d.createElement(s);
                    fjs = d.getElementsByTagName(s)[0];
                    js.src = 'https://apis.google.com/js/platform.js';
                    fjs.parentNode.insertBefore(js, fjs);
                    js.onload = function() {
                        g.load('analytics')
                    };
                }(window, document, 'script'));
            </script>
            <script type="text/javascript">
                var meowAnalytics = <?= $this->get_analytics_environment() ?>;
            </script>
        </div>
<?php
    }

    function create_dashboard()
    {
        $this->create_google_login();
        echo '<div id="meow-analytics-container"></div>';
    }
}

?>