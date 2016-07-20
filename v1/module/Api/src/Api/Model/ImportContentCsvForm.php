<?php

namespace Api\Model;

use Exception;
use SplFileObject;

use Zend\Http\Response;

use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\RequestClass as RequestClass;
use ContentsMonitor\Common\ValidationClass as Validation;

/**
 * CSV取得フォーム
 *
 * @package ContentsMonitor
 * @author  Verso Yanagi
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class ImportContentCsvForm extends CommonForm
{
	/**** CSVファイル操作 start ****/
	/**
	 * CSV読み込み関数
	 * @return array  $items	 読み込んだCSVデータ
	 */
	protected function readCsv()
	{
		Log::batch(__FILE__, __LINE__, 'Debug::readCsv() Start.');
		// CSV形式
		$csv_basic = array(
			CSV_COLMON_SERVICE_CODE, 
			CSV_COLMON_START_DATE, 
			CSV_COLMON_END_DATE,
			CSV_COLMON_TYPE,
			CSV_COLMON_ID,
			CSV_COLMON_URL,
			CSV_COLMON_FORMAT,
			CSV_COLMON_COMMENT,
			CSV_COLMON_TITLE,
			CSV_COLMON_USER,
			CSV_COLMON_CAPTION,
			CSV_COLMON_CREAT_DATE,
			CSV_COLMON_IMAGE_LIST
		);
		$csv_movie_option = array(
			CSV_COLMON_IMAGE_ID, 
			CSV_COLMON_IMAGE_URL,
			CSV_COLMON_IMAGE_CREAT_DATE
		);
		
		// CSVファイルを読み込み
		$items = array();
		try {
			$temp = tmpfile();
			if ($temp === false) {
				throw new \Exception('tmpfile error.', Response::STATUS_CODE_500);
			}
			if (fwrite($temp, $this->responseApi) === false) {
				throw new \Exception('fwrite error.', Response::STATUS_CODE_500);
			}
			if (rewind($temp) === false) {
				throw new \Exception('rewind error.', Response::STATUS_CODE_500);
			}
			$meta = stream_get_meta_data($temp);
			$file = new SplFileObject($meta['uri'], 'r');
			
			$file->setFlags(
				SplFileObject::DROP_NEW_LINE |  /* 行末の改行無視 */
				SplFileObject::READ_CSV			/* CSVとして読み込み */
			);
			
			foreach ($file as $lkey => $line) {
				// 空行
				if (is_null($line[0])) {
					continue;
				}
				
				$strs_basic = null;		 // CSV情報
				$strs_movie_option = null;  // movieオプション情報
				$cnt_movie_option = 0;	  // movieオプション数カウント
				$cnt = 0;				   // $csv_movie_optionの値を取得するためのカウント
				$cnt_empty = 0;			 // movieオプションの全空白データを除くためのカウント
				foreach ($line as $skey => $str) {
					// 文字列の先頭および末尾にあるホワイトスペースを除去
					$str = trim($str);
					// データを格納
					if ($skey < count($csv_basic)-1 ) {
						// CSV基本情報 ImageListにはデータを入れないので、$csv_basic要素数-1
						$strs_basic[$csv_basic[$skey]] = $str;
					} elseif ($strs_basic[CSV_COLMON_TYPE] == CONTENTS_TYPE_MV_NAME) {
						// Movieオプション
						$strs_movie_option[$csv_movie_option[$cnt]] = $str;
						if (empty($str)) {
							$cnt_empty++;
						}
						$cnt++;
						if ($cnt == 3) {
							if ($cnt_empty < count($csv_movie_option)) {
								$strs_basic[CSV_COLMON_IMAGE_LIST][$cnt_movie_option] = $strs_movie_option;
								$cnt_movie_option++;
							}
							$cnt = 0;
						}
					}
				}
				// 要素数が足りない
				if ($cnt != 0) {
					//err
				}
				$items[] = $strs_basic;
			}
			fclose($temp);
			$file = null;
		} catch (\Exception $e) {
			if (isset($temp) && $temp !== false) {
				fclose($temp);
			}
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw $e;
		}
		Log::batch(__FILE__, __LINE__, 'Debug::readCsv() End.');
		return $items;
	}
	
	/**
	 * CSVデータをバリデートチェック（DB用）
	 * @param $item_val CSVデータ（1行分）
	 */
	protected function chk_csv_data_db($item_val)
	{
		Log::batch(__FILE__, __LINE__, 'Debug::chk_csv_data_db() Start.');
		// 引数チェック
		if (is_null($item_val)) {
			throw new Exception('$item_val is null');
		}
		
		$req = array();
		
		try {
			// サービスコード
			$req[CSV_COLMON_SERVICE_CODE] = Validation::validateApi($item_val, CSV_COLMON_SERVICE_CODE, false, 0, null, null);
			if (is_null($req[CSV_COLMON_SERVICE_CODE])) {
				return false;
			}
			
			// コンテンツの取得開始日時
			$req[CSV_COLMON_START_DATE] = Validation::validateApi($item_val, CSV_COLMON_START_DATE, false, 2, null, null);
			if (is_null($req[CSV_COLMON_START_DATE])) {
				return false;
			}
			
			// コンテンツの取得終了日時
			$req[CSV_COLMON_END_DATE] = Validation::validateApi($item_val, CSV_COLMON_END_DATE, false, 2, null, null);
			if (is_null($req[CSV_COLMON_END_DATE])) {
				return false;
			}
			
			// コンテンツ種別判別
			$req[CSV_COLMON_TYPE] = Validation::validateApi($item_val, CSV_COLMON_TYPE, true, 0, null, null);
			if (is_null($req[CSV_COLMON_TYPE])) {
				return false;
			}
			if (!in_array($req[CSV_COLMON_TYPE], $this->contents_types)) {
				return false;
			}
			
			// コンテンツ内部ID（お客様環境）
			$req[CSV_COLMON_ID] = Validation::validateApi($item_val, CSV_COLMON_ID, false, 0, null, 10);
			if (is_null($req[CSV_COLMON_ID])) {
				return false;
			}
			
			// コンテンツ - タイトル
			$req[CSV_COLMON_TITLE] = Validation::validateApi($item_val, CSV_COLMON_TITLE, false, 0, null, 100);
			if (is_null($req[CSV_COLMON_TITLE])) {
				return false;
			}
			
			// コンテンツ - 名前
			$req[CSV_COLMON_USER] = Validation::validateApi($item_val, CSV_COLMON_USER, false, 0, null, 50);
			if (is_null($req[CSV_COLMON_USER])) {
				return false;
			}
			
			// コンテンツ作成日時
			$req[CSV_COLMON_CREAT_DATE] = Validation::validateApi($item_val, CSV_COLMON_CREAT_DATE, false, 2, null, 19);
			if (is_null($req[CSV_COLMON_CREAT_DATE])) {
				return false;
			}
			
			// キャプションデータ
			$req[CSV_COLMON_CAPTION] = Validation::validateApi($item_val, CSV_COLMON_CAPTION, false, 0, null, 100);
			if (is_null($req[CSV_COLMON_CAPTION])) {
				return false;
			}
			
			// コンテンツ種別別の必要なデータを取得
			switch ($req[CSV_COLMON_TYPE]) {
				case CONTENTS_TYPE_MV_NAME :	// 動画
					// コンテンツ - フォーマット
					$req[CSV_COLMON_FORMAT] = Validation::validateApi($item_val, CSV_COLMON_FORMAT, false, 0, null, 10);
					if (is_null($req[CSV_COLMON_FORMAT])) {
						return false;
					}
					// コンテンツURL
					$req[CSV_COLMON_URL] = Validation::validateApi($item_val, CSV_COLMON_URL, false, 0, null, 200);
					if (is_null($req[CSV_COLMON_URL])) {
						return false;
					}
					// コンテンツ - コメント
					$req[CSV_COLMON_COMMENT] = Validation::validateApi($item_val, CSV_COLMON_COMMENT, false, 5, null, 1024);
					if (is_null($req[CSV_COLMON_COMMENT])) {
						return false;
					}
					// 動画のイメージ
					$req[CSV_COLMON_IMAGE_LIST] = RequestClass::arrayValue($item_val, CSV_COLMON_IMAGE_LIST, array(), true);
					foreach ($req[CSV_COLMON_IMAGE_LIST] as $image_key => $image_val) {
						// コンテンツ内部ID（お客様環境）
						$req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_ID] = Validation::validateApi($image_val, CSV_COLMON_IMAGE_ID, false, 0, null, 10);
						if (is_null($req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_ID])) {
							return false;
						}
						// コンテンツURL
						$req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_URL] = Validation::validateApi($image_val, CSV_COLMON_IMAGE_URL, false, 0, null, 200);
						if (is_null($req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_URL])) {
							return false;
						}
						// コンテンツ作成日時
						$req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_CREAT_DATE] = Validation::validateApi($image_val, CSV_COLMON_IMAGE_CREAT_DATE, false, 2, null, 19);
						if (is_null($req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_CREAT_DATE])) {
							return false;
						}
					}
					break;
				case CONTENTS_TYPE_IM_NAME :	// 画像
					// コンテンツ - フォーマット
					$req[CSV_COLMON_FORMAT] = Validation::validateApi($item_val, CSV_COLMON_FORMAT, false, 0, null, 10);
					if (is_null($req[CSV_COLMON_FORMAT])) {
						return false;
					}
					// コンテンツURL
					$req[CSV_COLMON_URL] = Validation::validateApi($item_val, CSV_COLMON_URL, false, 0, null, 200);
					if (is_null($req[CSV_COLMON_URL])) {
						return false;
					}
					// コンテンツ - コメント
					$req[CSV_COLMON_COMMENT] = Validation::validateApi($item_val, CSV_COLMON_COMMENT, false, 5, null, 1024);
					if (is_null($req[CSV_COLMON_COMMENT])) {
						return false;
					}
					break;
				case CONTENTS_TYPE_CT_NAME :	// コメント
					// コンテンツ - フォーマット
					$req[CSV_COLMON_FORMAT] = Validation::validateApi($item_val, CSV_COLMON_FORMAT, false, 0, null, 10);
					if (is_null($req[CSV_COLMON_FORMAT])) {
						return false;
					}
					// コンテンツURL
					$req[CSV_COLMON_URL] = Validation::validateApi($item_val, CSV_COLMON_URL, false, 0, null, 200);
					if (is_null($req[CSV_COLMON_URL])) {
						return false;
					}
					// コンテンツ - コメント
					$req[CSV_COLMON_COMMENT] = Validation::validateApi($item_val, CSV_COLMON_COMMENT, false, 5, null, 1024);
					if (is_null($req[CSV_COLMON_COMMENT])) {
						return false;
					}
					break;
				default :
					Log::emerg(__FILE__, __LINE__, CSV_COLMON_TYPE . $req[CSV_COLMON_TYPE]);
					return false;
			}
		} catch (\Exception $e) {
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw $e;
		}
		Log::batch(__FILE__, __LINE__, 'Debug::chk_csv_data_db() End.');
		return $req;
	}
	
	/**
	 * CSVデータのバリデータ（DBに起因しないエラー）
	 * Enter description here ...
	 * @param unknown_type $req
	 */
	protected function chk_csv_data($req)
	{
		Log::batch(__FILE__, __LINE__, 'Debug::chk_csv_data() Start.');
		// 引数チェック
		if (is_null($req)) {
			throw new Exception('$req is null');
		}
		
		try {
			// サービスコード
			$req[CSV_COLMON_SERVICE_CODE] = Validation::validateApi($req, CSV_COLMON_SERVICE_CODE, true, 0, null, null);
			if (is_null($req[CSV_COLMON_SERVICE_CODE])) {
				return false;
			}
			
			// コンテンツの取得開始日時
			$req[CSV_COLMON_START_DATE] = Validation::validateApi($req, CSV_COLMON_START_DATE, true, 2, null, null);
			if (is_null($req[CSV_COLMON_START_DATE])) {
				return false;
			}
			
			// コンテンツの取得終了日時
			$req[CSV_COLMON_END_DATE] = Validation::validateApi($req, CSV_COLMON_END_DATE, true, 2, null, null);
			if (is_null($req[CSV_COLMON_END_DATE])) {
				return false;
			}
			
			// コンテンツ内部ID（お客様環境）
			$req[CSV_COLMON_ID] = Validation::validateApi($req, CSV_COLMON_ID, true, 0, null, 10);
			if (is_null($req[CSV_COLMON_ID])) {
				return false;
			}
			
			// コンテンツ作成日時
			$req[CSV_COLMON_CREAT_DATE] = Validation::validateApi($req, CSV_COLMON_CREAT_DATE, true, 2, null, 19);
			if (is_null($req[CSV_COLMON_CREAT_DATE])) {
				return false;
			}
			
			// コンテンツ種別別の必要なデータを取得
			switch ($req[CSV_COLMON_TYPE]) {
				case CONTENTS_TYPE_MV_NAME :	// 動画
					// コンテンツ - フォーマット
					$req[CSV_COLMON_FORMAT] = Validation::validateApi($req, CSV_COLMON_FORMAT, true, 0, null, 10);
					if (is_null($req[CSV_COLMON_FORMAT])) {
						return false;
					}
					if (!in_array($req[CSV_COLMON_FORMAT], $this->format_types_movie)) {
						return false;
					}
					// コンテンツURL
					$req[CSV_COLMON_URL] = Validation::validateApi($req, CSV_COLMON_URL, true, 3, null, 200);
					if (is_null($req[CSV_COLMON_URL])) {
						return false;
					}
					// 動画のイメージ
					$req[CSV_COLMON_IMAGE_LIST] = RequestClass::arrayValue($req, CSV_COLMON_IMAGE_LIST, array(), true);
					foreach ($req[CSV_COLMON_IMAGE_LIST] as $image_key => $image_val) {
						// コンテンツ内部ID（お客様環境）
						$req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_ID] = Validation::validateApi($image_val, CSV_COLMON_IMAGE_ID, true, 0, null, 10);
						if (is_null($req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_ID])) {
							return false;
						}
						// コンテンツURL
						$req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_URL] = Validation::validateApi($image_val, CSV_COLMON_IMAGE_URL, true, 3, null, 200);
						if (is_null($req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_URL])) {
							return false;
						}
						// コンテンツ作成日時
						$req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_CREAT_DATE] = Validation::validateApi($image_val, CSV_COLMON_IMAGE_CREAT_DATE, true, 2, null, 19);
						if (is_null($req[CSV_COLMON_IMAGE_LIST][$image_key][CSV_COLMON_IMAGE_CREAT_DATE])) {
							return false;
						}
					}
					break;
				case CONTENTS_TYPE_IM_NAME :	// 画像
					// コンテンツ - フォーマット
					$req[CSV_COLMON_FORMAT] = Validation::validateApi($req, CSV_COLMON_FORMAT, true, 0, null, 10);
					if (is_null($req[CSV_COLMON_FORMAT])) {
						return false;
					}
					if (!in_array($req[CSV_COLMON_FORMAT], $this->format_types_image)) {
						return false;
					}
					// コンテンツURL
					$req[CSV_COLMON_URL] = Validation::validateApi($req, CSV_COLMON_URL, true, 3, null, 200);
					if (is_null($req[CSV_COLMON_URL])) {
						return false;
					}	
					break;
				case CONTENTS_TYPE_CT_NAME :	// コメント
					// コンテンツ - コメント
					$req[CSV_COLMON_COMMENT] = Validation::validateApi($req, CSV_COLMON_COMMENT, true, 0, null, 1024);
					if (is_null($req[CSV_COLMON_COMMENT])) {
						return false;
					}
					// コンテンツURL
					$req[CSV_COLMON_URL] = Validation::validateApi($req, CSV_COLMON_URL, false, 3, null, 200);
					if (is_null($req[CSV_COLMON_URL])) {
						return false;
					}
					break;
				default :
					return false;
			}
		} catch (\Exception $e) {
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw $e;
		}
		Log::batch(__FILE__, __LINE__, 'Debug::chk_csv_data() end.');
		return true;
	}
	/**** CSVファイル操作 end   ****/
	
	/**
	 * 取得データ取り込み
	 *
	 */
	private function importResponseApi()
	{
		Log::batch(__FILE__, __LINE__, 'Debug::importResponseApi() Start.');
		
		if ($this->responseApi === null) {
			$this->updateBatchLogDataState(API_CSV_NO_FILE, true);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_CSV_NO_FILE);
			return;
		}
		
		$this->updateBatchLogDataState(API_CSV_CHECK_CSV);
		Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_CSV_CHECK_CSV);
		
		// CSVデータ読み込み（リトライあり）
		$maxTryCount = $this->configApi['retry_count'];
		for ($tryCount = 0; $tryCount < $maxTryCount; $tryCount++) {
			$items = $this->readCsv();
			if ($items != false) {
				break;
			}
		}
		
		// リトライ失敗
		if ($maxTryCount <= $tryCount) {
			Log::batch(__FILE__, __LINE__, '');
			// recovery_state : 53
			$this->updateBatchLogDataRecoveryState(API_NO_CSV);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataRecoveryState = ' . API_NO_CSV);
			
			// メール通知
			$template = $this->configApiMail['template']['failure'];
			$this->sendMail($this->configApiMail, $template['subject'], $template['body']);
			
			$this->sendMailCsv(API_NO_CSV);
			
			throw new \Exception('', Response::STATUS_CODE_500);
		}
		
		if (empty($items)) {
			// データなし
			$this->updateBatchLogDataState(API_CSV_NO_LINE, true);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_CSV_NO_LINE);
		} else {
			// データあり
			$this->updateBatchLogDataState(API_CSV_EXIST_LINE);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_CSV_EXIST_LINE);
			
			$this->updateBatchLogDataState(API_CSV_START_INSERT);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_CSV_START_INSERT);
			
			$error = false;
			foreach ($items as $item_key => $item_val) {
				// CSVデータ バリデーション(DB)
				$req = $this->chk_csv_data_db($item_val);
				if ($req == false) {
					// $item_val or $req の内容をログに出力
					Log::batch(__FILE__, __LINE__, print_r($item_val, true));
					$error = true;
					continue;
				}
				
				// CSVデータ バリデーション
				$recovery_state = null;
				if (false == $this->chk_csv_data($req)) {
					// $item_val or $req の内容をログに出力
					Log::batch(__FILE__, __LINE__, print_r($item_val, true));
					$error = true;
					continue;
				}
				
				$contents_type = array_search($req[CSV_COLMON_TYPE], $this->contents_types);
				
				try {
					// バッチログコンテンツ系テーブル登録
					$batch_contents_ids = $this->creatBatchLogContent($contents_type, $req);
				} catch (\Exception $e) {
					Log::batch(__FILE__, __LINE__, $e->getMessage());
					$error = true;
					continue;
				}
				
				$rsError = false;
				
				try {
					// コンテンツ系テーブル登録
					$this->creatContent(WK_BATCH_IMPORT_TYPE_CSV, $contents_type, $req);
				} catch (DbAccessException $e) {
					Log::batch(__FILE__, __LINE__, $e->getMessage());
					$error = true;
					$rsError = true;
				} catch (\Exception $e) {
					Log::batch(__FILE__, __LINE__, $e->getMessage());
					$error = true;
					$rsError = true;
				}
				
				if ($rsError) {
					if (!$this->updateBatchLogContentDetailDataRecoveryState($batch_contents_ids, API_ERR_DATA)) {
						$error = true;
					}
				}
			}
			
			if ($error) {
				Log::batch(__FILE__, __LINE__, '');
				$this->updateBatchLogDataRecoveryState(API_ERR_GET_DATA);
				Log::batch(__FILE__, __LINE__, 'updateBatchLogDataRecoveryState = ' . API_ERR_GET_DATA);
				
				// メール通知
				$template = $this->configApiMail['template']['failure'];
				$this->sendMail($this->configApiMail, $template['subject'], $template['body']);
				
				throw new \Exception('', Response::STATUS_CODE_500);
			}
			
			$this->updateBatchLogDataState(API_CSV_END, true);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_CSV_END);
			
			// メール通知
			$template = $this->configApiMail['template']['success'];
			$this->sendMail($this->configApiMail, $template['subject'], $template['body']);
		}
		Log::batch(__FILE__, __LINE__, 'Debug::importResponseApi() End.');
	}
	
	/**
	 * 取込処理
	 *
	 * @param array $request リクエストパラメータ
	 */
	public function import($request)
	{
		Log::batch(__FILE__, __LINE__, 'Debug::import() Start.');
		
		try {
			// トリガー種別チェック
			$this->validateTriggerType($request['trigger_type']);
			
			// バッチログ作成
			$this->creatBatchLogData(WK_BATCH_IMPORT_TYPE_CSV, $request['trigger_type']);
			
			// バッチ(API)情報取得
			$this->getBatch($request['batch_id']);
			
			// ロック
			$this->updateBatchProc(WK_BATCH_PROC_STATE_RUN);
			
			// API実行
			$this->execApi();
			
			// 取得データ取り込み
			$this->importResponseApi();
			
			$returnCode = Response::STATUS_CODE_200;
		} catch(\Exception $e) {
			if ($e->getCode() == 0) {
				$returnCode = Response::STATUS_CODE_500;
			} else {
				$returnCode = $e->getCode();
			}
			Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
		}
		// アンロック
		try {
			$this->updateBatchProc(WK_BATCH_PROC_STATE_WAIT);
		} catch (\Exception $e) {
			Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
			$returnCode = Response::STATUS_CODE_500;
		}
		Log::batch(__FILE__, __LINE__, 'Debug::import() End.');
		return $returnCode;
	}
}
