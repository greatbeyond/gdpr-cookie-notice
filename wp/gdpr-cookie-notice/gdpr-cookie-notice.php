<?php
/*
Plugin Name: GDPR Cookie Notice
Plugin URI:
description:
Version: 1.0
Author:
Author URI:
License: MIT
*/



class GDPRCookieNotice
{
    public $fields = [
        [
            'name' => 'ga',
            'label' => 'Google Analytics ID',
            'type' => 'input'
        ],
        [
            'name' => 'px',
            'label' => 'Facebook Pixel ID',
            'type' => 'input'
        ],
        [
            'name' => 'policy-url',
            'label' => 'Policy URL',
            'type' => 'input'
        ],
        [
            'name' => 'description',
            'label' => 'Beskrivning (Banner)',
            'type' => 'textarea'
        ],
        [
            'name' => 'cookie_essential_desc',
            'label' => 'Villkor (Grundläggande)',
            'type' => 'textarea'
        ],
        [
            'name' => 'cookie_performance_desc',
            'label' => 'Villkor (Prestanda)',
            'type' => 'textarea'
        ],
        [
            'name' => 'cookie_analytics_desc',
            'label' => 'Villkor (Statistik)',
            'type' => 'textarea'
        ],
        [
            'name' => 'cookie_marketing_desc',
            'label' => 'Villkor (Marknadsföring)',
            'type' => 'textarea'
        ]
    ];

    public function __construct()
    {
        $this->prefix = pathinfo(basename(__FILE__), PATHINFO_FILENAME);
        $this->locale = get_locale();

        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_menu', [$this,'register_menu_page']);
        add_action('wp_footer', [$this,'footer_scripts']);
    }

    public function prefixer($name)
    {
        return $this->prefix.'-'.$name;
    }

    public function get_option($key)
    {
        return get_option($this->prefixer($key));
    }

    public function update_option($key, $valie)
    {
        return update_option($this->prefixer($key), $value);
    }

    public function enqueue()
    {
        wp_enqueue_style($this->prefixer('style'), plugin_dir_url(__FILE__). 'dist/style.css');
        wp_enqueue_script($this->prefixer('script'), plugin_dir_url(__FILE__). 'dist/script.js');
    }

    public function register_menu_page()
    {
        add_submenu_page('options-general.php', 'GDPR Cookie Notice', 'GDPR Notice', 'manage_options', 'gdpr-cookie-notice', [$this,'menu_page']);
    }

    public function menu_page()
    {
        $this->save(); ?>
            <h1>GDPR Cookie Notice</h1>



            <form method="post">
                <?php wp_nonce_field($this->prefix); ?>
                <table class="form-table">
                    <tbody>
                    <?php foreach ($this->fields as $field) : ?>
                        <tr>
                            <th scope="row">
                                <label for="blogname"><?= $field['label']  ?></label>
                                <?php if (isset($field['desc'])) : ?>
                                    <p class="description"><?= $field['desc']; ?></p>
                                <?php endif; ?>
                            </th>
                            <td>
                                <?php if ($field['type'] === 'input') : ?>
                                    <input name="<?= $this->prefix.'['.$field['name'].']'  ?>" type="text" id="<?= $field['name']  ?>" value="<?= $this->get_option($field['name']); ?>" class="regular-text">
                                <?php endif; ?>
                                <?php if ($field['type'] === 'textarea') : ?>
                                    <textarea rows="5" name="<?= $this->prefix.'['.$field['name'].']'  ?>" id="<?= $field['name']  ?>" value="<?= $this->get_option($field['name']); ?>" class="regular-text"></textarea>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Spara ändringar"></p>

            </form>

        <?php
    }

    public function footer_scripts()
    {
        $policy_url = $this->get_option('policy-url');

        if ($policy_url) {
            ?>

            <script type="text/javascript">
                gdprCookieNotice({
                statement: '<?= $policy_url ?>',
                locale: '<?= substr(get_bloginfo('language'), 0, 2) ?>',
                alwaysHide: true,
                analytics: ['gtag'],
                optOut: ['analytics']
                });
                document.addEventListener('gdprCookiesEvent', function (e) {
                    if (e.detail.marketing) {
                        console.log('marketing scripts loaded');
                    }
                    if (e.detail.analytics) {
                        console.log('analytics scripts loaded');

                    }
                });
            </script>

        <?php
        }
    }


    public function save()
    {
        if (!empty($_POST) && !empty($_POST[$this->prefix]) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], $this->prefix)) {
            $valid_inputs = array_map(function ($item) {
                return $item['name'];
            }, $this->fields);

            foreach ($_POST[$this->prefix] as $key => $value) {
                $this->update_option($key, esc_attr($value));
            }
        }
    }
}

add_action('init', function () {
    new GDPRCookieNotice();
});
