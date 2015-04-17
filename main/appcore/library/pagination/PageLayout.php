<?
/**
 * The interface which specifies the behaviour all page layout classes must implement
 * PageLayout is a part of Paginated and can reference programmer defined layouts
 */

interface appcore_pagination_PageLayout {
	public function fetchPagedLinks($parent, $queryVars,$fst='',$params=array());
}
?>
