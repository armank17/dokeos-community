<?php

class appcore_library_pagination_Pagination {
    public $items_per_page;
    public $items_total;
    public $current_page;
    public $num_pages;
    public $mid_range;
    public $low;
    public $high;
    public $limit;
    public $return;
    public $base_url;
    public $default_ipp = 15;
    public $url_info = array();

    public function __construct() {
        $this->current_page = 1;
        $this->mid_range = 7;
        $this->items_per_page = (!empty($_GET['ipp'])) ? $_GET['ipp']:$this->default_ipp;        
        $this->url_info['base_url'] = api_get_self();
        $this->url_info['url_query'] = array();
    }

    public function paginate() {
        if ($_GET['ipp'] == 'All') {
            $this->num_pages = ceil($this->items_total/$this->default_ipp);
            $this->items_per_page = $this->default_ipp;
        }
        else {
            if(!is_numeric($this->items_per_page) OR $this->items_per_page <= 0) $this->items_per_page = $this->default_ipp;
            $this->num_pages = ceil($this->items_total/$this->items_per_page);
        }
        $this->current_page = (int) $_GET['page']; // must be numeric > 0
        if($this->current_page < 1 Or !is_numeric($this->current_page)) $this->current_page = 1;
        if($this->current_page > $this->num_pages) $this->current_page = $this->num_pages;
        $prev_page = $this->current_page-1;
        $next_page = $this->current_page+1;
        if ($this->num_pages > 10) {
            $this->url_info['url_query']['page'] = $prev_page;
            $this->url_info['url_query']['ipp'] = $this->items_per_page;           
            $this->return = ($this->current_page != 1 And $this->items_total >= 10) ? "<a class='paginate' href='".$this->url_info['base_url']."?".http_build_query($this->url_info['url_query'])."'><< ". api_utf8_encode(get_lang('PreviousPage'))."</a> ":"<span class='inactive' href='#'><< ". api_utf8_encode(get_lang('PreviousPage'))."</span> ";
            $this->start_range = $this->current_page - floor($this->mid_range/2);
            $this->end_range = $this->current_page + floor($this->mid_range/2);
            if ($this->start_range <= 0) {
                $this->end_range += abs($this->start_range)+1;
                $this->start_range = 1;
            }
            if ($this->end_range > $this->num_pages) {
                $this->start_range -= $this->end_range-$this->num_pages;
                $this->end_range = $this->num_pages;
            }
            $this->range = range($this->start_range,$this->end_range);
            for ($i=1;$i<=$this->num_pages;$i++) {
                if($this->range[0] > 2 And $i == $this->range[0]) $this->return .= " ... ";
                // loop through all pages. if first, last, or in range, display
                if ($i==1 Or $i==$this->num_pages Or in_array($i,$this->range)) {
                    $this->url_info['url_query']['page'] = $i; 
                    $this->url_info['url_query']['ipp'] = $this->items_per_page; 
                    $this->return .= ($i == $this->current_page And $_GET['ipp'] != 'All') ? "<a title='Go to page $i of {$this->num_pages}' class='current' href='#'>$i</a> ":"<a class='paginate' title='Go to page $i of {$this->num_pages}' href='".$this->url_info['base_url']."?".http_build_query($this->url_info['url_query'])."'>$i</a> ";
                }
                if ($this->range[$this->mid_range-1] < $this->num_pages-1 And $i == $this->range[$this->mid_range-1]) $this->return .= " ... ";
            }            
            $this->url_info['url_query']['page'] = $next_page; 
            $this->url_info['url_query']['ipp'] = $this->items_per_page;            
            $this->return .= (($this->current_page != $this->num_pages And $this->items_total >= 10) And ($_GET['ipp'] != 'All')) ? "<a class='paginate' href='".$this->url_info['base_url']."?".http_build_query($this->url_info['url_query'])."'>".get_lang('Next')." >></a>\n":"<span class='inactive' href='#'> >>".get_lang('Next')."</span>\n";
            $this->url_info['url_query']['page'] = 1; 
            $this->url_info['url_query']['ipp'] = 'All';
            $this->return .= ($_GET['ipp'] == 'All') ? "<a class='current' style='margin-left:10px' href='#'>".get_lang('All')."</a> \n":"<a class='paginate' style='margin-left:10px' href='".$this->url_info['base_url']."?".http_build_query($this->url_info['url_query'])."'>".get_lang('All')."</a> \n";
        }
        else {
            for ($i=1;$i<=$this->num_pages;$i++) {
                $this->url_info['url_query']['page'] = $i; 
                $this->url_info['url_query']['ipp'] = $this->items_per_page;
                $this->return .= ($_GET['ipp'] != 'All' && $i == $this->current_page) ? "<a class='current' href='#'>$i</a> ":"<a class='paginate' href='".$this->url_info['base_url']."?".http_build_query($this->url_info['url_query'])."'>$i</a> ";
            }
            $this->url_info['url_query']['page'] = 1; 
            $this->url_info['url_query']['ipp'] = 'All';
            $this->return .= ($_GET['ipp'] == 'All') ? "<a class='current' style='margin-left:10px' href='#'>".get_lang('All')."</a> \n":"<a class='paginate' style='margin-left:10px' href='".$this->url_info['base_url']."?".http_build_query($this->url_info['url_query'])."'>".get_lang('All')."</a> \n";
        }
        $this->low = ($this->current_page-1) * $this->items_per_page;
        $this->high = ($_GET['ipp'] == 'All') ? $this->items_total:($this->current_page * $this->items_per_page)-1;
        $this->limit = ($_GET['ipp'] == 'All') ? "":" LIMIT $this->low,$this->items_per_page";
    }

    function display_items_per_page()
    {
        $items = '';
        $ipp_array = array(10,25,50,100,'All');
        foreach($ipp_array as $ipp_opt)    $items .= ($ipp_opt == $this->items_per_page) ? "<option selected value='$ipp_opt'>$ipp_opt</option>\n":"<option value='$ipp_opt'>$ipp_opt</option>\n";
        return "<span class=\"paginate\">Items per page:</span><select class=\"paginate\" onchange=\"window.location='$_SERVER[PHP_SELF]?page=1&ipp='+this[this.selectedIndex].value;return false\">$items</select>\n";
    }

    function display_jump_menu()
    {
        for($i=1;$i<=$this->num_pages;$i++)
        {
            $option .= ($i==$this->current_page) ? "<option value=\"$i\" selected>$i</option>\n":"<option value=\"$i\">$i</option>\n";
        }
        return "<span class=\"paginate\">Page:</span><select class=\"paginate\" onchange=\"window.location='$_SERVER[PHP_SELF]?page='+this[this.selectedIndex].value+'&ipp=$this->items_per_page';return false\">$option</select>\n";
    }

    function display_pages()
    {
        return $this->return;
    }
}
