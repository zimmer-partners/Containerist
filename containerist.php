<?php

// ===========
// = Helpers =
// ===========

function datespan($field_from, $field_to) {
  if ($field_to->isNotEmpty()) {
    $html_time_format = '%Y-%m-%dT12:00:00';
    $from = new DateTime();
    $to = new DateTime();
    $from->setTimestamp($field_from);
    $to->setTimestamp($field_to);
    $diff = $from->diff($to);
    if (!empty($diff->m) && $diff->m > 1) {
      $print_date_format = '%B %Y';
    } else {
      $print_date_format = '%d.%m.%y';
    }
    $fromprint = strftime($print_date_format, $from->getTimestamp());
    $fromstamp = strftime($html_time_format, $from->getTimestamp());
    $toprint = strftime($print_date_format, $to->getTimestamp());
    $tostamp = strftime($html_time_format, $to->getTimestamp());
    return "<time datetime=\"$fromstamp\">$fromprint</time> - <time datetime=\"$tostamp\">$toprint</time>";
  } else {
    return datestamp($field);
  }
}

// Output date field as HTML timestamp
function datestamp($field, $variables = array()) {
  $dateprint = strftime('%d.%m.%y', $field);
  $datestamp = strftime('%Y-%m-%dT12:00:00', $field);
  return "<time datetime=\"$datestamp\">$dateprint</time>";
};

// =================
// = Field Methods =
// =================

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

