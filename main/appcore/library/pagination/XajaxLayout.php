<?php
class appcore_pagination_XajaxLayout implements appcore_pagination_PageLayout {

	public function fetchPagedLinks($parent, $queryVars,$fst='',$params=array()) {
	
		$currentPage = $parent->getPageNumber();
		$str = "";
		
		$strparams='';
		//$params
		foreach ($params as $val){ 
			if(substr($val,0,6) == 'xajax.')
			$strparams .= "$val,"; 	else
			$strparams .= "'$val',"; 	
		}
		if(!$parent->isFirstPage()) {
			if($currentPage != 1 && $currentPage != 2 && $currentPage != 3) {
					$str .= "<a href=\"\" onclick=\"$fst($strparams'1');return false;\" title='Start'> &#171; Primero </a>  ";
			}
		}

		//write statement that handles the previous and next phases
	   	//if it is not the first page then write previous to the screen
		if(!$parent->isFirstPage()) {
			$previousPage = $currentPage - 1;
			$str .= "<a href=\"\" onclick=\"$fst($strparams'$previousPage');return false;\">&lt; anterior</a> ";
		}

		for($i = $currentPage - 3; $i <= $currentPage + 3; $i++) {
			//if i is less than one then continue to next iteration		
			if($i < 1) {
				continue;
			}
	
			if($i > $parent->fetchNumberPages()) {
				break;
			}
	
			if($i == $currentPage) {
				$str .= " <span class='activo'> $i </span> ";
			}
			else {
				$str .= "<a href=\"\" onclick=\"$fst($strparams'$i');return false;\">$i</a>";
			}
			($i == $currentPage + 3 || $i == $parent->fetchNumberPages()) ? $str .= " " : $str .= " | ";              //determine if to print bars or not
		}//end for

  if(!$parent->isLastPage() ) {
                        $nextPage = $currentPage + 1;
                        $str .= "<a href=\"\" onclick=\"$fst($strparams'$nextPage');return false;\">siguiente &gt;</a>";
                }


		if (!$parent->isLastPage()) {
			if($currentPage != $parent->fetchNumberPages() && $currentPage != $parent->fetchNumberPages() -1 && $currentPage != $parent->fetchNumberPages() - 2)
			{
				$str .= " <a href=\"\" onclick=\"$fst($strparams'{$parent->fetchNumberPages()}');return false;\" title=\"Last\"> &Uacute;ltimo(".$parent->fetchNumberPages().") &#187; </a>";
			}
		}

/*		if(!$parent->isLastPage() ) {
			$nextPage = $currentPage + 1;
			$str .= "<a href=\"\" onclick=\"$fst($strparams'$nextPage');return false;\">siguiente &gt;</a>";
		}
*/
		return $str;
	}
}
?>
