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
            'name' => 'ga-id',
            'label' => 'Google Analytics ID',
            'type' => 'input'
        ],
        [
            'name' => 'px-id',
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

    public function update_option($key, $value)
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
        $iso_locale = substr(get_bloginfo('language'), 0, 2);
        $policy_url = $this->get_option('policy-url');
        $ga_id = $this->get_option('ga-id');
        $px_id = $this->get_option('px-id');

        $description = $this->get_option('description');
        $cookie_essential_desc = $this->get_option('cookie_essential_desc');
        $cookie_performance_desc = $this->get_option('cookie_performance_desc');
        $cookie_analytics_desc = $this->get_option('cookie_analytics_desc');
        $cookie_marketing_desc = $this->get_option('cookie_marketing_desc');



        if ($policy_url) {
            ?>
            <script type="text/javascript">
                <?= ($description) ? "gdprCookieNoticeLocales.".$iso_locale.".description = '".$description."';" : '' ?>
                <?= ($cookie_essential_desc) ? "gdprCookieNoticeLocales.".$iso_locale.".cookie_essential_desc = '".$cookie_essential_desc."';" : '' ?>
                <?= ($cookie_performance_desc) ? "gdprCookieNoticeLocales.".$iso_locale.".cookie_performance_desc = '".$cookie_performance_desc."';" : '' ?>
                <?= ($cookie_analytics_desc) ? "gdprCookieNoticeLocales.".$iso_locale.".cookie_analytics_desc = '".$cookie_analytics_desc."';" : '' ?>
                <?= ($cookie_marketing_desc) ? "gdprCookieNoticeLocales.".$iso_locale.".cookie_marketing_desc = '".$cookie_marketing_desc."';" : '' ?>

                gdprCookieNotice({
                    statement: '<?= $policy_url ?>',
                    locale: '<?= $iso_locale ?>',
                    alwaysHide: true,
                    <?= ($ga_id) ? "analytics: ['ga']," : '' ?>
                    <?= ($px_id) ? "marketing: ['px']," : '' ?>
                    optOut: ['analytics']
                });
                document.addEventListener('gdprCookiesEvent', function (e) {
                    <?php if ($px_id) : ?>
                    if (e.detail.marketing) {
                        console.log('marketing scripts enabled');
                        !function(f,b,e,v,n,t,s)
                        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                        n.queue=[];t=b.createElement(e);t.async=!0;
                        t.src=v;s=b.getElementsByTagName(e)[0];
                        s.parentNode.insertBefore(t,s)}(window, document,'script',
                        'https://connect.facebook.net/en_US/fbevents.js');
                        fbq('init', '<?= $px_id ?>');
                        fbq('track', 'PageView');
                    } else {
                        console.log('marketing scripts disabled');
                    }
                    <?php endif; ?>

                    <?php if ($ga_id) : ?>
                    if (e.detail.analytics === true) {
                        var gtagScript = document.createElement("script");
                            gtagScript.type = "text/javascript";
                            gtagScript.setAttribute("async", "true");
                            gtagScript.setAttribute("src", "https://www.googletagmanager.com/gtag/js?id=<?= $ga_id ?>");
                            document.documentElement.firstChild.appendChild(gtagScript);
                        window.dataLayer = window.dataLayer || [];
                        function gtag() { dataLayer.push(arguments); }
                        gtag('js', new Date());
                        gtag('config', '<?= $ga_id ?>', { 'anonymize_ip': true });
                        console.log('analytics scripts enabled');
                    } else {
                        window['ga-disable-<?= $ga_id ?>'] = true;
                        console.log('analytics scripts disabled');
                    }
                    <?php endif; ?>
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
