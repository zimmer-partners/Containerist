<?php 
  
/**
* Generic pseudo-abstract Kontainerist page model
*/
class ContaineristPage extends Page {
    
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