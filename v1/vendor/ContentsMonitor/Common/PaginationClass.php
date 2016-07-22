<?php
namespace ContentsMonitor\Common;

/**
 * 監視サイトで使用するページネーション操作クラス
 *
 * @package ContentsMonitor
 * @author  FST goto
 * @since   PHP 5.6, Zendframwork 2.4.9
 * @version 1.0.0
 */
class PaginationClass
{
	private $pagename;
	private $maxcount;
	/** 最大件数 */
	function maxcount($value = null) {
		if (func_num_args()) $this->maxcount = $value;
		else return $this->maxcount;
	}
	private $maxpage;
	private $currentpage;
	/** 現在のページ */
	function currentpage($value = null) {
		if (func_num_args()) $this->currentpage = $value;
		else return $this->currentpage;
	}
	private $viewCount;
	/** 表示件数 */
	function viewCount($value = null) {
		if (func_num_args()) $this->viewCount = $value;
		else return $this->viewCount;
	}
	private $intPageTurnerViewStartPage;
	private $intPageTurnerViewEndPage;
	private $intLimitStart;    //開始件数
	/** 開始件数 */
	function intLimitStart() {
		if (func_num_args()) $this->intLimitStart = $value;
		else return $this->intLimitStart;
	}
	private $intLimitCount;    //終了件数
	/** 終了件数 */
	function intLimitCount() {
		if (func_num_args()) $this->intLimitCount = $value;
		else return $this->intLimitCount;
	}
	
	/**
	 * コンストラクタ
	 * @param string $pagename ページ名
	 */
	function __construct($pagename, $viewCount) {
		$this->pagename = $pagename;
		$this->maxcount = 0;
		$this->currentpage = 1;
		$this->viewCount = empty($viewCount) ?PAGE_COUNT:$viewCount;
	}
	
	/**
	 * ページ数などを計算
	 */
	function CalcPage() {
		if ($this->viewCount == 0) {
			$this->intLimitCount = null;
			$this->intLimitStart = null;
			$intLastPage = 1;
			$this->currentpage = $intLastPage;
			$this->maxpage = $intLastPage;
		} else {
			$this->intLimitCount = $this->viewCount * $this->currentpage;
			$this->intLimitStart = ($this->viewCount * $this->currentpage) - (($this->viewCount * $this->currentpage) / $this->currentpage);
			// 最終ページ算出
			$intLastPage = floor(($this->maxcount - 1) / $this->viewCount);
			if ($this->maxcount <= $this->intLimitStart) {
				// 表示ページが総件数を超える場合、最終ページを表示
				$this->intLimitStart = $intLastPage * $this->intLimitCount;
			}
			$this->maxpage = $intLastPage + 1;
		}
		
		if ($this->maxpage > 1) {
			$intPageTurnerViewListCount = PAGE_TURNER_LIST_VIEW_COUNT - 1;
				
			$this->intPageTurnerViewStartPage = $this->currentpage - floor($intPageTurnerViewListCount / 2);
			if ($this->intPageTurnerViewStartPage < 1) {
				$this->intPageTurnerViewStartPage = 1;
			}
			$this->intPageTurnerViewEndPage = $this->intPageTurnerViewStartPage + $intPageTurnerViewListCount;
			if ($this->intPageTurnerViewEndPage > $this->maxpage) {
				$this->intPageTurnerViewEndPage = $this->maxpage;
			}
			if (($this->intPageTurnerViewEndPage - $this->intPageTurnerViewStartPage) < $intPageTurnerViewListCount) {
				$this->intPageTurnerViewStartPage = $this->intPageTurnerViewEndPage - $intPageTurnerViewListCount;
			}
			if ($this->intPageTurnerViewStartPage < 1 ) {
				$this->intPageTurnerViewStartPage = 1;
			}
		}
	}
	
	/**
	 * ページネーション表示
	 */
	function Create() {
		if ($this->maxpage <= 1) return;

		require_once(dirname(__DIR__).'/Layout/pagination.php');
	}
	
	private function createPageTurnerHref($pageNo)
	{
		$params = $_GET;
		$params['p'] = $pageNo;
		$href = $this->pagename . '?' . 'formvalue=pagination&p='.$pageNo;
		return $href;
	}
}
