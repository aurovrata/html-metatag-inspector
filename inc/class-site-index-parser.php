<?php
/**
* Class to extract site index urls for a given domain.
* @since 0.1
*/


class SiteIndexParser {
  /**
  * home url to scan for sitemap.
  * @since 0.1
  * @access private
  * @var String
  */

  private $home_url=null;

  public $debug=false;

  public $quiet = false;

  public $error = 0;

  public $metatags =array('title','og:title','description','og:description', 'og:image');

  public function __construct() {}

  public function scan($url){
    if(substr($url, -4) !== '.xml'){
      echo "Error, use scan function with a sitemap link";
      return array();
    }

    $this->doing("fetching sitemap $url");
    $source = file_get_contents($url);
    $results = array();
    if($file && $this->error==0){
      $this->doing("extracting sitemap");
      $xml = simplexml_load_string($source);
       // print_r($xml->children());
      if(isset($xml->sitemap)){ //keep scanning.
        foreach($xml->children() as $x) $results = array_merge($results, $this->scan($x->loc));
      }else if(isset($xml->url)){ //scrape.
        $this->doing('scrapping pages');
        foreach($xml->children() as $x) {
          $p = (string) $x->loc;
          $results[$p] = $this->scrape($p);
        }

      }
    }
    $this->doing('terminating');
    return $results;
  }
  /**
  * scrapes a page for the metatags configured.
  * @since 0.1
  * @param String $page_url URL of page to scrape.
  * @return Array of values for each metatags found in $this->metatags.
  */
  public function scrape($page){
    $source = file_get_contents($page);
    $tags = $this->get_metatags($source);
    // $tags = get_meta_tags($page);
    // print_r($tags);
    $scrape = array();
    if(empty($tags)) return $scrape;
    foreach($this->metatags as $t){
      if(!isset($tags[$t])) $v='';
      else $v = $tags[$t];

      if(empty($v)) $this->error +=1;
      array_push( $scrape, $v);
    }
    return $scrape;
  }

  private function doing($step){
    if(!$this->quiet){
      if($this->error) echo "...found ({$this->error}) errors".PHP_EOL;
      echo $step.PHP_EOL;
    }

    $this->error = 0;
    set_error_handler(function($enum, $emsg) use ($step){
      if($this->debug){
        echo "ERROR encountered while {$step}".PHP_EOL;
        echo "$emsg ($enum)".PHP_EOL;
      }
      $this->error +=1;
    });
  }
  protected function get_metatags($str){
    $pattern = '
    ~<\s*meta\s

    # using lookahead to capture type to $1
      (?=[^>]*?
      \b(?:name|property|http-equiv)\s*=\s*
      (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
      ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
    )

    # capture content to $2
    [^>]*?\bcontent\s*=\s*
      (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
      ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
    [^>]*>

    ~ix';

    if(preg_match_all($pattern, $str, $out))
      return array_combine($out[1], $out[2]);
    return array();
  }

}
