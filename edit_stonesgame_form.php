<?php // $Id$
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
class question_edit_stonesgame_form extends question_edit_form
{
    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    function definition_inner(&$mform)
    {
        //Общие параметры
        $mform->addElement('header', 'mainheader', get_string('mainheader', 'qtype_stonesgame'));

        $mform->addElement('text', 'pilescount', get_string('pilescount', 'qtype_stonesgame'), 'size="5"');
        $mform->setDefault('pilescount', '2');

        $players = array(get_string('jspet', 'qtype_stonesgame'), get_string('jsvan', 'qtype_stonesgame'));
        $mform->addElement('select', 'player', get_string('player', 'qtype_stonesgame'), $players);
        $mform->setDefault('player', 'Петя');


        $operationscount = 2;
        $mform->addElement('text', 'operationscount', get_string('operationscount', 'qtype_stonesgame'), 'size="5"');
        $mform->setType('operationscount', PARAM_INTEGER);
        $mform->setDefault('operationscount', $operationscount);

        for ($i = 1; $i <= $operationscount; $i++) {
            $mform->addElement('text', 'operation' . $i, get_string('operation', 'qtype_stonesgame') . $i);
            $mform->setDefault('operation' . $i, '+1');
        }

        $mform->addElement('text', 'wincase', get_string('wincase', 'qtype_stonesgame'));
        $mform->setDefault('wincase', '>= 73');


        //Параметры Задания 1
        $mform->addElement('header', 'task1header', get_string('task1header', 'qtype_stonesgame'));

        $positionscount = array();
        $positionscount[1] = 1;
        $mform->addElement('text', 'positionscount'[1], get_string('positionscount', 'qtype_stonesgame'), 'size="5"');
        $mform->setType('positionscount'[1], PARAM_INTEGER);
        $mform->setDefault('positionscount'[1], $positionscount);

        for ($i = 1; $i <= $positionscount[1]; $i++) {
            $mform->addElement('text', 'position1' [$i], get_string('position', 'qtype_stonesgame') . $i);
            $mform->setDefault('position1' [$i], '(7, 31)');
        }


        //Параметры Задания 2
        $mform->addElement('header', 'task2header', get_string('task2header', 'qtype_stonesgame'));

        $positionscount[2] = 2;
        $mform->addElement('text', 'positionscount'[2], get_string('positionscount', 'qtype_stonesgame'), 'size="5"');
        $mform->setType('positionscount'[2], PARAM_INTEGER);
        $mform->setDefault('positionscount'[2], $positionscount[2]);

        for ($i = 1; $i <= $positionscount[2]; $i++) {
            $mform->addElement('text', 'position2' [$i], get_string('position', 'qtype_stonesgame') . $i);
            $mform->setDefault('position2' [$i], '(7, 31)');
        }


        //Параметры Задания 3
        $mform->addElement('header', 'task3header', get_string('task3header', 'qtype_stonesgame'));

        $positionscount[3] = 1;
        $mform->addElement('text', 'positionscount3', get_string('positionscount', 'qtype_stonesgame'), 'size="5"');
        $mform->setType('positionscount3', PARAM_INTEGER);
        $mform->setDefault('positionscount3', $positionscount[3]);

        for ($i = 1; $i <= $positionscount[3]; $i++) {
            $mform->addElement('text', 'position3'[$i], get_string('position', 'qtype_stonesgame') . $i);
            $mform->setDefault('position3' [$i], '(7, 31)');
        }
    }

    function set_data($question)
    {
        //при создании нового вопроса ничего не происходит
        if (isset($question->options)) {
            //если такой вопрос уже был создан, в нем какие-то значения уже есть
            //открыли на редактирование существующего

            $default_values['hseditordata'] = stripslashes($question->options->hseditordata);
            $question = (object)((array)$question + $default_values);

        }
        parent::set_data($question);
    }

    function validation($data, $files)
    {
        //$errors = parent::validation($data, $files);

        $errors = array();

        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }

    function qtype()
    {
        return 'stonesgame';
    }
}

?>