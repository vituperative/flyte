<?php
if (ob_get_level() == 0) ob_start();
require_once("include/scrape_class.php");
$scraper = new Scraper();
print($scraper->doScrape());

?>
