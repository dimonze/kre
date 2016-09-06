<?php

/**
 * page components.
 *
 * @package    kre
 * @subpackage page
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $el
 */
class pageComponents extends sfComponents
{
  public function executeLatestNews()
  {
    $this->executeLatest(Page::NEWS_ID, 2);
  }

  public function executeLatestReviews()
  {
    $this->executeLatest(Page::REVIEW_ID, 1);
  }

  private function executeLatest($parent_id, $limit)
  {
    $this->items = Doctrine::getTable('Page')->getLatest($parent_id, $limit);
  }
}