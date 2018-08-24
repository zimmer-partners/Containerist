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
    
}