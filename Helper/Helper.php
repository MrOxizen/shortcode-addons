<?php

namespace SHORTCODE_ADDONS\Helper;

/**
 *
 * @author biplo
 */
trait Helper {

    /**
     * Plugin fixed
     *
     * @since 2.0.0
     */
    public function fixed_data($agr) {
        return hex2bin($agr);
    }

    /**
     * Plugin fixed debugging data
     *
     * @since 2.0.0
     */
    public function fixed_debug_data($str) {
        return bin2hex($str);
    }

    /**
     * Plugin Elements Name Convert to View
     *
     * @since 2.0.0
     */
    public function name_converter($data) {
        $data = str_replace('_', ' ', $data);
        $data = str_replace('-', ' ', $data);
        $data = str_replace('+', ' ', $data);
        return ucwords($data);
    }

    public function shortcode_render($atts) {
        extract(shortcode_atts(array('id' => ' ',), $atts));
        $styleid = $atts['id'];
        ob_start();
        $CLASS = '\SHORTCODE_ADDONS\Includes\Shortcode';
        if (class_exists($CLASS)) :
            new $CLASS($styleid, 'user');
        endif;
        return ob_get_clean();
    }

    /**
     * Plugin menu Permission
     *
     * @since 2.0.0
     */
    public function menu_permission() {
        $user_role = get_option('oxi_addons_user_permission');
        $role_object = get_role($user_role);
        if (isset($role_object->capabilities) && is_array($role_object->capabilities)) :
            reset($role_object->capabilities);
            return key($role_object->capabilities);
        else :
            return 'manage_options';
        endif;
    }

    /**
     * Plugin admin menu
     *
     * @since 2.0.0
     */
    public function oxilab_admin_menu($agr) {
        $admin_menu = 'get_oxilab_addons_menu';
        $response = !empty(get_transient($admin_menu)) ? get_transient($admin_menu) : [];
        if (!array_key_exists('Shortcode', $response)) :
            $database = new \SHORTCODE_ADDONS\Helper\Database();
            $response = $database->active_menu();
            set_transient($admin_menu, $response, 10 * DAY_IN_SECONDS);
        endif;

        $bgimage = SA_ADDONS_URL . 'image/sa-logo.png';
        $sub = '';

        $menu = '<div class="oxi-addons-wrapper">
                    <div class="oxilab-new-admin-menu">
                        <div class="oxi-site-logo">
                            <a href="' . admin_url('admin.php?page=shortcode-addons') . '" class="header-logo" style=" background-image: url(' . $bgimage . ');">
                            </a>
                        </div>
                        <nav class="oxilab-sa-admin-nav">
                            <ul class="oxilab-sa-admin-menu">';

        $GETPage = sanitize_text_field($_GET['page']);
        $oxitype = (!empty($_GET['oxitype']) ? sanitize_text_field($_GET['oxitype']) : '');

        if (count($response) == 1) :
            if ($oxitype != '') :
                $menu .= '<li class="active"><a href="' . admin_url('admin.php?page=shortcode-addons&oxitype=' . $oxitype) . '">' . $this->name_converter($oxitype) . '</a></li>';
            endif;
            foreach ($response['Shortcode'] as $key => $value) {
                $active = ($GETPage == $value['homepage'] ? (empty($oxitype) ? ' class="active" ' : '') : '');
                $menu .= '<li ' . $active . '><a href="' . admin_url('admin.php?page=' . $value['homepage'] . '') . '">' . $this->name_converter($value['name']) . '</a></li>';
            }
        else :
            foreach ($response as $key => $value) {
                $active = ($key == 'Shortcode' ? 'active' : '');
                $menu .= '<li class="' . $active . '"><a class="oxi-nev-drop-menu" href="#">' . $this->name_converter($key) . '</a>';
                $menu .= '     <div class="oxi-nev-d-menu">
                                                    <div class="oxi-nev-drop-menu-li">';
                foreach ($value as $key2 => $submenu) {
                    $menu .= '<a href="' . admin_url('admin.php?page=' . $submenu['homepage'] . '') . '">' . $this->name_converter($submenu['name']) . '</a>';
                }
                $menu .= '                                                                                                  </div>';
                $menu .= '</li>';
            }
            if ($GETPage == 'shortcode-addons' || $GETPage == 'shortcode-addons-import' || $GETPage == 'shortcode-addons-extension') :
                $sub .= '<div class="shortcode-addons-main-tab-header">';
                if ($oxitype != '') :
                    $sub .= '<a href="' . admin_url('admin.php?page=shortcode-addons&oxitype=' . $oxitype) . '">
                                <div class="shortcode-addons-header oxi-active">' . $this->name_converter($oxitype) . '</div>
                              </a>';
                endif;
                foreach ($response['Shortcode'] as $key => $value) {
                    $active = ($GETPage == $value['homepage'] ? (empty($oxitype) ? 'oxi-active' : '') : '');
                    $sub .= '<a href="' . admin_url('admin.php?page=' . $value['homepage'] . '') . '">
                                <div class="shortcode-addons-header ' . $active . '">' . $this->name_converter($value['name']) . '</div>
                              </a>';
                }
                $sub .= '</div>';
            endif;
        endif;
        $menu .= '              </ul>
                            <ul class="oxilab-sa-admin-menu2">
                               ' . (apply_filters(SA_ADDONS_PLUGIN_ADMIN, false) == FALSE ? ' <li class="fazil-class" ><a target="_blank" href="https://www.oxilabdemos.com/shortcode-addons/pricing/">Upgrade</a></li>' : '') . '
                               <li class="saadmin-doc"><a target="_black" href="https://www.oxilabdemos.com/shortcode-addons/docs/">Docs</a></li>
                               <li class="saadmin-doc"><a target="_black" href="https://wordpress.org/support/plugin/shortcode-addons/">Support</a></li>
                               <li class="saadmin-set"><a href="' . admin_url('admin.php?page=shortcode-addons-settings') . '"><span class="dashicons dashicons-admin-generic"></span></a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                ' . $sub;
        echo __($menu, SHORTCODE_ADDOONS);
    }

    /**
     * Plugin admin menu
     *
     * @since 2.0.0
     */
    public function admin_menu() {
        $permission = $this->menu_permission();
        add_menu_page('Shortcode Addons', 'Shortcode Addons', $permission, 'shortcode-addons', [$this, 'addons_elements']);
        add_submenu_page('shortcode-addons', 'Elements', 'Elements', $permission, 'shortcode-addons', [$this, 'addons_elements']);
        add_submenu_page('shortcode-addons', 'Import', 'Import', $permission, 'shortcode-addons-import', [$this, 'addons_import']);
        add_submenu_page('shortcode-addons', 'Font Manager', 'Font Manager', $permission, 'shortcode-addons-font', [$this, 'addons_font']);
        add_submenu_page('shortcode-addons', 'Settings', 'Settings', $permission, 'shortcode-addons-settings', [$this, 'addons_settings']);
        add_submenu_page('shortcode-addons', 'Oxilab Plugins', 'Oxilab Plugins', $permission, 'shortcode-addons-plugins', [$this, 'oxilab_plugins']);
        add_submenu_page('shortcode-addons', 'Addons Support', 'Addons Support', $permission, 'shortcode-addons-support', [$this, 'addons_support']);
    }

    /**
     * Plugin admin Menu Home
     *
     * @since 2.0.0
     */
    public function addons_elements() {
        $oxitype = ucfirst(strtolower(!empty($_GET['oxitype']) ? sanitize_text_field($_GET['oxitype']) : ''));
        $style = (!empty($_GET['styleid']) ? (int) $_GET['styleid'] : '');
        if (!empty($oxitype) && empty($style)):
            $clsss = '\SHORTCODE_ADDONS_UPLOAD\\' . $oxitype . '\\' . $oxitype . '';
            if (class_exists($clsss)):
                $elements = new $clsss();
                $elements->elements();
            else:
                $url = admin_url('admin.php?page=shortcode-addons');
                echo '<script type="text/javascript"> document.location.href = "' . $url . '"; </script>';
                exit;
            endif;
        elseif (!empty($oxitype) && !empty($style)):
            $database = new \SHORTCODE_ADDONS\Helper\Database();
            $query = $database->wpdb->get_row($database->wpdb->prepare("SELECT style_name, type, id FROM $database->parent_table WHERE id = %d ", $style), ARRAY_A);
            if (array_key_exists('style_name', $query) && strtolower($oxitype) != strtolower($query['type'])):
                $url = admin_url('admin.php?page=shortcode-addons&oxitype=' . $query['type'] . '&styleid=' . $style);
                echo '<script type="text/javascript"> document.location.href = "' . $url . '"; </script>';
                exit;
            endif;
            if (array_key_exists('style_name', $query)):
                $StyleName = ucfirst(str_replace('-', "_", $query['style_name']));
                $clsss = '\SHORTCODE_ADDONS_UPLOAD\\' . $oxitype . '\Admin\\' . $StyleName . '';
                if (class_exists($clsss)):
                    new $clsss();
                else:
                    $this->file_check($oxitype);
                    $url = admin_url('admin.php?page=shortcode-addons');
                    echo '<script type="text/javascript"> document.location.href = "' . $url . '"; </script>';
                    exit;
                endif;
            else:
                $url = admin_url('admin.php?page=shortcode-addons');
                echo '<script type="text/javascript"> document.location.href = "' . $url . '"; </script>';
                exit;
            endif;
        else:
            $elements = new \SHORTCODE_ADDONS\Layouts\Collection();
            $elements->element_page();
        endif;
    }

    /**
     * Plugin Import Addons
     *
     * @since 2.1.0
     */
    public function addons_import() {


        $elements = new \SHORTCODE_ADDONS\Layouts\Import();
        $elements->element_page();
    }

    /**
     * Plugin admin Menu Extension
     *
     * @since 2.0.0
     */
    public function addons_settings() {
        new \SHORTCODE_ADDONS\Layouts\Settings();
    }

    /**
     * Plugin admin Menu Extension
     *
     * @since 2.0.0
     */
    public function addons_font() {
        $elements = new \SHORTCODE_ADDONS\Layouts\GoogleFont();
        $elements->font_manager();
    }

    public function addons_support() {
        new \SHORTCODE_ADDONS\Layouts\Support();
    }

    public function oxilab_plugins() {
        if (current_user_can('activate_plugins')):
            new \SHORTCODE_ADDONS\Layouts\Plugins();
        endif;
    }

    /**
     * shortcode addons menu Icon
     * @since 1.0.0
     */
    public function admin_icon() {
        ?>
        <style type='text/css' media='screen'>
            #adminmenu #toplevel_page_shortcode-addons div.wp-menu-image:before {
                content: "\f486";
            }
        </style>
        <?php

    }

    public function supportandcomments($agr) {
        echo '  <div class="oxi-addons-admin-notifications">
                    <h3>
                        <span class="dashicons dashicons-flag"></span> 
                        Notifications
                    </h3>
                    <p></p>
                    <div class="oxi-addons-admin-notifications-holder">
                        <div class="oxi-addons-admin-notifications-alert">
                            <p>Unable to create your desire design or need any help? I Just wanted to see if you have any questions or concerns about my plugins. If you do, Please do not hesitate to <a href="https://wordpress.org/support/plugin/shortcode-addons#new-post">file a bug report or help</a>. Our dedicated team will helps you to solve your issues. </p>
                            ' . (apply_filters(SA_ADDONS_PLUGIN_ADMIN, false) ? '' : '<p>By the way, did you know we also have a <a href="https://www.oxilabdemos.com/shortcode-addons/pricing/">Premium Version</a>? It offers lots of options with automatic update. It also comes with 16/5 personal support.</p>') . '
                            <p>Thanks Again!</p>
                            <p></p>
                        </div>                     
                    </div>
                    <p></p>
                </div>';
    }

    /**
     * Plugin check Current Accordions
     *
     * @since 2.0.1
     */
    public function check_current_version($agr) {
        $vs = get_option('oxi_addons_license_status');
        if ($vs == $this->fixed_data('76616c6964')) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Shortcode Addons file Check.
     * 
     * @since 2.1.0
     */

    public function file_check($elements) {
        ob_start();
        $upload = new \SHORTCODE_ADDONS\Core\Console();
        $upload->post_get_elements($elements);
        ob_get_clean();
    }

    /**
     * Shortcode Call
     */
    public function oxi_addons_shortcode($atts) {
        extract(shortcode_atts(array('id' => ' ',), $atts));
        $styleid = (int) $atts['id'];
        $shortcode = new \SHORTCODE_ADDONS\Layouts\Shortcode();
        return $shortcode->oxi_addons($styleid);
    }

    /**
     * shortcode addons Data Process
     * 
     * @since 2.0.0
     */
    public function shortcode_addons_data_process() {

        if (isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_key(wp_unslash($_POST['_wpnonce'])), 'shortcode-addons-data')):
            $classname = isset($_POST['classname']) ? '\\' . str_replace('\\\\', '\\', sanitize_text_field($_POST['classname'])) : '';
            $functionname = isset($_POST['functionname']) ? sanitize_text_field($_POST['functionname']) : '';
            $rawdata = isset($_POST['rawdata']) ? sanitize_post($_POST['rawdata']) : '';
            $optional = isset($_POST['optional']) ? sanitize_post($_POST['optional']) : '';
            $optional2 = isset($_POST['optional2']) ? sanitize_post($_POST['optional2']) : '';
            if (!empty($classname) && !empty($functionname)):
                $classname::$functionname($rawdata, $optional, $optional2);
            endif;
        else:
            return;
        endif;
        die();
    }

}