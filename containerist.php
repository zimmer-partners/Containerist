<?php
  
// ===================
// = Model Registery =
// ===================

require_once(__DIR__ . '/models/containerist.php');
$kirby->set('page::model', 'containerist', 'ContaineristPage');

// =================
// = Field Methods =
// =================

field::$methods['anchor'] = function($field, $variables = array()) {
  if (!$field->empty()) {
    return new Field($field->page(), $field->key() . '_anchor', '#' . str::slug($field));
  } else {
    return new Field($field->page(), $field->key() . '_anchor', '');
  }
  
};

// Loads and return a field snippet
field::$methods['snippet'] = function($field, $variables = array()) {
  if (!$field->empty()) {
    return snippet($field->intendedSnippet(), array_merge(array('field' => $field), $variables), true);
  }
};

// Returns the snippet name for a field
field::$methods['intendedSnippet'] = function($field) {
  return 'c-' . preg_replace('/^b\-/i', '', str::slug($field->key));
};

// Strips paragraph from Markdown processed HTML
field::$methods['kirbytexter'] = function($field) {
  $field->value = preg_replace(
    array(
      '/ *<\/p>\Z/i',
      '/ *<p> */i',
      '/ *<\/p> */i',
      '/\n/'
    ),
    array(
      '',
      '',
      '<br /><br />',
      ''
    ), $field->kirbytext());
    return $field;
};

