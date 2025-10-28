<?php
/**
 * Plugin Name: Eweb Techs - Sajid Dashboard Customisation
 * Description: Custom admin layout, menu, and restrictions for user info@spearremovals.co.uk
 * Version: 1.1
 * Author: Eweb Techs
 */

add_action('init', function () {
    if (!is_user_logged_in()) return;

    $user = wp_get_current_user();
    if (!$user || $user->user_email !== 'info@spearremovals.co.uk') return;

    // Redirect on login
    add_filter('login_redirect', function ($redirect_to, $request, $user) {
        if ($user && $user->user_email === 'info@spearremovals.co.uk') {
            return admin_url();
        }
        return $redirect_to;
    }, 10, 3);

    // Hide admin bar
    add_filter('show_admin_bar', '__return_false');

    // Clean up admin menu
    add_action('admin_menu', function () {
        global $menu;
        $allowed = ['FluentCRM', 'Pages', 'Posts', 'WooCommerce', 'Services'];

        foreach ($menu as $index => $item) {
            if (!empty($item[0])) {
                $visible = false;
                foreach ($allowed as $keyword) {
                    if (stripos($item[0], $keyword) !== false) {
                        $visible = true;
                        break;
                    }
                }
                if (!$visible) {
                    remove_menu_page($item[2]);
                }
            }
        }

        remove_submenu_page('themes.php', 'theme-editor.php');
        remove_submenu_page('plugins.php', 'plugin-editor.php');
    }, 999);

    // Custom dashboard widgets
    add_action('wp_dashboard_setup', function () {
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_meta_box('dashboard_site_health', 'dashboard', 'normal');

        wp_add_dashboard_widget('custom_welcome_widget', 'Welcome to Your Dashboard', 'ewebtechs_sajid_welcome_widget');
    });

    // Custom logo style
    add_action('admin_head', function () {
        echo '<style>
            #wp-admin-bar-wp-logo { display: none !important; }
            #adminmenu #menu-dashboard:before {
                content: "";
                background-image: url("https://ewebtechs.com/wp-content/uploads/2025/07/logo-1.png");
                background-size: contain;
                background-repeat: no-repeat;
                display: inline-block;
                width: 20px;
                height: 20px;
                margin-right: 10px;
                vertical-align: middle;
            }
        </style>';
    });
});

// Dashboard widget content
function ewebtechs_sajid_welcome_widget() {
    ?>
    <div style="font-family:Arial, sans-serif; padding:20px; background:#f1f5f9; border:1px solid #d1d5db; border-radius:10px;">
        <img src="https://ewebtechs.com/wp-content/uploads/2025/07/logo-1.png" alt="eWeb Techs" style="width:150px; margin-bottom:15px;" />
        <h2 style="margin-top:0; color:#0f172a;">Hi Sajid 👋</h2>
        <p style="font-size:15px; color:#1e293b;">Welcome to your custom dashboard. Here’s where you manage your business, check your leads, and view customer orders.</p>
        <ul style="list-style:none; padding-left:0; line-height:2; font-size:15px;">
            <li>📧 <a href="/wp-admin/admin.php?page=fluentcrm-admin" style="color:#0ea5e9;">View Your Leads</a></li>
            <li>🧰 <a href="/wp-admin/edit.php?post_type=services" style="color:#0ea5e9;">Manage Services</a></li>
            <li>📦 <a href="/wp-admin/edit.php?post_type=shop_order" style="color:#0ea5e9;">View Orders</a></li>
            <li>🌐 <a href="/" target="_blank" style="color:#0ea5e9;">Visit Your Website</a></li>
            <li>💬 <a href="mailto:support@ewebtechs.com" style="color:#0ea5e9;">Contact Support</a></li>
        </ul>
    </div>
    <?php
}
