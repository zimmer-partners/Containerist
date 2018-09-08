<?php 
  
/**
* Generic pseudo-abstract Kontainerist page model
*/
class ContaineristPage extends Page {
  
  protected $blueprint;

  // ===============
  // = Constructor =
  // ===============
  
  public function __construct($parent, $dirname) {
    
    parent::__construct($parent, $dirname);
        
  }
  
  protected function setBlueprint($blueprint_name) {
    
    // Load and cache the blueprint
    $this->blueprint = data::read(kirby()->get('blueprint', $blueprint_name), 'yaml');
    // Load and add global field definitions to chached blueprint
    ContaineristPage::extendBlueprint($this->blueprint);

  }
  
  protected static function extendBlueprint(&$blueprint) {
    
    foreach ($blueprint as $key => &$item) {
      if (is_array($item) && isset($item['extends'])) {
        $file = kirby()->get('blueprint', 'fields/' . $item['extends']);
        if(empty($file) || !is_file($file)) {
          throw new Exception(l('fields.error.extended') . ' "' . $item['extends'] . '"');
        }
        $item = data::read($file, 'yaml');
      } elseif (is_array($item)) {
        ContaineristPage::extendBlueprint($item);
      }
    }
    
  }
  
  // =================
  // = Pseudo Fields =
  // =================
  
  public function excerpt($length = 240, $mode = 'chars') {
    $excerpt_field = $this->content()->get('excerpt');
    if ($excerpt_field->isNotEmpty() && $excerpt_field->isTranslated()) {
      return $excerpt_field;
    } else {
      $text_field = $this->content()->get('text');
      if ($text_field->isNotEmpty()) {
        return new Field($this, 'excerpt', $text_field->excerpt($length, $mode));
      } else {
        return $text_field;
      }
    }
  }
  
  // ===========
  // = Helpers =
  // ===========
  
  public static function datespan($field_from, $field_to) {
    if ($field_to->isNotEmpty()) {
      $html_time_format = '%Y-%m-%dT12:00:00';
      $from = new DateTime($field_from);
      $to = new DateTime($field_to);
      $diff = $from->diff($to);
      // print("DateInterval is as follows:");
      // print_r($diff);
      $print_date_format = '%d.%m.%y';
      if ($diff->m > 12) {
        $print_date_format = '%Y';
      } else if ($diff->m >= 11 && $diff->d >= 30) {
        $print_date_format = '%Y';
        $fromprint = strftime($print_date_format, $from->getTimestamp());
        $fromstamp = strftime($html_time_format, $from->getTimestamp());
        $toprint = strftime($print_date_format, $to->getTimestamp());
        $tostamp = strftime($html_time_format, $to->getTimestamp());
        return "<time datetime=\"$tostamp\">$toprint</time>";
      } else if (($diff->m == 1 && $diff->d > 0) || $diff->m > 1) {
        $print_date_format = '%B %Y';
      }
      $fromprint = strftime($print_date_format, $from->getTimestamp());
      $fromstamp = strftime($html_time_format, $from->getTimestamp());
      $toprint = strftime($print_date_format, $to->getTimestamp());
      $tostamp = strftime($html_time_format, $to->getTimestamp());
      return "<time datetime=\"$fromstamp\">$fromprint</time> - <time datetime=\"$tostamp\">$toprint</time>";
    } else {
      return ContaineristPage::datestamp($field_from);
    }
  }
  
  // Output date field as HTML timestamp
  public static function datestamp($field, $variables = array()) {
    $from = new DateTime($field);
    $dateprint = strftime('%d.%m.%y', $from->getTimestamp());
    $datestamp = strftime('%Y-%m-%dT12:00:00', $from->getTimestamp());
    return "<time datetime=\"$datestamp\">$dateprint</time>";
  }
  
  // Original Title Case script © John Gruber <daringfireball.net>
  // Javascript port © David Gouch <individed.com>
  // PHP port of the above by Kroc Camen <camendesign.com>
  
  public static function titleCase($title) {
  	//remove HTML, storing it for later
  	//       HTML elements to ignore    | tags  | entities
  	$regx = '/<(code|var)[^>]*>.*?<\/\1>|<[^>]+>|&\S+;/';
  	preg_match_all ($regx, $title, $html, PREG_OFFSET_CAPTURE);
  	$title = preg_replace ($regx, '', $title);
  	
  	//find each word (including punctuation attached)
  	preg_match_all ('/[\w\p{L}&`\'‘’"“\.@:\/\{\(\[<>_]+-? */u', $title, $m1, PREG_OFFSET_CAPTURE);
  	foreach ($m1[0] as &$m2) {
  		//shorthand these- "match" and "index"
  		list ($m, $i) = $m2;
  		
  		//correct offsets for multi-byte characters (`PREG_OFFSET_CAPTURE` returns *byte*-offset)
  		//we fix this by recounting the text before the offset using multi-byte aware `strlen`
  		$i = mb_strlen (substr ($title, 0, $i), 'UTF-8');
  		
  		//find words that should always be lowercase…
  		//(never on the first word, and never if preceded by a colon)
  		$m = $i>0 && mb_substr ($title, max (0, $i-2), 1, 'UTF-8') !== ':' && 
  			!preg_match ('/[\x{2014}\x{2013}] ?/u', mb_substr ($title, max (0, $i-2), 2, 'UTF-8')) && 
  			 preg_match ('/^(a(nd?|s|t)?|b(ut|y)|en|for|i[fn]|o[fnr]|t(he|o)|vs?\.?|via)[ \-]/i', $m)
  		?	//…and convert them to lowercase
  			mb_strtolower ($m, 'UTF-8')
  			
  		//else:	brackets and other wrappers
  		: (	preg_match ('/[\'"_{(\[‘“]/u', mb_substr ($title, max (0, $i-1), 3, 'UTF-8'))
  		?	//convert first letter within wrapper to uppercase
  			mb_substr ($m, 0, 1, 'UTF-8').
  			mb_strtoupper (mb_substr ($m, 1, 1, 'UTF-8'), 'UTF-8').
  			mb_substr ($m, 2, mb_strlen ($m, 'UTF-8')-2, 'UTF-8')
  			
  		//else:	do not uppercase these cases
  		: (	preg_match ('/[\])}]/', mb_substr ($title, max (0, $i-1), 3, 'UTF-8')) ||
  			preg_match ('/[A-Z]+|&|\w+[._]\w+/u', mb_substr ($m, 1, mb_strlen ($m, 'UTF-8')-1, 'UTF-8'))
  		?	$m
  			//if all else fails, then no more fringe-cases; uppercase the word
  		:	mb_strtoupper (mb_substr ($m, 0, 1, 'UTF-8'), 'UTF-8').
  			mb_substr ($m, 1, mb_strlen ($m, 'UTF-8'), 'UTF-8')
  		));
  		
  		//resplice the title with the change (`substr_replace` is not multi-byte aware)
  		$title = mb_substr ($title, 0, $i, 'UTF-8').$m.
  			 mb_substr ($title, $i+mb_strlen ($m, 'UTF-8'), mb_strlen ($title, 'UTF-8'), 'UTF-8')
  		;
  	}
  	
  	//restore the HTML
  	foreach ($html[0] as &$tag) $title = substr_replace ($title, $tag[0], $tag[1], 0);
  	return $title;
  }  
  
}