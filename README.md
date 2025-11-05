# Kashiwazaki SEO OGP Manager

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.0.0--dev-orange.svg)](https://github.com/TsuyoshiKashiwazaki/wp-kashiwazaki-seo-ogp-manager/releases)

SEO対策とSNSシェアを最適化する高機能OGP管理プラグイン。投稿ごとのOGP設定、Twitter Cards対応、プレビュー機能を搭載。

**特徴**: 投稿タイプごとに細かく制御でき、OGP画像のwidth/heightも自動出力。カスタム投稿タイプにも完全対応。

## 主な機能

- 投稿タイプごとのOGP設定制御
- 個別投稿でのカスタムOGP設定（タイトル、説明、画像、タイプ）
- OGP画像の自動選択（アイキャッチ画像 → デフォルト画像）
- 画像サイズ（width/height）の自動出力
- Twitter Card対応（summary / summary_large_image）
- article:タグの自動出力（記事タイプの場合）
- Robots Metaタグ（max-image-preview:large）の出力とON/OFF制御
- Robots Metaタグの重複チェック機能
- Facebook App ID対応
- デフォルト画像設定
- 投稿タイプの一括選択機能
- 日本語化済み管理画面

## クイックスタート

### インストール

1. このリポジトリをクローンまたはダウンロード
2. `kashiwazaki-seo-ogp-manager` フォルダを `/wp-content/plugins/` にアップロード
3. WordPressの管理画面からプラグインを有効化
4. 管理画面の「Kashiwazaki SEO OGP Manager」から設定

### 基本設定

1. **基本設定**
   - OGPの有効化
   - Twitter Cardの有効化
   - サイト名の設定
   - デフォルトOGタイプの選択

2. **ソーシャルメディア設定**
   - Facebook App ID（任意）
   - Twitterユーザー名

3. **画像設定**
   - デフォルトOGP画像のアップロード（推奨サイズ: 1200x630px）

4. **投稿タイプ設定**
   - OGPを有効化する投稿タイプを選択
   - 「すべて選択」「すべて解除」ボタンで一括設定可能

5. **Robots Meta設定**
   - max-image-preview:large タグの有効化
   - 重複チェック機能で他のプラグインとの競合を確認

## 使い方

### 個別投稿での設定

各投稿・固定ページの編集画面に「OGP Settings」メタボックスが表示されます：

- **OG Title**: OGPタイトル（空欄の場合は投稿タイトルを使用）
- **OG Description**: OGP説明文（空欄の場合は抜粋またはコンテンツから自動生成）
- **OG Image**: OGP画像URL（空欄の場合はアイキャッチ画像を使用）
- **OG Type**: コンテンツタイプ（article / website / blog / product / video）
- **Twitter Card Type**: Twitterカードタイプ（summary / summary_large_image）

### OGPタグの優先順位

1. **個別投稿のカスタム設定**（最優先）
2. **自動判定**
   - タイトル: 投稿タイトル
   - 説明: 投稿抜粋 → コンテンツから自動生成
   - 画像: アイキャッチ画像 → デフォルト画像
   - タイプ: 標準投稿は「article」、その他はデフォルト設定
3. **デフォルト設定**

## 技術仕様

### システム要件

- WordPress 6.0以上
- PHP 7.4以上

### 出力されるメタタグ

Robots Meta（オプション）:
- `<meta name="robots" content="max-image-preview:large">` - Google検索結果での画像プレビュー最適化

OGP基本タグ:
- `og:site_name`
- `og:locale`
- `og:type`
- `og:title`
- `og:description`
- `og:url`
- `og:image`
- `og:image:width`（自動取得）
- `og:image:height`（自動取得）

記事タイプの場合は追加:
- `article:published_time`
- `article:modified_time`
- `article:author`
- `article:section`
- `article:tag`

Twitter Card:
- `twitter:card`
- `twitter:title`
- `twitter:description`
- `twitter:image`
- `twitter:site`（設定時）

### フィルターフック

プラグインは以下のフィルターフックを提供：

- `ksom_ogp_tags` - OGPタグのカスタマイズ
- `ksom_twitter_card_tags` - Twitter Cardタグのカスタマイズ
- `ksom_og_image` - OGP画像URLのカスタマイズ
- `ksom_og_image_dimensions` - 画像サイズのカスタマイズ
- `ksom_valid_image_extensions` - 有効な画像拡張子のカスタマイズ

## 更新履歴

### 1.0.0 (2025-01-13)
- 初回リリース
- 投稿タイプごとのOGP制御機能
- OGP画像width/height自動出力
- article:タグの動的出力
- Robots Meta（max-image-preview:large）出力機能
- Robots Metaタグの重複チェック機能
- 投稿タイプ一括選択機能
- 管理画面の日本語化
- グローバルメニュー対応（位置81、dashicons-shareアイコン）

## ライセンス

GPL-2.0-or-later

## サポート・開発者

**開発者**: 柏崎剛 (Tsuyoshi Kashiwazaki)  
**ウェブサイト**: https://www.tsuyoshikashiwazaki.jp/  
**サポート**: プラグインに関するご質問や不具合報告は、開発者ウェブサイトまでお問い合わせください。

## 貢献

バグ報告や機能リクエストは、GitHubのIssuesページからお願いします。

プルリクエストも歓迎します：
1. このリポジトリをフォーク
2. 機能ブランチを作成 (`git checkout -b feature/amazing-feature`)
3. 変更をコミット (`git commit -m 'Add amazing feature'`)
4. ブランチにプッシュ (`git push origin feature/amazing-feature`)
5. プルリクエストを作成

---

<div align="center">

**Keywords**: WordPress, OGP, Open Graph Protocol, Twitter Card, SEO, Social Media, Meta Tags, SNS

Made by [Tsuyoshi Kashiwazaki](https://github.com/TsuyoshiKashiwazaki)

</div>
