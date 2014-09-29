<?php
namespace Kitpages\CmsBundle\Model;

class Paginator {
    protected $itemList = array();
    protected $totalItemCount = 0;

    protected $currentPage = 1;
    protected $itemCountPerPage = 20;
    protected $visiblePageCountInPaginator = 5;
    protected $urlTemplate = null;

    protected $isCalculated = false;

    protected $totalPageCount = null;
    protected $minPage = null;
    protected $maxPage = null;
    protected $nextButtonPage = null;
    protected $previousButtonPage = null;

    public function __construct()
    {
    }

    ////
    // init and configuration
    ////
    public function setCurrentPage($page)
    {
        $this->currentPage = $page;
        return $this;
    }
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function setItemCountPerPage($itemCount)
    {
        $this->itemCountPerPage = $itemCount;
        return $this;
    }

    public function setVisiblePageCountInPaginator($pageCount)
    {
        $this->visiblePageCountInPaginator = $pageCount;
        return $this;
    }

    public function setTotalItemCount($totalItemCount)
    {
        $this->totalItemCount = $totalItemCount;
        return $this;
    }

    public function getTotalItemCount()
    {
        return $this->totalItemCount;
    }

    public function getItemCountPerPage()
    {
        return $this->itemCountPerPage;
    }

    /**
     * @param string $urlTemplate with _PAGE_ that will be replaced by page number
     */
    public function setUrlTemplate($urlTemplate)
    {
        $this->urlTemplate = $urlTemplate;
    }

    public function getUrlTemplate()
    {
        return $this->urlTemplate;
    }

    ////
    // get results
    ////
    public function getSqlLimitOffset()
    {
        $offset = ($this->currentPage - 1) * $this->itemCountPerPage;
        return $offset;
    }

    public function getTotalPageCount()
    {
        if ($this->isCalculated == false) {
            $this->calculate();
        }
        return $this->totalPageCount;
    }

    public function getMinPage()
    {
        if ($this->isCalculated === false) {
            $this->calculate();
        }
        return $this->minPage;
    }
    public function getMaxPage()
    {
        if ($this->isCalculated === false) {
            $this->calculate();
        }
        return $this->maxPage;
    }
    public function getPreviousButtonPage()
    {
        if ($this->isCalculated === false) {
            $this->calculate();
        }
        return $this->previousButtonPage;
    }
    public function getNextButtonPage()
    {
        if ($this->isCalculated === false) {
            $this->calculate();
        }
        return $this->nextButtonPage;
    }

    public function getPageRange()
    {
        if ($this->isCalculated === false) {
            $this->calculate();
        }
        $tab = array();
        for ($i = $this->minPage ; $i <= $this->maxPage ; $i++) {
            $tab[] = $i;
        }
        return $tab;
    }

    public function calculate()
    {
        // calculate total page count
        if ($this->totalItemCount == 0) {
            $this->totalPageCount = 0;
        }
        else {
            $this->totalPageCount = (int)((($this->totalItemCount - 1) / $this->itemCountPerPage) + 1);
        }
        // calculate nbPageLeft and nbPageRight
        $nbPageLeft = (int)($this->visiblePageCountInPaginator / 2);
        $nbPageRight = $this->visiblePageCountInPaginator - 1 - $nbPageLeft ;

        // calculate firstPage to display
        $minPage = max(1, $this->currentPage - $nbPageLeft);
        // calculate lastPage to display
        $maxPage = min($this->totalPageCount,$this->currentPage + $nbPageRight);
        // adapt minPage and maxPage
        $minPage = max(1, $maxPage-($this->visiblePageCountInPaginator - 1));
        $maxPage = min($this->totalPageCount, $minPage + ($this->visiblePageCountInPaginator - 1));

        // calculate previousButton
        if ($this->currentPage == 1) {
            $previousButtonPage = null;
        }
        else {
            $previousButtonPage = $this->currentPage - 1;
        }

        // calculate nextButton
        if ($this->currentPage == $this->totalPageCount) {
            $nextButtonPage = null;
        }
        else {
            $nextButtonPage = $this->currentPage + 1;
        }

        $this->minPage = $minPage;
        $this->maxPage = $maxPage;
        $this->previousButtonPage = $previousButtonPage;
        $this->nextButtonPage = $nextButtonPage;
        $this->isCalculated = true;
    }
}

?>
