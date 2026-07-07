<?php
define('CLI_SCRIPT', true);
require('C:\Moodle\server\moodle\config.php');
require_once('C:\Moodle\server\moodle\completion\classes\form\form_trait.php');
require_once('C:\smartcards\mod_form.php');

$form = new mod_adaptivereview_mod_form(null, null, 'post', '', null, true, null);
$form->definition();

// Check the form elements that Moodle built dynamically under completion
$mform = $form->getform();
foreach ($mform->_elements as $el) {
    if (strpos($el->getName(), 'completion') !== false) {
         echo $el->getName() . " (" . $el->getType() . ")\n";
    }
}
