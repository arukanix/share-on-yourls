<?php
/*
Plugin Name: Share on YOURLS
Description: YOURLSで、記事を簡単に共有できるようにするプラグインです。
Version: 1.0.0
Author: oimo
*/

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'set_menu');
function set_menu() {
    add_options_page(
        'YOURLS APIキー設定',
        'YOURLS APIキー設定',
        'manage_options',
        'yourls-apikey-settings',
        'render_page'
    );
}

add_action('admin_init', 'settings_init');
function settings_init() {
    register_setting('yourls_settings_group', 'yourls_apikey', array(
        'sanitize_callback' => 'sanitize_text_field',
    ));
    register_setting('yourls_settings_group', 'yourls_url', array(
        'sanitize_callback' => 'esc_url_raw', // URL用にサニタイズを最適化
    ));
}

function render_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>YOURLS APIキー設定</h1>

        <?php settings_errors(); ?>

        <form method="post" action="options.php">
            <?php settings_fields('yourls_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="yourls_apikey">APIキー</label></th>
                        <td>
                            <input
                                name="yourls_apikey"
                                type="password"
                                id="yourls_apikey"
                                value="<?php echo esc_attr(get_option('yourls_apikey')); ?>"
                                class="regular-text"
                                placeholder="YOURLS APIキー"
                            >
                            <p class="description">YOURLSで取得したAPIキーを入力してください</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="yourls_url">サイトURL</label></th>
                        <td>
                            <input
                                name="yourls_url"
                                type="url"
                                id="yourls_url"
                                value="<?php echo esc_url(get_option('yourls_url')); ?>"
                                class="regular-text"
                                placeholder="https://example.com"
                            >
                            <p class="description">YOURLSのAPIエンドポイントURLと/yourls-api.php（例: `https://your-domain.com/yourls-api.php`）を入力してください。</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button('設定を保存'); ?>
        </form>
    </div>
    <?php
}

function add_shortify_popup($content) {
    // 単一の記事ページ（投稿）のみ対象
    if (is_singular('post')) {
        $html_path = plugin_dir_path(__FILE__) . 'template.html';
        if (file_exists($html_path)) {
            $template = file_get_contents($html_path);
            $post_id = get_the_ID();

            // すでに保存されている短縮URLを取得
            $short_url = get_post_meta($post_id, 'yourls_short_url', true);

            // 保存されていない場合のみAPIに問い合わせる（負荷軽減・キャッシュ化）
            if (empty($short_url)){
                $apikey = get_option('yourls_apikey');
                $yourls_url = get_option('yourls_url');
                $page_url = get_permalink();

                if (!empty($apikey) && !empty($yourls_url)) {
                    $data = [
                        'signature' => $apikey,
                        'action'    => 'shorturl',
                        'url'       => $page_url,
                        'format'    => 'json'
                    ];

                    // WordPress推奨の安全なHTTPリクエスト関数を使用
                    $response = wp_remote_post($yourls_url, [
                        'body' => $data,
                    ]);

                    if (!is_wp_error($response)) {
                        $body = wp_remote_retrieve_body($response);
                        $result = json_decode($body, true);

                        // 正常に短縮URLが返ってきたかチェック
                        if (isset($result['shorturl'])) {
                            $short_url = $result['shorturl'];
                            // 次回以降のためにデータベースに保存
                            update_post_meta($post_id, 'yourls_short_url', $short_url);
                        }
                    }
                }
            }

            // 短縮URLが正常に取得できている場合のみテンプレートを置換して結合
            if (!empty($short_url)) {
                $template = str_replace('{{SHORT_URL}}', esc_url($short_url), $template);
                $content .= $template;
            }
        }
    }
    return $content;
}
add_filter('the_content', 'add_shortify_popup');

// 1. 投稿一覧に「短縮URL」の列（カラム）を追加する
add_filter('manage_posts_columns', 'add_yourls_url_column');
function add_yourls_url_column($columns) {
    // 列の名前を「短縮URL」として追加
    $columns['yourls_short_url'] = '短縮URL';
    return $columns;
}

// 2. 追加した列に、データベースから取得した短縮URLを表示する
add_action('manage_posts_custom_column', 'display_yourls_url_column', 10, 2);
function display_yourls_url_column($column, $post_id) {
    if ($column === 'yourls_short_url') {
        // データベースから該当記事の短縮URLを取得
        $short_url = get_post_meta($post_id, 'yourls_short_url', true);

        if (!empty($short_url)) {
            // URLを表示し、クリックして別タブで開けるようにリンク化
            echo '<a href="' . esc_url($short_url) . '" target="_blank" rel="noopener">' . esc_html($short_url) . '</a>';
        } else {
            // まだAPI通信が行われておらず、短縮URLがない場合の表示
            echo '<span style="color: #999; font-style: italic;">未生成（ページ表示時に生成されます）</span>';
        }
    }
}
