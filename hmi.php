<?php
include_once  './inc/class-site-index-parser.php';

$url = 'https://www.vinsolutions.com/sitemap.xml';
// $url = 'http://localhost/cf7/sitemap.xml';
// print_r($argv);
$scraper = new SiteIndexParser();
// $scraper->quiet = true;
$results = array(''=>$scraper->scrape('https://www.vinsolutions.com/'));
// $results = $scraper->scan($url);
echo 'page,'.implode(',',$scraper->metatags).PHP_EOL;
foreach($results as $p=>$vs){
  echo '"'.$p.'","'.implode('","',$vs).'"'.PHP_EOL;
}

?>
