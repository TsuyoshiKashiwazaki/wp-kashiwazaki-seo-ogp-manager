# Changelog

All notable changes to Kashiwazaki SEO OGP Manager will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-13

### Added
- 初回リリース
- 投稿タイプごとのOGP設定制御機能
- 個別投稿でのカスタムOGP設定（タイトル、説明、画像、タイプ）
- OGP画像の自動選択機能（アイキャッチ → コンテンツ内画像 → デフォルト画像）
- OGP画像のwidth/height自動出力機能
- Twitter Card対応（summary / summary_large_image）
- article:タグの動的出力（og:typeがarticleの場合）
- Robots Metaタグ（max-image-preview:large）の出力機能
- Robots Metaタグの重複チェック機能（管理画面でワンクリックチェック）
- Facebook App ID設定
- デフォルト画像設定
- 投稿タイプの「すべて選択」「すべて解除」ボタン
- グローバルメニュー対応（メニュー位置81、dashicons-shareアイコン）
- 完全日本語化された管理画面
- エラーメッセージの日本語化
- 設定項目への詳細な説明文追加

### Fixed
- 投稿タイプフィルタリングの実装
- article:タグの出力条件（is_singular('post')からog:type判定に変更）
- デフォルトOGタイプの統一（articleからwebsiteに変更）
- URL取得方法の改善（$_SERVER['REQUEST_URI']からグローバル$wp使用に変更）
- 設定保存時のエラー重複表示を解消
- プラグイン一覧の「設定」リンク重複を解消
- 日本語ファイル名画像のURLエンコード処理を追加（Facebook OGPデバッガー対応）

### Improved
- 画像拡張子検証を共通メソッド化（DRY原則の適用）
- セキュリティ強化（URL取得方法の改善）
- コードの保守性向上（重複コードの削減）
- ユーザビリティ向上（日本語化、説明文追加）
