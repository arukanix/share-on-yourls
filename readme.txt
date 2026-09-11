=== Share on YOURLS ===
Contributors: oimo
Tags: yourls, short url, url shortener, share, post
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://gnu.org

WordPressの投稿ページから、自作の短縮URLサーバー「YOURLS」のAPIを利用して自動で短縮URLを生成・共有できるようにするプラグインです。

== Description ==

『Share on YOURLS』は、WordPressの記事ごとに自動で短縮URLを発行・保存し、一覧や公開ページに組み込むことができる軽量なプラグインです。

= 主な特徴 =
* **短縮URLの自動生成**: 投稿が表示されたタイミングで、YOURLS API経由で自動的に短縮URLを発行します。
* **データベースキャッシュ**: 一度生成した短縮URLはカスタムフィールド（メタデータ）に保存されるため、2回目以降のアクセスで無駄なAPI通信（負荷）が発生しません。
* **管理画面での一覧表示**: 投稿一覧画面に「短縮URL」カラムが追加され、生成済みのURLをひと目で確認・アクセスできます。
* **安全な設定画面**: 管理画面からいつでもAPIキーとYOURLSのサイトURLを変更・保存できます。

== Installation ==

1. WordPress管理画面の「プラグイン ＞ 新規追加 ＞ プラグインのアップロード」へ移動します。
2. 本プラグインのZIPファイル（`share-on-yourls.zip`）を選択してインストールします。
3. インストール完了後、プラグインを「有効化」します。
4. WordPress管理画面の「設定 ＞ YOURLS APIキー設定」に移動します。
5. お使いのYOURLSサーバーから取得した「APIキー（Signature）」と「サイトURL」を入力して保存します。

== Frequently Asked Questions ==

= 短縮URLが生成されません =
設定画面で「APIキー」と「サイトURL」が正しく入力されているか確認してください。また、プラグイン有効化後に該当の投稿ページを一度ブラウザで閲覧（表示）させることで、バックグラウンドで自動的に初期生成が行われます。

= どのページでも短縮URLは作られますか？ =
現バージョンでは、通常の「投稿（post）」の単一記事ページのみが自動生成の対象となっています。固定ページやカスタム投稿タイプは対象外です。

== Changelog ==

= 1.0.0 =
* 初回リリース。YOURLS APIとの連携、自動生成、カスタムフィールドへのキャッシュ、投稿一覧へのカラム追加機能を実装。
