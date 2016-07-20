<?php

namespace Api\Model;

use Zend\Http\Response;

use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Common\RequestClass as RequestClass;
use ContentsMonitor\Common\ValidationClass as Validation;

class ImportContentApiForm extends CommonForm
{
	/**
	 * バリデーションエラー取得
	 *
	 * @param array $req 登録データ
	 * @param int $n 深度
	 * @return bool true:正常,false:エラー
	 */
	private function isValidateError($req, $n = 0)
	{
		// 指定深度以上はエラーとする
		if ($n >= 10) {
			return true;
		}
		
		if (is_array($req)) {
			$result = false;
			foreach ($req as $v) {
				$result = $this->isValidateError($v, ($n + 1));
				if ($result) { 
					break;
				}
			}
		} else {
			$result = is_null($req);
		}
		return $result;
	}
	
	/**
	 * バリデーション
	 *
	 * @param array $item_val 登録データ
	 * @return array 登録データ
	 */
	private function validateResponseApi($item_val)
	{
		Log::batch(__FILE__, __LINE__, 'Debug::validateResponseApi() Start.');
		
		$req = array();
		
		// コンテンツ種別名（Movie:動画／Image:画像／Comment:コメント）
		$req['Type'] = Validation::validateApi($item_val, 'Type', true, $this->contents_types, null, null);
		
		// コンテンツ内部ID（お客様側の管理ID）
		$req['Id'] = Validation::validateApi($item_val, 'Id', true, 0, null, 10);
		// コンテンツ - タイトル
		$req['Title'] = Validation::validateApi($item_val, 'Title', false, 0, null, 1024);
		// コンテンツ - 名前
		$req['User'] = Validation::validateApi($item_val, 'User', false, 0, null, 100);
		// コンテンツ - 作成日時
		$req['CreateDate'] = Validation::validateApi($item_val, 'CreateDate', true, 2, null, 19);
		// コンテンツ - キャプション
		$req['Caption'] = Validation::validateApi($item_val, 'Caption', false, 0, null, 100);
		
		if ($req['Type'] == CONTENTS_TYPE_MV_NAME) {
			// フォーマット
			$req['Format'] = Validation::validateApi($item_val, 'Format', true, $this->format_types_movie, null, 10);
			// コンテンツURL
			$req['Url'] = Validation::validateApi($item_val, 'Url', true, 3, null, 200);
			// コンテンツ - コメント
			$req['Comment'] = Validation::validateApi($item_val, 'Comment', false, 5, null, 1024);
			$imageList = RequestClass::arrayValue($item_val, 'ImageList', array(), true);
			if ($this->serviceData->api_type == API_TYPE_XML) {
				$imageList = RequestClass::arrayValue($imageList, 'Image', array(), true);
				if (isset($imageList['Id'])) {
					$imageList = array($imageList);
				}
			}
			foreach ($imageList as $image_key => $image_val) {
				$image = array();
				// コンテンツ内部ID（お客様側の管理ID）
				$image['Id'] = Validation::validateApi($image_val, 'Id', true, 0, null, 10);
				// コンテンツURL
				$image['Url'] = Validation::validateApi($image_val, 'Url', true, 3, null, 200);
				// コンテンツ - 作成日時
				$image['CreateDate'] = Validation::validateApi($image_val, 'CreateDate', true, 2, null, 19);
				$imageList[$image_key] = $image;
			}
			$req['ImageList'] = $imageList;
		} elseif ($req['Type'] == CONTENTS_TYPE_IM_NAME) {
			// フォーマット
			$req['Format'] = Validation::validateApi($item_val, 'Format', true, $this->format_types_image, null, 10);
			// コンテンツURL
			$req['Url'] = Validation::validateApi($item_val, 'Url', true, 3, null, 200);
			// コンテンツ - コメント
			$req['Comment'] = Validation::validateApi($item_val, 'Comment', false, 5, null, 1024);
		} elseif ($req['Type'] == CONTENTS_TYPE_CT_NAME) {
			// フォーマット
			$req['Format'] = Validation::validateApi($item_val, 'Format', false, 0, null, 10);
			// コンテンツURL
			$req['Url'] = Validation::validateApi($item_val, 'Url', false, 3, null, 200);
			// コンテンツ - コメント
			$req['Comment'] = Validation::validateApi($item_val, 'Comment', true, 5, null, 1024);
		} else {
			//
		}
		Log::batch(__FILE__, __LINE__, 'Debug::validateResponseApi() End.');
		return $req;
	}
	
	/**
	 * 取得データ取り込み
	 *
	 */
	private function importResponseApi()
	{
		Log::batch(__FILE__, __LINE__, 'Debug::importResponseApi() Start.');
		
		$this->updateBatchLogDataState(API_API_CHECK_API);
		Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_API_CHECK_API);
		
		$items = RequestClass::arrayValue($this->responseApi, 'Items', array(), true);
		if ($this->serviceData->api_type == API_TYPE_XML) {
			// XMLの場合、値によっては配列、又は、スカラで帰ってくるため配列に統一
			$items = RequestClass::arrayValue($items, 'Item', array(), true);
			if (isset($items['Type'])) {
				$items = array($items);
			}
		}
		
		if (empty($items)) {
			// データなし
			$this->updateBatchLogDataState(API_API_NO_LINE, true);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_API_NO_LINE);
		} else {
			// データあり
			$this->updateBatchLogDataState(API_API_EXIST_LINE);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_API_EXIST_LINE);
			
			$this->updateBatchLogDataState(API_API_START_INSERT);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_API_START_INSERT);
			
			$error = false;
			foreach ($items as $item_key => $item_val) {
				// バリデート
				$req = $this->validateResponseApi($item_val);
				if ($this->isValidateError($req)) {
					// $item_val or $req の内容をログに出力
					Log::batch(__FILE__, __LINE__, print_r($item_val, true));
					$error = true;
					continue;
				}
				
				// コンテンツタイプ名よりコンテンツタイプ値を取得
				$contents_type = array_search($req['Type'], $this->contents_types);
				
				try {
					// バッチログコンテンツ系テーブル登録
					$batch_contents_ids = $this->creatBatchLogContent($contents_type, $req);
				} catch (DbAccessException $e) {
					Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
					$error = true;
					continue;
				} catch (\Exception $e) {
					Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
					$error = true;
					continue;
				}
				
				$rsError = false;
				
				try {
					// コンテンツ系テーブル登録
					$this->creatContent(WK_BATCH_IMPORT_TYPE_API, $contents_type, $req);
				} catch (DbAccessException $e) {
					Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
					$error = true;
					$rsError = true;
				} catch (\Exception $e) {
					Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
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
			
			$this->updateBatchLogDataState(API_API_END, true);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . API_API_END);
			
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
	 * @return int ステータスコード
	 */
	public function import($request)
	{
		Log::batch(__FILE__, __LINE__, 'Debug::import() Start.');
		
		try {
			// トリガー種別チェック
			$this->validateTriggerType($request['trigger_type']);
			
			// バッチログ作成
			$this->creatBatchLogData(WK_BATCH_IMPORT_TYPE_API, $request['trigger_type']);
			
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
