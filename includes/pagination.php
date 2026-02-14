<?php
class Pagination {
    private $total_records;
    private $records_per_page;
    private $current_page;
    private $total_pages;
    
    public function __construct($total_records, $records_per_page = 10, $current_page = 1) {
        $this->total_records = $total_records;
        $this->records_per_page = $records_per_page;
        $this->current_page = max(1, (int)$current_page);
        $this->total_pages = ceil($total_records / $records_per_page);
    }
    
    public function getOffset() {
        return ($this->current_page - 1) * $this->records_per_page;
    }
    
    public function getLimit() {
        return $this->records_per_page;
    }
    
    public function getCurrentPage() {
        return $this->current_page;
    }
    
    public function getTotalPages() {
        return $this->total_pages;
    }
    
    public function render($url_pattern) {
        if ($this->total_pages <= 1) {
            return '';
        }
        
        $html = '<div class="pagination">';
        
        // Previous button
        if ($this->current_page > 1) {
            $html .= '<a href="' . str_replace('{page}', $this->current_page - 1, $url_pattern) . '" class="page-link">&laquo; Previous</a>';
        }
        
        // Page numbers
        $start = max(1, $this->current_page - 2);
        $end = min($this->total_pages, $this->current_page + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $active = $i == $this->current_page ? 'active' : '';
            $html .= '<a href="' . str_replace('{page}', $i, $url_pattern) . '" class="page-link ' . $active . '">' . $i . '</a>';
        }
        
        // Next button
        if ($this->current_page < $this->total_pages) {
            $html .= '<a href="' . str_replace('{page}', $this->current_page + 1, $url_pattern) . '" class="page-link">Next &raquo;</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
?>