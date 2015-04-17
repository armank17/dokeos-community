<?php
/* For licensing terms, see /license.txt */

/**
 * Pagination helper
 */
class PaginationHelper 
{
    private $page = 1; // Current Page
    private $perPage = 10; // Items on each page, defaulted to 10
    private $showFirstAndLast = false; // if you would like the first and last page options.
    private $displayNumbers = false;
    
    /**
     * Constructor
     */
    public function __construct() {}
    
    /**
     * Create a pagination helper object
     * @return object
     */
    public static function create() {
        return new PaginationHelper();
    }
    
    /**
     * Generate a pagination from array 
     */
    public function generate($array, $perPage = 10, $showFirstAndLast = false, $displayNumbers = false) {
        
      // Assign the items per page variable
      if (!empty($perPage)) { $this->perPage = $perPage; }
      $this->showFirstAndLast = $showFirstAndLast;
      $this->displayNumbers   = $displayNumbers;
      
      // Assign the page variable
      if (!empty($_GET['page'])) {
        $this->page = $_GET['page']; // using the get method
      } else {
        $this->page = 1; // if we don't have a page number then assume we are on the first page
      }
      
      // Take the length of the array
      $this->length = count($array);
      
      // Get the number of pages
      $this->pages = ceil($this->length / $this->perPage);
      
      // Calculate the starting point 
      $this->start  = ceil(($this->page - 1) * $this->perPage);
      
      // Return the part of the array we have requested
      return array_slice($array, $this->start, $this->perPage, true);
    }
    
    public function prevAndNext()
    {
		
		return array(($this->page!= 1?$this->page-1:false),($this->page < $this->pages?$this->page+1:false));
		
	}
    
    
    /**
     * Return links for the pagination
     * @return  string  pagination links
     */
    public function links() {
      // Initiate the links array
      $plinks = array();
      $links = array();
      $slinks = array();
      
      // Concatenate the get variables to add to the page numbering string
      if (count($_GET)) {
        $queryURL = '';
        foreach ($_GET as $key => $value) {            
          if ($key != 'page' && $key != 'action' && $key != 'id') {
            $queryURL .= '&'.$key.'='.$value;
          }
        }
      }
      
      // If we have more then one pages
      if (($this->pages) > 1) {
        // Assign the 'previous page' link into the array if we are not on the first page
        if ($this->page != 1) {
          if ($this->showFirstAndLast) {
            $plinks[] = ' <a href="?page=1'.$queryURL.'">'.Display::return_icon('slide_first.png').'</a> ';
          }
          $plinks[] = ' <a href="?page='.($this->page - 1).$queryURL.'">'.Display::return_icon('slide_previous.png').'</a> ';
        }
        
        // Assign all the page numbers & links to the array
        if ($this->displayNumbers) {
            for ($j = 1; $j < ($this->pages + 1); $j++) {
              if ($this->page == $j) {
                $links[] = ' <a class="selected">'.$j.'</a> '; // If we are on the same page as the current item
              } else {
                $links[] = ' <a href="?page='.$j.$queryURL.'">'.$j.'</a> '; // add the link to the array
              }
            }
        }
  
        // Assign the 'next page' if we are not on the last page
        if ($this->page < $this->pages) {
          $slinks[] = ' <a href="?page='.($this->page + 1).$queryURL.'">'.Display::return_icon('slide_next.png').'</a> ';
          if ($this->showFirstAndLast) {
            $slinks[] = ' <a href="?page='.($this->pages).$queryURL.'">'.Display::return_icon('slide_last.png').'</a> ';
          }
        }
        
        // Push the array into a string using any some glue
        return implode(' ', $plinks).implode($this->implodeBy, $links).implode(' ', $slinks);
      }
      return;
    }
    
}

?>
