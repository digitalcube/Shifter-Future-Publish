# Shifter Future Publish

WordPress で記事の公開日を未来日に設定した状態でステータスを「公開」にできるプラグインです。Shifter の静的サイト生成（アーティファクト）で未来日の記事を含めることができます。

## 概要

WordPress のデフォルト動作では、未来日を設定した記事は「予約投稿」（future）ステータスになり、指定した日時まで公開されません。このプラグインを使用すると、未来日を設定した記事でも即座に「公開」（publish）ステータスとなり、サイト上で表示されます。

これは特に Shifter の静的サイト生成において有用です。Shifter のアーティファクト生成は「公開」ステータスの記事のみを対象とするため、このプラグインを使用することで未来日の記事もアーティファクトに含めることができます。

## 機能

- 未来日を設定した記事を即座に「公開」ステータスで保存
- 対象となる投稿タイプを設定画面で選択可能
- カスタム投稿タイプにも対応
- シンプルで効率的なアーキテクチャ

## 動作の仕組み

このプラグインはシンプルな2層アーキテクチャで、未来日の投稿が確実に「公開」として扱われるようにします：

1. **投稿保存時のインターセプト（メイン）** - `wp_insert_post_data` フィルターを使用して、未来日の投稿を保存する際、データベースに保存される前にステータスを「future」から「publish」に変更します。

2. **Future Post フック（フォールバック）** - 投稿タイプ別のフック（`future_{post_type}`）を使用して、エッジケースに対応します。

## インストール方法

1. `shifter-future-publish` フォルダを `/wp-content/plugins/` ディレクトリにアップロード
2. WordPress 管理画面の「プラグイン」メニューからプラグインを有効化
3. 「設定」>「Shifter Future Publish」で設定を行う
4. 対象となる投稿タイプを選択して保存

## 設定

### プラグインの有効化/無効化

設定画面で「Enable future date publishing」チェックボックスをオン/オフすることで、プラグインの機能を有効化/無効化できます。

### 投稿タイプの選択

「Post Types」セクションで、未来日公開を有効にする投稿タイプを選択できます。デフォルトでは「投稿」（post）のみが選択されています。

カスタム投稿タイプを含む、`public => true` で登録されたすべての投稿タイプが設定画面に表示されます。

## 使用例

### イベントサイト

イベントの開催日を投稿の公開日として設定し、イベント情報を事前に公開したい場合に便利です。

### ニュースサイト

将来の公開日を設定しつつ、記事を即座に表示したい場合に使用できます。

### Shifter での静的サイト生成

Shifter のアーティファクト生成で未来日の記事を含めたい場合に最適です。

## よくある質問

### 既存の予約投稿に影響はありますか？

いいえ、このプラグインはプラグイン有効化後に保存された投稿にのみ影響します。既存の予約投稿はそのまま予約状態を維持します。

### プラグインを無効化するとどうなりますか？

プラグインを無効化すると、WordPress はデフォルトの動作に戻ります。新しい未来日投稿は通常通り予約投稿となります。プラグイン有効時に公開された未来日投稿は、公開状態のまま維持されます。

### 管理画面での表示はどうなりますか？

管理画面では、投稿一覧で実際のステータスを確認できるよう、フィルターをスキップしています。これにより、管理者は未来日投稿の状態を正確に把握できます。

## 動作要件

- WordPress 6.0 以上
- PHP 8.1 以上

## 変更履歴

### 2.1.3

* refactor: Delete root Composer files and PHPStan config, and update the CI workflow to execute Composer and static analysis within the `_tests` directory. by @tekapo in https://github.com/digitalcube/Shifter-Future-Publish/pull/19

**Full Changelog**: https://github.com/digitalcube/Shifter-Future-Publish/compare/v2.1.2...v2.1.3
### 2.1.2

## What's Changed
* feat: Auto-update changelog from GitHub Release Notes by @devin-ai-integration[bot] in https://github.com/digitalcube/Shifter-Future-Publish/pull/17


**Full Changelog**: https://github.com/digitalcube/Shifter-Future-Publish/compare/v2.1.1...v2.1.2
### 2.1.0

- コードベースの大幅な簡略化（5層→2層アーキテクチャ）
- 冗長なフォールバック機構を削除（get_post_status、the_posts、posts_where フィルター）
- JavaScript ファイルの簡略化（editor.js: 76%削減、classic-editor.js: 75%削減）
- PHPStan（level max）対応 - 型安全性の向上
- PHPCS（WordPress コーディング規約）対応
- 全クラス・メソッドにPHPDocコメントを追加
- `_future_post_hook()` の呼び出しを修正（WordPress API準拠）

### 2.0.5

- while文の代入構文を明確化
- 複数イベントリスナー登録の防止
- ボタンテキストのi18n対応を追加
- setIntervalを1秒から3秒に変更（パフォーマンス改善）
- 日付比較ロジックをparseIntで適切にパース
- PHP enqueue関数の不要な条件分岐を削除
- 未使用のwp-element依存を削除
- publish_future_post_now関数にエラーログを追加

### 2.0.4

- クラシックエディタ対応 - 「予約投稿」ボタンを「公開」に変更する機能を追加
- GutenbergとクラシックエディタでボタンテキストのUI動作を統一

### 2.0.3

- ブロックエディタで「予約投稿」ボタンを「公開」に変更する機能を追加
- 有効な投稿タイプでのGutenbergエディタのユーザー体験を改善

### 2.0.2

- SELECT * を明示的なカラムリストに変更（セキュリティ向上）
- is_single プロパティアクセスを is_single() メソッド呼び出しに変更
- WP_Post オブジェクトの直接変更を避けるためクローンを使用
- preg_replace のエラーハンドリングを ?? から ?: 演算子に変更
- stdClass を WP_Post に適切に変換

### 2.0.1

- WordPress API 呼び出しから名前付き引数を削除（WordPress コア関数では未サポート）

### 2.0.0

- PHP 8.0 以上が必須 - PHP 8 機能を使用した完全な書き直し
- strict types 宣言の追加
- 型付きプロパティと戻り値型の全面的な使用
- フックコールバックにファーストクラス callable 構文を使用
- match 式によるクリーンな条件分岐
- union 型による型安全性の向上
- null 合体代入演算子の使用
- WordPress API 呼び出しに名前付き引数を使用
- シンプルなコールバックにアロー関数を使用
- クラスを final として宣言しカプセル化を強化
- ジェネリクスを使用した PHPDoc アノテーションの改善

### 1.2.0

- show_future_posts() のバグ修正（クエリ再実行時に未来日投稿が含まれない問題）
- get_post_status フィルターで管理画面をスキップするよう改善
- SQL WHERE 句の修正パターンを拡張
- 定数定義の安全性向上（再定義エラー防止）
- 型チェックの改善

### 1.1.0

- 複数レイヤーによる未来日投稿処理を追加
- 投稿タイプ別フック（future_{post_type}）のサポート
- get_post_status フィルターの追加
- the_posts フィルターによる 404 防止
- posts_where フィルターによるアーカイブクエリ対応
- 設定画面に「How it works」セクションを追加

### 1.0.0

- 初回リリース
- 未来日投稿の公開機能
- 管理画面での投稿タイプ設定

## ライセンス

GPL-2.0+

## 作者

DigitalCube

## 関連リンク

- [Shifter](https://www.getshifter.io/)
- [DigitalCube](https://developer.getshifter.io/)
