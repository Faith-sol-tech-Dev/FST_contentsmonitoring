<?php
namespace ContentsMonitor\Common;

/**
 * 監視サイトで使用するメッセージクラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class MessageClass
{
	public $HTTP_ERROR_MESSAGE = array(
							'1' => 'ログインの入力内容に誤りがあります。',
							'2' => 'ログイン済みの状態を確認できませんでした。ログインされていません。',
							'3' => '有効なトークンがありませんでした。',
							'4' => 'ログイン情報を確認中にエラーが発生しました。ユーザデータ、トークンデータに損傷がないか確認してください。(ユーザID：%s／ログインID:%s／トークン：%s)',
							'5' => 'セッションにユーザ情報がありません。',
							'6' => 'セッションにユーザIDがありません。',
							'7' => 'セッションにログインIDがありません。',
							'8' => 'トークン情報に誤りがあります。認証に失敗しました。',
							'9' => 'トークンのインデックスキーがありませんでした。',
							'10' => 'トークンを削除できませんでした。(ユーザID：%s／ログインID:%s／トークン：%s)',
							'11' => 'アクセス権限がありません。システム管理者へご連絡ください。',
							'12' => 'クッキーを有効にしてアクセスしてください。',
							'101' => '入力内容に誤りがあります。',
							'102' => '不正なURLです。(コンテンツIDがありませんでした。パラメータを確認してください。)',
							'103' => '不正なURLです。(バッチ処理Noがありませんでした。パラメータを確認してください。)',
							'111' => 'ログインIDは必須入力です。',
							'112' => '1文字以上で入力してください。',
							'113' => '10文字以内で入力してください。',
							'114' => 'パスワードは必須入力です。',
							'115' => '1文字以上で入力してください。',
							'116' => '20文字以内で入力してください。',
							'500' => 'アプリケーションエラーが発生しました。エラーログの方をご確認ください。'
	);

	public $HTTP_DISPLAY_MESSAGE = array(
							's_none' => 'サービスを選択してから、コンテンツ検索・集計画面へ遷移してください。',
							's_noset' => 'サービス設定がされていません。システム管理者にサービス設定するよう連絡してください。',
							'content_result_none' => '検索結果データはありません。',
							'content_result_no_post' => '検索条件を受け取れませんでした。再度、検索項目をセットしてください。',
							'content_search_encode' => 'エンコードされていません。',
							'content_search_encode_title' => '%sはエンコードされていません。',
							'content_search_required' => '必須項目に値がありません。',
							'content_search_required_title' => '必須項目 (%s) に値がありません。',
							'content_search_control' => '制御文字が含まれています。',
							'content_search_control_title' => '項目 (%s) に制御文字が含まれています。',
							'content_search_numeric' => '数値以外の文字が含まれています。',
							'content_search_numeric_title' => '項目 (%s) に数値以外の文字が含まれています。',
							'content_search_mail' => 'メールアドレスに誤りがあります。',
							'content_search_mail_title' => '項目 (%s) のメールアドレスに誤りがあります。',
							'content_search_pass' => 'パスワードは英数字で作成してください。',
							'content_search_pass_title' => 'パスワードは英数字で作成してください。 (%s) ',
							'content_search_date' => '日付に誤りがあります。。',
							'content_search_date_title' => '日付に誤りがあります。 (%s) ',
							'content_search_kana' => 'カナ以外の文字が含まれています。',
							'content_search_kana_title' => '項目 (%s) にカナ以外の文字が含まれています。 ',
							'content_search_string' => '入力内容に誤りがあります。',
							'content_search_string_title' => '項目 (%s) は入力内容に誤りがあります。',
							'content_search_url' => 'URLに誤りがあります。',
							'content_search_url_title' => '項目 (%s) はURLに誤りがあります。',
							'content_search_length_min' => '対象の文字列が、指定の最小長を下回っています。',
							'content_search_length_min_item' => '対象の文字列が、指定の最小長を下回っています。(%s,%s)',
							'content_search_length_max' => '対象の文字列が、指定の最大長を上回っています。',
							'content_search_length_max_item' => '対象の文字列が、指定の最大長を上回っています。(%s,%s)',
							'update_success' => '更新しました。',
							'update_fail' => '更新に失敗しました。',
			'batch_log_nodata' => 'バッチ情報がありません。取込処理中の可能性があります、バッチ稼働状況を確認してください。',
				
	);
	

}
