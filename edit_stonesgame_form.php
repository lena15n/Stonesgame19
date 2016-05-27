<?php
/**
 * The editing form code for this question type.
 *
 * @copyright &copy; 2011 Universitat de Barcelona
 * @author jleyva@cvaconsulting.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ubhotspots
 *
 */

require_once($CFG->dirroot.'/question/type/edit_question_form.php');

/**
 * ubhotspots editing form definition.
 *
 * See http://docs.moodle.org/en/Development:lib/formslib.php for information
 * about the Moodle forms library, which is based on the HTML Quickform PEAR library.
 */
class question_edit_stonesgame_form extends question_edit_form {

    function definition_inner(&$mform) {
        global $CFG;

        $mform->addElement('header', 'stonesgameheader', get_string('stonesgame', 'qtype_stonesgame'));

        $mform->addElement('text', 'email', get_string('stonesgame')); // Add elements to your form
        $mform->setType('email', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('email', 'Please enter email');
        /*$mform->addElement('text', 'penalty', get_string('penaltyfactor', 'quiz'),
            array('size' => 3));
        $mform->setType('penalty', PARAM_NUMBER);
        $mform->addRule('penalty', null, 'required', null, 'client');
        $mform->setHelpButton('penalty', array('penalty', get_string('penalty', 'quiz'), 'quiz'));
        $mform->setDefault('penalty', 0.1);*/
        //$mform->addElement

        //$mform->addElement('button', 'buttoneditor', get_string('openeditor', 'qtype_stonesgame'),array('onclick'=>'hscheckImages(\''.(get_string('imagealert','qtype_stonesgame')).'\',\''.(get_string('chooseanimage','qtype_stonesgame')).'\',\''.$CFG->wwwroot.'\',this.form)'));

        $attributes = ['строка1', 'строка2'];
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'yesno', '', get_string('yes'), 1, $attributes);
        $radioarray[] = $mform->createElement('radio', 'yesno', '', get_string('no'), 0, $attributes);
        $mform->addGroup($radioarray, 'radioar', '', array(' '), false);
        $mform->addElement('hidden', 'stone');
    }

    function set_data($question) {
        if(isset($question->options)){
            $default_values['stone'] =  stripslashes($question->options->stone);
            $question = (object)((array)$question + $default_values);
        }
        parent::set_data($question);
    }

    function validation($data) {
        $errors = array();

        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }

    function qtype() {
        return 'stonesgame';
    }
}
?>