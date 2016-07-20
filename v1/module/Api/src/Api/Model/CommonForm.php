<?php

namespace Api\Model;

use Exception;

use Zend\Http\Response;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Zend\Mail;
use Zend\Json\Json;

use ContentsMonitor\Common\LogClass as Log;
use ContentsMonitor\Exception\DbAccessException as DbAccessException;

use ContentsMonitor\Service\Entity\BatchLogData as BatchLogData;
use ContentsMonitor\Service\Entity\BatchProcData as BatchProcData;
use ContentsMonitor\Service\Entity\BatchLogContentData;
use ContentsMonitor\Service\Entity\BatchLogContentDetailData;
use ContentsMonitor\Service\Entity\ContentData;
use ContentsMonitor\Service\Entity\ContentDetailData;

class CommonForm
{
	/**
	 * サービスロケーター
	 * @var $service_locator
	 */
	protected $service_locator;
	
	/**
	 * コンフィグ
	 * @var $config
	 */
	protected $config;
	
	/**
	 * config情報
	 * @var $configApi
	 */
	protected $configApi;
	
	/**
	 * config内のメール情報
	 * @var $configApiMail
	 */
	protected $configApiMail;
	
	/**
	 * アダプター
	 * @var $adapter
	 */
	protected $adapter;
	
	/**
	 * コネクション
	 * @var $conn
	 */
	protected $conn;
	
	/**** DBテーブル start ****/
	/**
	 * APIエラー情報へのアクセスクラス
	 * @var $apiErrorTable
	 */
	protected $apiErrorTable;
	
	/**
	 * バッチ情報へのアクセスクラス
	 * @var $batchTable
	 */
	protected $batchTable;
	
	/**
	 * 処理ステータス情報へのアクセスクラス
	 * @var $batchProcTable
	 */
	protected $batchProcTable;
	
	/**
	 * バッチログ情報へのアクセスクラス
	 * @var $batchLogTable
	 */
	protected $batchLogTable;
	
	/**
	 * バッチログコンテンツ情報へのアクセスクラス
	 * @var $batchLogContentTable
	 */
	protected $batchLogContentTable;
	
	/**
	 * バッチログコンテンツ詳細情報へのアクセスクラス
	 * @var $batchLogContentDetailTable
	 */
	protected $batchLogContentDetailTable;
	
	/**
	 * コンテンツ情報へのアクセスクラス
	 * @var $contentsTable
	 */
	protected $contentsTable;
	
	/**
	 * コンテンツ詳細情報へのアクセスクラス
	 * @var $contentsDetailTable
	 */
	protected $contentsDetailTable;
	
	/**
	 * サービス情報へのアクセスクラス
	 * @var $serviceTable
	 */
	protected $serviceTable;
	/**** DBテーブル end ****/
	
	/**** DBデータ start ****/
	/**
	 * 
	 * @var $batchData
	 */
	protected $batchData;
	
	/**
	 * 
	 * @var $batchLogData
	 */
	protected $batchLogData;
	
	/**
	 * 
	 * @var $serviceData
	 */
	protected $serviceData;
	
	/**
	 * 
	 * @var $apiErrorData
	 */
	protected $apiErrorData;
	
	/**** DBデータ end ****/
	
	/**
	 * 
	 * @var $apiErrorArray
	 */
	protected $apiErrorArray;
	
	/**
	 * 
	 * @var $responseApi
	 */
	protected $responseApi;
	
	/**
	 * 日時（年月）<INT>
	 * @var $now
	 */
	protected $now;
	
	/**
	 * 実行日時<DATTIME>
	 * @var $nowDatetime
	 */
	protected $nowDatetime;
	
	/**
	 * バッチ実行状態フラグ
	 * @var $isBatchProcStatusRun
	 */
	protected $isBatchProcStatusRun = false;
	
	/**
	 * 
	 * @var $contents_types
	 */
	protected $contents_types = array(
		CONTENTS_TYPE_MV => CONTENTS_TYPE_MV_NAME,
		CONTENTS_TYPE_IM => CONTENTS_TYPE_IM_NAME,
		CONTENTS_TYPE_CT => CONTENTS_TYPE_CT_NAME,
	);
	
	/**** バリデータ用データ start ****/
	/**
	 * 画像用フォーマット種別
	 * @var $format_types_image
	 */
	protected $format_types_image = array(FORMAT_TYPE_JPG, FORMAT_TYPE_PNG);
	
	/**
	 * 画像用フォーマット種別
	 * @var $format_types_movie
	 */
	protected $format_types_movie = array(FORMAT_TYPE_MP4);
	/**** バリデータ用データ end   ****/
	
	/**
	 * コンストラクタ
	 * @param ServiceLocator $service_locator サービスロケーター
	 * @throws Exception
	 */
	public function __construct($service_locator)
	{
		Log::batch(__FILE__, __LINE__, 'Dedub::__construct() Start.');
		
		// 引数チェック
		if (is_null($service_locator)) {
			throw new Exception('$service_locator is null');
		}
		try {
			// DBテーブル
			$this->service_locator = $service_locator;
			$this->config = $this->service_locator->get('Config');
			$this->adapter = $this->service_locator->get('Zend\Db\Adapter\Adapter');
			$this->apiErrorTable = $this->service_locator->get('ContentsMonitor\Service\Data\ApiErrorTable');
			$this->batchTable = $this->service_locator->get('ContentsMonitor\Service\Data\BatchTable');
			$this->batchProcTable = $this->service_locator->get('ContentsMonitor\Service\Data\BatchProcTable');
			$this->batchLogTable = $this->service_locator->get('ContentsMonitor\Service\Data\BatchLogTable');
			$this->batchLogContentTable = $this->service_locator->get('ContentsMonitor\Service\Data\BatchLogContentTable');
			$this->batchLogContentDetailTable = $this->service_locator->get('ContentsMonitor\Service\Data\BatchLogContentDetailTable');
			$this->contentsTable = $this->service_locator->get('ContentsMonitor\Service\Data\ContentTable');
			$this->contentsDetailTable = $this->service_locator->get('ContentsMonitor\Service\Data\ContentDetailTable');
			$this->serviceTable = $this->service_locator->get('ContentsMonitor\Service\Data\ServiceTable');
			// 現在時間取得
			$this->now = time();
			$this->nowDatetime = strftime('%Y-%m-%d %H:%M:%S', $this->now);
			$this->configApi = $this->config['api'];
			$this->configApiMail = $this->configApi['mail'];
			$this->conn = $this->adapter->getDriver()->getConnection();
		} catch (\Exception $e) {
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw $e;
		}
		
		Log::batch(__FILE__, __LINE__, 'Dedub::__construct() End.');
	}
	
	/**
	 * 
	 * メール送信
	 * @param array $configApiMail configのメール情報
	 * @param string $subject メールサブジェクト
	 * @param string $body メール本文
	 * @throws Exception
	 */
	protected function sendMail($configApiMail, $subject, $body)
	{
		Log::batch(__FILE__, __LINE__, 'Dedub::sendMail() Start.');
		
		// 引数チェック
		if (is_null($configApiMail)) {
			throw new Exception('$service_locator is configApiMail');
		}
		if (is_null($subject)) {
			throw new Exception('$service_locator is subject');
		}
		if (is_null($body)) {
			throw new Exception('$service_locator is body');
		}
		
		try {
			// メールセット
			$mail = new Mail\Message();
			$mail->setEncoding('UTF-8');
			$mail->setFrom($configApiMail['from']['email'], $configApiMail['from']['name']);
			$mail->setSubject($subject);
			$mail->setBody($body);
			// メール送信
			$transport = new Mail\Transport\Sendmail();
			foreach ($configApiMail['to'] as $to) {
				$mail->setTo($to['email'], $to['name']);
				$transport->send($mail);
			}
		} catch (\Exception $e) {
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw $e;
		}
		Log::batch(__FILE__, __LINE__, 'Dedub::sendMail() End.');
	}
	
	/**
	 * トリガー種別チェック
	 *
	 * @param int $trigger_type トリガー種別（1:PUSH／2:PULL）
	 */
	protected function validateTriggerType($trigger_type)
	{
		Log::batch(__FILE__, __LINE__, 'Debug::validateTriggerType() Start.');
		
		$triggerTypeArray = array(WK_BATCH_TRIGGER_TYPE_PUSH, WK_BATCH_TRIGGER_TYPE_PULL);
		if (!in_array($trigger_type, $triggerTypeArray)) {
			// error
			throw new \Exception($trigger_type, Response::STATUS_CODE_400);
		}
		Log::batch(__FILE__, __LINE__, 'Debug::validateTriggerType() End.');
	}
	
	/**
	 * コンテンツ取込バッチ処理データ作成
	 *
	 * @param int $import_type 取込種別（1:API／2:CSV／3:クローラ）
	 * @param int $trigger_type トリガー種別（1:PUSH／2:PULL）
	 */
	protected function creatBatchLogData($import_type, $trigger_type)
	{
		Log::batch(__FILE__, __LINE__, 'Dedub::creatBatchLogData() Start.');
		
		// 引数チェック
		if (is_null($trigger_type)) {
			throw new Exception('trigger_type is null');
		}
		// 取り込み種別によってstateを変更
		switch ($import_type) {
			case WK_BATCH_IMPORT_TYPE_API:		// API
				$state = API_API_START;
				break;
			case WK_BATCH_IMPORT_TYPE_CSV:		// CSV
				$state = API_CSV_START;
				break;
			case WK_BATCH_IMPORT_TYPE_CRAWLER:	// CRAWLER
				// TODO
				break;
			default :
				throw new Exception('import_type : '.$import_type);
				break;
		}
		
		Log::batch(__FILE__, __LINE__, 'WK_BATCH_LOG.import_type = ' . $import_type);
		Log::batch(__FILE__, __LINE__, 'WK_BATCH_LOG.start_ym = ' . (int)strftime('%Y%m', $this->now));
		Log::batch(__FILE__, __LINE__, 'WK_BATCH_LOG.start_date = ' . $this->nowDatetime);
		Log::batch(__FILE__, __LINE__, 'WK_BATCH_LOG.state = ' . $state);
		Log::batch(__FILE__, __LINE__, 'WK_BATCH_LOG.insert_date = ' . $this->nowDatetime);
		
		try {
			$this->batchLogData = new BatchLogData();
			// バッチID
			$this->batchLogData->batch_id = null;
			// 取込種別（1:API／2:CSV／3:クローラ）
			$this->batchLogData->import_type = $import_type;
			// トリガー種別（1:PUSH／2:PULL）
			$this->batchLogData->trigger_type = $trigger_type;
			// 実行日時（年月）<INT>
			$this->batchLogData->start_ym = (int)strftime('%Y%m', $this->now);
			// 実行日時<DATTIME>
			$this->batchLogData->start_date = $this->nowDatetime;
			// 終了日時
			$this->batchLogData->end_date = null;
			// 実行URL?お客さんのURL
			$this->batchLogData->url = null;
			// 実行結果ステータス
			$this->batchLogData->state = $state;
			// リカバリ―ステータス
			$this->batchLogData->recovery_state = null;
			// 作成日時
			$this->batchLogData->insert_date = $this->nowDatetime;
			
			// DBに保存
			$this->batchLogTable->saveWkBatchLog($this->batchLogData);
			$this->batchLogData->batch_log_id = $this->batchLogTable->getLastInsertValue();
		} catch (\DbAccessException $e) {
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw new Exception($e->getMessage());
		} catch (\Exception $e) {
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw $e;
		}
		
		Log::batch(__FILE__, __LINE__, 'WK_BATCH_LOG.batch_log_id = ' . $this->batchLogData->batch_log_id);
		
		Log::batch(__FILE__, __LINE__, 'Dedub::creatBatchLogData() End.');
	}
	
	/**
	 * バッチログ終了日時設定
	 *
	 * @param string $end_date 終了日時
	 */
	protected function setBatchLogEndDate($end_date = false)
	{
		if ($end_date === false) {
			$end_date = strftime('%Y-%m-%d %H:%M:%S', time());
		}
		// 終了日時
		$this->batchLogData->end_date = $end_date;
	}
	
	/**
	 * コンテンツ取込バッチ処理データ－実行結果ステータス 更新
	 *
	 * @param int $state 実行結果ステータス
	 * @param int $flag 終了日時更新フラグ
	 */
	protected function updateBatchLogDataState($state, $flag = false)
	{
		Log::batch(__FILE__, __LINE__, 'Dedub::updateBatchLogDataState() Start.');
		
		// 引数チェック
		if (is_null($state)) {
			throw new Exception('state is null');
		}
		
		// コンテンツ取込バッチ処理データの有無をチェック
		if (is_null($this->batchLogData)) {
			throw new Exception('state is null');
		}
		
		if ($flag) {
			$this->setBatchLogEndDate(false);
		}
		
		try {
			$this->batchLogData->state = $state;
			$this->batchLogTable->saveWkBatchLog($this->batchLogData);
		} catch (\DbAccessException $e) {
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw new Exception($e->getMessage());
		} catch (\Exception $e) {
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw $e;
		}
		
		Log::batch(__FILE__, __LINE__, 'Dedub::updateBatchLogDataState() End.');
	}
	
	/**
	 * コンテンツ取込バッチ処理データ－リカバリーステータス 更新
	 *
	 * @param int $recovery_state リカバリーステータス
	 */
	protected function updateBatchLogDataRecoveryState($recovery_state)
	{
		Log::batch(__FILE__, __LINE__, 'Dedub::updateBatchLogDataRecoveryState() Start.');
		
		// 引数チェック
		if (is_null($recovery_state)) {
			throw new Exception('recovery_state is null');
		}
		
		// コンテンツ取込バッチ処理データの有無をチェック
		if (is_null($this->batchLogData)) {
			throw new Exception('state is null');
		}
		
		$this->setBatchLogEndDate(false);
		
		try {
			$this->batchLogData->recovery_state = $recovery_state;
			$this->batchLogTable->saveWkBatchLog($this->batchLogData);
		} catch (\DbAccessException $e) {
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw new Exception($e->getMessage());
		} catch (\Exception $e) {
			Log::batch(__FILE__, __LINE__, $e->getMessage());
			throw $e;
		}
		
		Log::batch(__FILE__, __LINE__, 'Dedub::updateBatchLogDataRecoveryState() End.');
	}
	
	/**
	 * コンテンツ取込バッチ処理データ－詳細リカバリーステータス 更新
	 *
	 * @param array $batch_contents_ids バッチコンテンツID
	 * @param int $recovery_state リカバリーステータス
	 * @return bool 正常:true,エラー:false
	 */
	protected function updateBatchLogContentDetailDataRecoveryState($batch_contents_ids, $recovery_state)
	{
		Log::batch(__FILE__, __LINE__, 'Dedub::updateBatchLogContentDetailDataRecoveryState() Start.');
		
		// 引数チェック
		if (is_null($recovery_state)) {
			throw new Exception('recovery_state is null');
		}
		
		// コンテンツ取込バッチ処理データの有無をチェック
		if (is_null($this->batchLogData)) {
			throw new Exception('state is null');
		}
		
		$error = false;
		
		foreach ($batch_contents_ids as $batch_contents_id) {
			try {
				$wkBatchLogContentDetail = $this->batchLogContentDetailTable->getWkBatchLogContentDetailByContentsId($batch_contents_id);
				
				$wkBatchLogContentDetail->recovery_state = $recovery_state;
				
				$this->batchLogContentDetailTable->saveWkBatchLogContentDetail($wkBatchLogContentDetail);
			} catch (DbAccessException $e) {
				Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
				$error = true;
			} catch (\Exception $e) {
				Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
				$error = true;
			}
		}
		
		Log::batch(__FILE__, __LINE__, 'Dedub::updateBatchLogContentDetailDataRecoveryState() End.');
		
		return $error;
	}
	
	/**
	 * 処理ステータスUPDATE
	 *
	 * @param int $state 処理ステータス（0:処理終了／1:処理中）
	 */
	protected function updateBatchProc($state)
	{
		Log::batch(__FILE__, __LINE__, 'Dedub::updateBatchProc() Start.');
		
		Log::batch(__FILE__, __LINE__, 'state = ' . $state);
		
		if ($state == WK_BATCH_PROC_STATE_WAIT && !$this->isBatchProcStatusRun) {
			// 停止中に停止設定は続行しない
			return;
		}
		try {
			// ロック処理＆処理状態更新処理
			$this->conn->beginTransaction();
			
			$wkBatchProc = $this->batchProcTable->getWkBatchProcForUpdate($this->batchData->batch_id);
			if (!$wkBatchProc) {
				Log::batch(__FILE__, __LINE__, 'wkBatchProc is empty.');
				throw new \Exception($this->batchData->batch_id, Response::STATUS_CODE_500);
			}
			if ($state == WK_BATCH_PROC_STATE_RUN && $wkBatchProc['state'] == WK_BATCH_PROC_STATE_RUN) {
				// 実行中
				Log::batch(__FILE__, __LINE__, 'already running.');
				throw new \Exception($this->batchData->batch_id, Response::STATUS_CODE_500);
			}
			$wkBatchProc = new BatchProcData();
			$wkBatchProc->batch_id = $this->batchData->batch_id;
			$wkBatchProc->state = $state;
//			$this->batchProcTable->saveWkBatchProc($wkBatchProc);
			$this->conn->commit();
			$this->isBatchProcStatusRun = true;
		} catch (\DbAccessException $e) {
			Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
			throw new Exception($e->getMessage());
		} catch (\Exception $e) {
			$this->conn->rollback();
			Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
			throw $e;
		}
		
		Log::batch(__FILE__, __LINE__, 'Dedub::updateBatchProc() End.');
	}
	
	/**
	 * バッチデータ取得
	 *
	 * @param int $batch_id バッチID
	 */
	protected function getBatch($batch_id)
	{
		Log::batch(__FILE__, __LINE__, 'Debug::getBatch() Start.');
		
		switch ($this->batchLogData->import_type) {
			case WK_BATCH_IMPORT_TYPE_API:
				$start_get_connection_data = API_API_START_GET_CONNECTION_DATA;
				$get_connection_data = API_API_GET_CONNECTION_DATA;
				break;
			case WK_BATCH_IMPORT_TYPE_CSV:
				$start_get_connection_data = API_CSV_START_GET_CONNECTION_DATA;
				$get_connection_data = API_CSV_GET_CONNECTION_DATA;
				break;
			case WK_BATCH_IMPORT_TYPE_CRAWLER:
				break;
			default:
				break;
		}
		
		$maxTryCount = $this->configApi['retry_count'];
		for ($tryCount = 0; $tryCount < $maxTryCount; $tryCount++) {
			Log::batch(__FILE__, __LINE__, 'tryCount = ' . $tryCount);
			if ($tryCount > 0) {
				// sleep
				Log::batch(__FILE__, __LINE__, 'interval = ' . $this->configApi['interval']);
				sleep($this->configApi['interval']);
			}
			
			// バッチ情報取得
			$wkBatch = $this->batchTable->getBatch($batch_id);
			
			$this->updateBatchLogDataState($start_get_connection_data);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . $start_get_connection_data);
			
			if (!$wkBatch) {
				Log::batch(__FILE__, __LINE__, $batch_id);
				Log::batch(__FILE__, __LINE__, 'wkBatch is empty.');
				continue;
			}
			
			// サービス情報取得
			$mstService = $this->serviceTable->getService($wkBatch->service_id);
			if (!$mstService) {
				Log::batch(__FILE__, __LINE__, $wkBatch->service_id);
				Log::batch(__FILE__, __LINE__, 'mstService is empty.');
				continue;
			}
			
			// APIエラー情報取得
			$mstApiError = $this->apiErrorTable->getMstApiErrorByServiceId($mstService->service_id);
			$mstApiErrorArray = array();
			foreach ($mstApiError as $v) {
				$temp = array();
				$temp['error_code'] = $v->error_code;
				$mstApiErrorArray[] = $temp;
			}
			break;
		}
		
		if ($maxTryCount <= $tryCount) {
			// 3回以上失敗
			Log::batch(__FILE__, __LINE__, '');
			Log::batch(__FILE__, __LINE__, 'maxTryCount = ' . $maxTryCount);
			$this->updateBatchLogDataRecoveryState(API_NO_CONNECTION_DATA);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataRecoveryState = ' . API_NO_CONNECTION_DATA);
			
			// メール通知
			$template = $this->configApiMail['template']['failure'];
			$this->sendMail($this->configApiMail, $template['subject'], $template['body']);
			
			throw new \Exception('', Response::STATUS_CODE_500);
		}
		$this->batchLogData->batch_id = $wkBatch->batch_id;
		$this->batchData = $wkBatch;
		$this->serviceData = $mstService;
		$this->apiErrorData = $mstApiError;
		$this->apiErrorArray = $mstApiErrorArray;
		switch ($this->batchLogData->import_type) {
			case WK_BATCH_IMPORT_TYPE_API:
				$this->batchLogData->url = $this->serviceData->api_url . '?' . $this->serviceData->api_param;
				//$this->batchLogData->url = 'https://noue.cm.ip128.ip140.faith-sol-tech.local/test_json2.php';
				break;
			case WK_BATCH_IMPORT_TYPE_CSV:
				$this->batchLogData->url = $this->serviceData->csv_url . '?' . $this->serviceData->csv_param;
				break;
			case WK_BATCH_IMPORT_TYPE_CRAWLER:
				break;
			default:
				break;
		}
		$this->updateBatchLogDataState($get_connection_data);
		Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . $get_connection_data);
		
		Log::batch(__FILE__, __LINE__, 'Debug::getBatch() End.');
	}
	
	/**
	 * API実行
	 *
	 */
	protected function execApi()
	{
		Log::batch(__FILE__, __LINE__, 'Debug::execApi() Start.');
		
		switch ($this->batchLogData->import_type) {
			case WK_BATCH_IMPORT_TYPE_API:
				$start_connection = API_API_START_CONNECTION_API;
				$connection = API_API_CONNECTION_API;
				$filepath_string = DOWNLOAD_API_PATH . "/api_%s_%d_%d.txt";
				break;
			case WK_BATCH_IMPORT_TYPE_CSV:
				$start_connection = API_CSV_START_CONNECTION_HTTP;
				$connection = API_CSV_CONNECTION_HTTP;
				$filepath_string = DOWNLOAD_CSV_PATH . "/csv_%s_%d_%d.txt";
				break;
			case WK_BATCH_IMPORT_TYPE_CRAWLER:
				throw new \Exception('', Response::STATUS_CODE_500);
				break;
			default:
				throw new \Exception('', Response::STATUS_CODE_500);
				break;
		}
		
		Log::batch(__FILE__, __LINE__, 'url = ' . $this->batchLogData->url);
		Log::batch(__FILE__, __LINE__, 'max_redirects = ' . $this->configApi['max_redirects']);
		Log::batch(__FILE__, __LINE__, 'timeout = ' . $this->configApi['timeout']);
		Log::batch(__FILE__, __LINE__, 'user_agent = ' . $this->configApi['user_agent']);
		
		$adapter = new \Zend\Http\Client\Adapter\Curl();
		$adapter->setOptions(
			array(
				'curloptions' => array(
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_MAXREDIRS => $this->configApi['max_redirects'],
					CURLOPT_TIMEOUT => $this->configApi['timeout'],
					CURLOPT_USERAGENT => $this->configApi['user_agent'],
					CURLOPT_SSL_VERIFYPEER => false, // 本番環境ではtrue推奨
				)
			)
		);
		$client = new \Zend\Http\Client();
		$client->setAdapter($adapter);
		$client->setUri($this->batchLogData->url);
		$client->setMethod('GET');
		Log::batch(__FILE__, __LINE__, 'api_auth_flag = ' . $this->serviceData->api_auth_flag);
		if ($this->serviceData->api_auth_flag) {
			// BASIC認証
			Log::batch(__FILE__, __LINE__, 'api_auth_user = ' . $this->serviceData->api_auth_user);
			Log::batch(__FILE__, __LINE__, 'api_auth_pass = ' . $this->serviceData->api_auth_pass);
			$client->setAuth($this->serviceData->api_auth_user, $this->serviceData->api_auth_pass, Client::AUTH_BASIC);
		}
		
		$maxTryCount = $this->configApi['retry_count'];
		for ($tryCount = 0; $tryCount < $maxTryCount; $tryCount++) {
			Log::batch(__FILE__, __LINE__, 'tryCount = ' . $tryCount);
			if ($tryCount > 0) {
				// sleep
				Log::batch(__FILE__, __LINE__, 'interval = ' . $this->configApi['interval']);
				sleep($this->configApi['interval']);
			}
			
			// API実行
			try {
				$res = $client->send();
				
				$this->updateBatchLogDataState($start_connection);
				Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . $start_connection);
			} catch (\Exception $e) {
				Log::batch(__FILE__, __LINE__, '');
				Log::batch(__FILE__, __LINE__, 'api error.');
				continue;
			}
			
			$file = $res->getBody();
			
			// 保存
			if (!empty($file)) {
				try {
					$date = strftime('%Y%m%d%H%M%S', $this->now);
					$filepath = sprintf($filepath_string, $date, $this->batchLogData->batch_log_id, 1);
					Log::batch(__FILE__, __LINE__, 'download_filepath = ' . $filepath);
					$ret = file_put_contents($filepath, $file);
					if ($ret === false) {
						Log::batch(__FILE__, __LINE__, '');
						Log::batch(__FILE__, __LINE__, 'file_put_contents error.');
						continue;
					}
				} catch (\Exception $e) {
					Log::batch(__FILE__, __LINE__, '');
					continue;
				}
			}
			
			$httpStatusCode = $res->getStatusCode();
			Log::batch(__FILE__, __LINE__, 'StatusCode = ' . $httpStatusCode);
			if ($httpStatusCode != Response::STATUS_CODE_200) {
				// error
				Log::batch(__FILE__, __LINE__, '');
				continue;
			}
			
			if ($this->batchLogData->import_type == WK_BATCH_IMPORT_TYPE_API) {
				if (empty($file)) {
					Log::batch(__FILE__, __LINE__, '');
					Log::batch(__FILE__, __LINE__, 'file is empty.');
					continue;
				}
				try {
					if ($this->serviceData->api_type == API_TYPE_JSON) {
						$responseApi = Json::decode($file, Json::TYPE_ARRAY);
					} elseif ($this->serviceData->api_type == API_TYPE_XML) {
						$responseApi = Json::fromXml($file, true);
						$responseApi = Json::decode($responseApi, Json::TYPE_ARRAY);
						$responseApi = $responseApi['Results'];
					} else {
						// error
						Log::batch(__FILE__, __LINE__, '');
						continue;
					}
				} catch (\Exception $e) {
					Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
					continue;
				}
				
				if (!isset($responseApi['ResultCode'])) {
					// error
					Log::batch(__FILE__, __LINE__, '');
					continue;
				}
				
				$error = false;
				foreach ($this->apiErrorArray as $v) {
					if ($v['error_code'] == $responseApi['ResultCode']) {
						// error
						Log::batch(__FILE__, __LINE__, '');
						Log::batch(__FILE__, __LINE__, 'ResultCode = ' . $responseApi['ResultCode']);
						$error = true;
						break;
					}
				}
				if (!$error) {
					break;
				}
			} elseif ($this->batchLogData->import_type == WK_BATCH_IMPORT_TYPE_CSV) {
				if (empty($file)) {
					$responseApi = null;
					Log::batch(__FILE__, __LINE__, 'file is empty.');
				} else {
					// 文字コード変換
					$from_encode = mb_detect_encoding($file, "auto");
					Log::batch(__FILE__, __LINE__, 'from_encode = ' . $from_encode);
					if ($from_encode === false) {
						Log::batch(__FILE__, __LINE__, "文字コード不正");
						continue;
					}
					$responseApi = mb_convert_encoding($file, 'UTF-8', $from_encode);
				}
				break;
			} else {
			}
		}
		
		if ($maxTryCount <= $tryCount) {
			// 3回以上失敗
			Log::batch(__FILE__, __LINE__, '');
			Log::batch(__FILE__, __LINE__, 'maxTryCount = ' . $maxTryCount);
			$this->updateBatchLogDataRecoveryState(API_ERR_CONNECTION);
			Log::batch(__FILE__, __LINE__, 'updateBatchLogDataRecoveryState = ' . API_ERR_CONNECTION);
			
			// メール通知
			$template = $this->configApiMail['template']['failure'];
			$this->sendMail($this->configApiMail, $template['subject'], $template['body']);
			
			throw new \Exception('', Response::STATUS_CODE_500);
		}
		$this->responseApi = $responseApi;
		$this->updateBatchLogDataState($connection);
		Log::batch(__FILE__, __LINE__, 'updateBatchLogDataState = ' . $connection);
		
		Log::batch(__FILE__, __LINE__, 'Debug::execApi() End.');
	}
	
	/**
	 * バッチログコンテンツINSERT
	 *
	 * @return int バッチトランザクションID
	 */
	protected function insertBatchLogContent()
	{
		Log::batch(__FILE__, __LINE__, 'Debug::insertBatchLogContent() Start.');
		
		$wkBatchLogContent = new BatchLogContentData();
		
		// バッチログID
		$wkBatchLogContent->batch_log_id = $this->batchLogData->batch_log_id;
		
		$this->batchLogContentTable->saveWkBatchLogContent($wkBatchLogContent);
		
		$batch_transaction_id = $this->batchLogContentTable->getLastInsertValue();
		
		Log::batch(__FILE__, __LINE__, 'Debug::insertBatchLogContent() End.');
		
		return $batch_transaction_id;
	}
	
	/**
	 * バッチログコンテンツ詳細INSERT
	 *
	 * @param int $batch_transaction_id バッチトランザクションID
	 * @param int $contents_type コンテンツ種別（1:動画／2:画像／3:コメント）
	 * @param array $req 登録データ
	 * @return int バッチコンテンツID
	 */
	protected function insertBatchLogContentDetail($batch_transaction_id, $contents_type, $req)
	{
		Log::batch(__FILE__, __LINE__, 'Debug::insertBatchLogContentDetail() Start.');
		
		$insert_now = time();
		$insert_date = strftime('%Y-%m-%d %H:%M:%S', $insert_now);
		
		$wkBatchLogContentDetail = new BatchLogContentDetailData();
		// バッチトランザクションID
		$wkBatchLogContentDetail->batch_transaction_id = $batch_transaction_id;
		// コンテンツ種別（1:動画／2:画像／3:コメント）
		$wkBatchLogContentDetail->contents_type = $contents_type;
		// コンテンツ内部ID（お客様側の管理ID）
		$wkBatchLogContentDetail->contents_id = isset($req['Id']) ? $req['Id'] : null;
		// 動画or画像URL
		$wkBatchLogContentDetail->url = isset($req['Url']) ? $req['Url'] : null;
		// コンテンツ - フォーマット（動画）
		$wkBatchLogContentDetail->format = isset($req['Format']) ? $req['Format'] : null;
		// コンテンツ - コメント
		$wkBatchLogContentDetail->comment = isset($req['Comment']) ? $req['Comment'] : null;
		// コンテンツ - タイトル
		$wkBatchLogContentDetail->title = isset($req['Title']) ? $req['Title'] : null;
		// コンテンツ - キャプション
		$wkBatchLogContentDetail->caption = isset($req['Caption']) ? $req['Caption'] : null;
		// コンテンツ - 名前
		$wkBatchLogContentDetail->user = isset($req['User']) ? $req['User'] : null;
		// 作成日時
		$wkBatchLogContentDetail->create_date = isset($req['CreateDate']) ? $req['CreateDate'] : null;
		// 取込日時
		$wkBatchLogContentDetail->import_date = $insert_date;
		// 取込処理ステータス
		$wkBatchLogContentDetail->import_state = null;
		// エラー事由(MySQLのエラー番号等)
		$wkBatchLogContentDetail->error_reason = null;
		// リカバリーステータス(番号追加)
		$wkBatchLogContentDetail->recovery_state = isset($req['recovery_state']) ? $req['recovery_state'] : null;
		// リカバリー処理日時
		$wkBatchLogContentDetail->recovery_date = null;
		// リカバリー処理ユーザ
		$wkBatchLogContentDetail->recovery_user = null;
		// 作成日時
		$wkBatchLogContentDetail->insert_date = $insert_date;
		// 更新日時
		$wkBatchLogContentDetail->update_date = null;
		
		$this->batchLogContentDetailTable->saveWkBatchLogContentDetail($wkBatchLogContentDetail);
		
		$batch_contents_id = $this->batchLogContentDetailTable->getLastInsertValue();
		
		Log::batch(__FILE__, __LINE__, 'Debug::insertBatchLogContentDetail() End.');
		
		return $batch_contents_id;
	}
	
	/**
	 * コンテンツINSERT
	 *
	 * @param int $import_type 取込種別（1:API／2:CSV／3:クローラ）
	 * @param int $contents_type コンテンツ種別（1:動画／2:画像／3:コメント）
	 * @return int コンテンツID
	 */
	protected function insertContent($import_type, $contents_type)
	{
		Log::batch(__FILE__, __LINE__, 'Debug::insertContent() Start.');
		
		$insert_now = time();
		$insert_date = strftime('%Y-%m-%d %H:%M:%S', $insert_now);
		
		$trnContents = new ContentData();
		// コンテンツ種別（1:動画／2:画像／3:コメント）
		$trnContents->contents_type = $contents_type;
		// サービスID
		$trnContents->service_id = $this->serviceData->service_id;
		// 監視期間の開始日
		$trnContents->monitoring_start_date = null;
		// 監視期間の終了日
		$trnContents->monitoring_end_date = null;
		// 取込種別（1:API／2:CSV／3:クローラ）
		$trnContents->import_type = $import_type;
		// 取込処理日時（年月）
		$trnContents->import_ym = (int)strftime('%Y%m', $insert_now);
		// 取込処理日時（年月）
		$trnContents->import_date = $insert_date;
		
		$this->contentsTable->saveTrnContents($trnContents);
		
		$contents_id = $this->contentsTable->getLastInsertValue();
		
		Log::batch(__FILE__, __LINE__, 'Debug::insertContent() End.');
		
		return $contents_id;
	}
	
	/**
	 * コンテンツ詳細INSERT
	 *
	 * @param int $contents_id コンテンツID
	 * @param int $contents_type コンテンツ種別（1:動画／2:画像／3:コメント）
	 * @param array $req 登録データ
	 */
	protected function insertContentDetail($contents_id, $contents_type, $req)
	{
		Log::batch(__FILE__, __LINE__, 'Debug::insertContentDetail() Start.');
		
		$insert_now = time();
		$insert_date = strftime('%Y-%m-%d %H:%M:%S', $insert_now);
		
		$trnContentsDetail = new ContentDetailData();
		// コンテンツID
		$trnContentsDetail->contents_id = $contents_id;
		// コンテンツ種別（1:動画／2:画像／3:コメント）
		$trnContentsDetail->contents_type = $contents_type;
		// コンテンツ内部ID（お客様環境）
		$trnContentsDetail->contents_inner_id = isset($req['Id']) ? $req['Id'] : null;
		// コンテンツ親ID
		$trnContentsDetail->contents_parent_id = isset($req['ContentsParentId']) ? $req['ContentsParentId'] : null;
		// サブID
		$trnContentsDetail->sub_id = isset($req['SubId']) ? $req['SubId'] : 1;
		// コンテンツURL
		$trnContentsDetail->url = isset($req['Url']) ? $req['Url'] : null;
		// コンテンツ - フォーマット（動画）
		$trnContentsDetail->format = isset($req['Format']) ? $req['Format'] : null;
		// コンテンツ - コメント
		$trnContentsDetail->comment = isset($req['Comment']) ? $req['Comment'] : null;
		// コンテンツ - タイトル
		$trnContentsDetail->title = isset($req['Title']) ? $req['Title'] : null;
		// コンテンツ - キャプション
		$trnContentsDetail->caption = isset($req['Caption']) ? $req['Caption'] : null;
		// コンテンツ - 名前
		$trnContentsDetail->user = isset($req['User']) ? $req['User'] : null;
		// コンテンツ作成日時
		$trnContentsDetail->create_date = isset($req['CreateDate']) ? $req['CreateDate'] : null;
		// 監視チェックステータス
		$trnContentsDetail->check_state = null;
		// 結果ステータス
		$trnContentsDetail->check_result = null;
		// チェック時の備考
		$trnContentsDetail->check_note = null;
		// チェックユーザ
		$trnContentsDetail->check_user = null;
		// チェック日時
		$trnContentsDetail->check_date = null;
		// ロックユーザ
		$trnContentsDetail->rock_user = null;
		// ロック日時
		$trnContentsDetail->rock_date = null;
		// 作成ユーザ
		$trnContentsDetail->insert_user = null;
		// 作成日時
		$trnContentsDetail->insert_date = $insert_date;
		// 更新ユーザ
		$trnContentsDetail->update_user = null;
		// 更新日時
		$trnContentsDetail->update_date = null;
		
		$this->contentsDetailTable->saveTrnContentsDetail($trnContentsDetail);
		
		Log::batch(__FILE__, __LINE__, 'Debug::insertContentDetail() End.');
	}
	
	/**
	 * バッチログコンテンツ系テーブル登録
	 *
	 * @param int $contents_type コンテンツ種別（1:動画／2:画像／3:コメント）
	 * @param array $req 登録データ
	 * @return array バッチコンテンツID
	 */
	protected function creatBatchLogContent($contents_type, $req)
	{
		Log::batch(__FILE__, __LINE__, 'Dedub::creatBatchLogContent() Start.');
		
		$recovery_state = isset($req['recovery_state']) ? $req['recovery_state'] : null;
		
		$batch_contents_ids = array();
		
		try {
			// バッチログコンテンツ系テーブル登録
			$batch_transaction_id = $this->insertBatchLogContent();
			
			$batch_contents_ids[] = $this->insertBatchLogContentDetail($batch_transaction_id, $contents_type, $req);
			
			if ($req['Type'] == CONTENTS_TYPE_MV_NAME && isset($req['ImageList']) && !empty($req['ImageList'])) {
				// 動画の場合、画像があれば登録
				foreach ($req['ImageList'] as $image_key => $image_val) {
					$image_val['recovery_state'] = $recovery_state;
					$batch_contents_ids[] = $this->insertBatchLogContentDetail($batch_transaction_id, CONTENTS_TYPE_IM, $image_val);
				}
			}
		} catch (DbAccessException $e) {
			Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
			throw $e;
		} catch (\Exception $e) {
			Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
			throw $e;
		}
		
		Log::batch(__FILE__, __LINE__, 'Dedub::creatBatchLogContent() End.');
		
		return $batch_contents_ids;
	}
	
	/**
	 * コンテンツ系テーブル登録
	 *
	 * @param int $import_type 取込種別（1:API／2:CSV／3:クローラ）
	 * @param int $contents_type コンテンツ種別（1:動画／2:画像／3:コメント）
	 * @param array $req 登録データ
	 */
	protected function creatContent($import_type, $contents_type, $req)
	{
		Log::batch(__FILE__, __LINE__, 'Dedub::creatContent() Start.');
		
		try {
			// バッチログコンテンツ系テーブル登録
			$this->conn->beginTransaction();
			
			$contents_id = $this->insertContent($import_type, $contents_type);
			
			$this->insertContentDetail($contents_id, $contents_type, $req);
			
			if ($req['Type'] == CONTENTS_TYPE_MV_NAME && isset($req['ImageList']) && !empty($req['ImageList'])) {
				// 動画の場合、画像があれば登録
				foreach ($req['ImageList'] as $image_key => $image_val) {
					$contents_id = $this->insertContent($import_type, CONTENTS_TYPE_IM);
					
					$image_val['ContentsParentId'] = $req['Id'];
					$image_val['SubId'] = ($image_key + 1);
					
					$this->insertContentDetail($contents_id, CONTENTS_TYPE_IM, $image_val);
				}
			}
			
			$this->conn->commit();
		} catch (DbAccessException $e) {
			$this->conn->rollback();
			Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
			throw $e;
		} catch (\Exception $e) {
			$this->conn->rollback();
			Log::batch($e->getFile(), $e->getLine(), $e->getMessage());
			throw $e;
		}
		
		Log::batch(__FILE__, __LINE__, 'Dedub::creatContent() End.');
	}
}
