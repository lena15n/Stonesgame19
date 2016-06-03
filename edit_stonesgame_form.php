<?php  // $Id$
/**
 * Defines the editing form for the stonesgame question type.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questionbank
 * @subpackage questiontypes
 */

/**
 * stonesgame editing form definition.
 */
class question_edit_stonesgame_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    function definition_inner(&$mform) {
        //Общие параметры
        $mform->addElement('header', 'mainheader', get_string('mainheader', 'qtype_stonesgame'));

        $mform->addElement('text', 'pilescount', get_string('pilescount', 'qtype_stonesgame'), 'size="5"');
        $mform->setDefault('pilescount', '2');

        $players = array(get_string('jspet', 'qtype_stonesgame'), get_string('jsvan', 'qtype_stonesgame'));
        $mform->addElement('select', 'player', get_string('player', 'qtype_stonesgame'), $players);
        $mform->setDefault('player', '2');
        
        
        $operationscount = 2;
        $mform->addElement('text', 'operationscount', get_string('operationscount', 'qtype_stonesgame'), 'size="5"');
        $mform->setType('operationscount', PARAM_INTEGER);
        $mform->setDefault('operationscount', $operationscount);

        for($i = 1; $i <= $operationscount; $i++) {
            $mform->addElement('text', 'operation'.$i, get_string('operation', 'qtype_stonesgame').$i);
            $mform->setDefault('operation'.$i, '+1');
        }

        $mform->addElement('text', 'wincase', get_string('wincase', 'qtype_stonesgame'));
        $mform->setDefault('wincase', '>= 73');


        //Параметры Задания 1
        $mform->addElement('header', 'task1header', get_string('task1header', 'qtype_stonesgame'));

        $positionscount1 = 1;
        $mform->addElement('text', 'positionscount1', get_string('positionscount', 'qtype_stonesgame'), 'size="5"');
        $mform->setType('positionscount1', PARAM_INTEGER);
        $mform->setDefault('positionscount1', $positionscount1);

        for($i = 1; $i <= $positionscount1; $i++) {
            $mform->addElement('text', 'position1'.$i, get_string('position', 'qtype_stonesgame').$i);
            $mform->setDefault('position1'.$i, '(7, 31)');
        }


        //Параметры Задания 2
        $mform->addElement('header', 'task2header', get_string('task2header', 'qtype_stonesgame'));

        $positionscount2 = 2;
        $mform->addElement('text', 'positionscount2', get_string('positionscount', 'qtype_stonesgame'), 'size="5"');
        $mform->setType('positionscount2', PARAM_INTEGER);
        $mform->setDefault('positionscount2', $positionscount2);

        for($i = 1; $i <= $positionscount2; $i++) {
            $mform->addElement('text', 'position'.$i, get_string('position', 'qtype_stonesgame').$i);
            $mform->setDefault('position'.$i, '(7, 31)');
        }


        //Параметры Задания 3
        $mform->addElement('header', 'task3header', get_string('task3header', 'qtype_stonesgame'));

        $positionscount3 = 1;
        $mform->addElement('text', 'positionscount3', get_string('positionscount', 'qtype_stonesgame'), 'size="5"');
        $mform->setType('positionscount3', PARAM_INTEGER);
        $mform->setDefault('positionscount3', $positionscount3);

        for($i = 1; $i <= $positionscount3; $i++) {
            $mform->addElement('text', 'position3'.$i, get_string('position', 'qtype_stonesgame').$i);
            $mform->setDefault('position3'.$i, '(7, 31)');
        }
    }

    function set_data($question) {
        if (isset($question->options)){
            $answers = $question->options->answers;
            if (count($answers)) {
                $key = 0;
                foreach ($answers as $answer){
                    $default_values['answer['.$key.']'] = $answer->answer;
                    $default_values['fraction['.$key.']'] = $answer->fraction;
                    $default_values['tolerance['.$key.']'] = $answer->tolerance;
                    $default_values['feedback['.$key.']'] = $answer->feedback;
                    $key++;
                }
            }
            $units  = array_values($question->options->units);
            if (!empty($units)) {
                foreach ($units as $key => $unit){
                    $default_values['unit['.$key.']'] = $unit->unit;
                    $default_values['multiplier['.$key.']'] = $unit->multiplier;
                }
            }
            $question = (object)((array)$question + $default_values);
        }
        parent::set_data($question);
    }
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Check the answers.
        $answercount = 0;
        $maxgrade = false;
        $answers = $data['answer'];
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer);
            if ($trimmedanswer != '') {
                $answercount++;
                if (!(is_numeric($trimmedanswer) || $trimmedanswer == '*')) {
                    $errors["answer[$key]"] = get_string('answermustbenumberorstar', 'qtype_stonesgame');
                }
                if ($data['fraction'][$key] == 1) {
                    $maxgrade = true;
                }
            } else if ($data['fraction'][$key] != 0 || !html_is_blank($data['feedback'][$key])) {
                $errors["answer[$key]"] = get_string('answermustbenumberorstar', 'qtype_stonesgame');
                $answercount++;
            }
        }
        if ($answercount == 0) {
            $errors['answer[0]'] = get_string('notenoughanswers', 'qtype_stonesgame');
        }
        if ($maxgrade == false) {
            $errors['fraction[0]'] = get_string('fractionsnomax', 'question');
        }

        // Check units.
        $alreadyseenunits = array();
        if (isset($data['unit'])) {
            foreach ($data['unit'] as $key => $unit) {
                $trimmedunit = trim($unit);
                if ($trimmedunit!='' && in_array($trimmedunit, $alreadyseenunits)) {
                    $errors["unit[$key]"] = get_string('errorrepeatedunit', 'qtype_stonesgame');
                    if (trim($data['multiplier'][$key]) == '') {
                        $errors["multiplier[$key]"] = get_string('errornomultiplier', 'qtype_stonesgame');
                    }
                } else {
                    $alreadyseenunits[] = $trimmedunit;
                }
            }
        }

        return $errors;
    }
    function qtype() {
        return 'stonesgame';
    }
}
?>