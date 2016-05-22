<?php

  // First retrieve the pageList
  $pageList = $vars['pageList'];

  // The cycle through all and print the sidebar links
foreach ($pageList->getPages() as $page) {
    echo "<li name='sidebar:".$page->getIdentifier().'/'.$page->getPagePath()."'><a href='".$vars['adminURL'].$page->getIdentifier().'/'.$page->getPagePath()."'>
        <i class='".($page->getIcon() == '' ? '' : 'fa '.$page->getIcon())."'></i> <span>".$page->getName().'</span></a></li>';
}
