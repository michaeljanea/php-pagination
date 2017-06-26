<?php
/*
	Class Name		: MJ_Pagination
	Description		: simple pagination script
	Pre-requisites	: Bootstrap Plugin
	Version			: 2.0
*/

class MJ_Pagination{
	
	/* variables that can be change */
	var $pageSize = 5;												//page size
	var $ilanLangBa = 5;											//number of buttons to be displayed
	var $prevLabel = '&lt; Prev';									//previous button label
	var $nextLabel = 'Next &gt;';									//next button label
	var $btnContainerClass = 'btn-group btn-group-sm pull-right';	//class of button container
	var $btnClass = 'btn btn-default hidden-xs';					//class of buttons
	var $btnActiveClass = 'active';									//class of active button
	var $detailsContainerClass = 'marginBottom pull-left';			//class of details container
	var $dropdownClass = 'dropdownPagination displayNone';			//class of dropdown pagination
	
	/* do not alter the variables below */
	public $currentPage = 0;										//current page
	public $page = 1;												//current page in url
	private $totRec = 0;											//total records found
	private $allPage = 0;											//total pages
	private $prevPage, $nextPage;									//previous and next page
	private $statement = '';										//query variable for all items
	private $query = '';											//query variable for displaying items
	private $result = array();										//array result
	
	function __construct(){
		
		//setting current page
		$this->currentPage = empty($_GET['page']) ? 0 : ((int)$_GET['page'] > 0 ? (int)$_GET['page'] - 1 : 0);
		$this->currentPage = $this->currentPage < 0 ? 0 : $this->currentPage;
		$this->page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
		$this->page = $this->page < 1 ? 1 :$this->page;
		
	}
	
	public function paginate($fields='*', $tableName, $where='', $where2='', $order='', $sort=''){

		//pdo connection
		global $pdo;
		
		//order by
		if(!empty($order))
			$order = " ORDER BY " . $order . " " . $sort;
		
		//where clause
		if(!empty($where))
			$where = " WHERE " . $where;
		
		//query statement
		$this->statement = "SELECT $fields FROM $tableName $where $order";
		//echo $this->statement;
		//die($this->statement);
		
		//query for pagination
		$sql = $pdo->prepare($this->statement);
		empty($where2) ? $sql->execute() : $sql->execute($where2);
		
		//total records
		$this->totRec = $sql->rowCount();
		
		//total pages
		$this->allPage = ceil($this->totRec / $this->pageSize);
		
		//previous 5 pages button
		$this->prevPage = (floor($this->page / $this->ilanLangBa) * $this->ilanLangBa) + 1;
		$this->prevPage = $this->prevPage < 1 ? 1 : $this->prevPage;
		
		//next 5 pages butto
		$this->nextPage = ceil($this->page / $this->ilanLangBa) * $this->ilanLangBa;
		$this->nextPage = $this->nextPage > $this->allPage ? $this->allPage : $this->nextPage;
		
		//redeclare previous page if page is equal or greater than the next page
		$this->prevPage = $this->prevPage >= $this->nextPage ? $this->prevPage - $this->ilanLangBa : $this->prevPage;
		
		//actual query
		$this->query = $pdo->prepare($this->statement." LIMIT ".($this->currentPage * $this->pageSize).", ".$this->pageSize);
		//echo $this->statement." LIMIT ".($this->currentPage * $this->pageSize).", ".$this->pageSize;
		empty($where2) ? $this->query->execute() : $this->query->execute($where2);
		$this->result = $this->query->fetchAll();
		
		//result
		return $this->result;

	}
	
	public function links(){
		
		//form opening tag
		$links = '<form action="" method="post">';
		
		//display pagination if total records is greater than 0
		if($this->totRec != 0){
			
			//pagination details and opening tag for pagination buttons
			$links .= '
				<div' . (!empty($this->detailsContainerClass) ? ' class="' . $this->detailsContainerClass . '"' : '') . '>
					<div>
						Displaying ' . (($this->currentPage * $this->pageSize) + 1) . '-' . (($this->currentPage * $this->pageSize) + $this->query->rowCount()) . ' of ' . $this->totRec . ' items
					</div>
				</div>
				
				<div' . (!empty($this->btnContainerClass) ? ' class="' . $this->btnContainerClass . '"' : '') . '>
					<div class="visible-lg visible-md">
						<div class="btn-group btn-group-sm">
			';
			
			//display pagination button if total records is greater than 1
			if($this->allPage > 1){
				
				//display previous label if current page is greater than 1
				if($this->page > 1)
					$links .= '<input type="button"' . (!empty($this->btnClass) ? ' class="' . $this->btnClass . '"' : '') . ' value="' . $this->prevLabel . '" onclick="location.href=\'' . getQueryString(array('page' => $this->page - 1)) . '\'" />';
					
				if($this->prevPage > 1 && $this->allPage > $this->ilanLangBa){
					
					//first page button
					$links .= '<input type="button"' . (!empty($this->btnClass) ? ' class="' . $this->btnClass . '"' : '') . ' value="1" onclick="location.href=\'' . getQueryString(array('page' => 1)) . '\'" />';
					
					if($this->prevPage != 1){
					
						//previous group of buttons
						$links .= '<input type="button"' . (!empty($this->btnClass) ? ' class="' . $this->btnClass . '"' : '') . ' value="..." onclick="location.href=\'' . getQueryString(array('page' => $this->prevPage - 1)) . '\'" />';
					
					}
					
				}

				//display pages in button form
				for($i = $this->prevPage; $i <= $this->nextPage; $i++){
					
					$links .= '<input type="button"' . (!empty($this->btnClass) ? ' class="' . $this->btnClass . ($this->page == $i ? ' ' . $this->btnActiveClass : '') . '"' : '') . ' value="' . $i . '" onclick="location.href=\'' . getQueryString(array('page' => $i)) . '\'" />';
					
				}
				
				if($this->nextPage < ($this->allPage - 1) && $this->allPage > $this->ilanLangBa){
					
					if($this->nextPage != ($this->allPage - 1)){
					
						//next group of buttons
						$links .= '<input type="button"' . (!empty($this->btnClass) ? ' class="' . $this->btnClass . '"' : '') . ' value="..." onclick="location.href=\'' . getQueryString(array('page' => $this->nextPage + 1)) . '\'" />';
					
					}

					//last page button
					$links .= '<input type="button"' . (!empty($this->btnClass) ? ' class="' . $this->btnClass . '"' : '') . ' value="' . $this->allPage . '" onclick="location.href=\'' . getQueryString(array('page' => $this->allPage)) . '\'" />';
					
				
				}
			
			}
			
			//display next button if current page is less than the total pages
			if($this->page < $this->allPage)
				$links .= '<input type="button"' . (!empty($this->btnClass) ? ' class="' . $this->btnClass . '"' : '') . ' value="' . $this->nextLabel . '" onclick="location.href=\'' . getQueryString(array('page' => $this->page + 1)) . '\'" />';
			
			$links .= '
						</div>
					</div>
					<div class="hidden-lg hidden-md">
						<div' . (!empty($this->dropdownClass) ? ' class="' . $this->dropdownClass . '"' : '') . ' >
							<div class="btn-group btn-group-sm">
								<button type="button" class="btn btn-default" onclick="location.href=\'' . getQueryString(array('page' => $this->page - 1)) . '\'">
									' . $this->prevLabel . '
								</button>
								<button class="btn btn-default" disabled>
									Page ' . $this->page . ' of ' . $this->allPage . '
								</button>
								<button type="button" class="btn btn-default" onclick="location.href=\'' . getQueryString(array('page' => $this->page + 1)) . '\'">
									' . $this->nextLabel . '
								</button>
							</div>
						</div>
					</div>
				</div>
			';
		
		}
		
		//form closing tag
		$links .= '</form>';
		
		return $links;
		
	}
	
}

$pagination = new MJ_Pagination();