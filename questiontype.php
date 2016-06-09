<?php  // $Id$
/**
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questionbank
 * @subpackage questiontypes
 *//** */

require_once("$CFG->dirroot/question/type/shortanswer/questiontype.php");

/**
 * stonesgame QUESTION TYPE CLASS
 *
 * This class contains some special features in order to make the
 * question type embeddable within a multianswer (cloze) question
 *
 * This question type behaves like shortanswer in most cases.
 * Therefore, it extends the shortanswer question type...
 * @package questionbank
 * @subpackage questiontypes
 */
class question_stonesgame_qtype extends default_questiontype {

    function name() {
        return 'stonesgame';
    }

    function get_question_options(&$question) {
        if (!$question->options = get_record('qtype_stonesgame', 'question', $question->id)) {
            notify('Error: Missing question options for stonesgame question'.$question->id.'!');
            return false;
        }

        if (!$question->options->answers = get_records('qtype_stonesgame_answer', 'question', $question->id)) {
            notify('Error: Missing question answers for stonesgame question'.$question->id.'!');
            return false;
        }

        return true;

    }


    /**
     * Save the units and the answers associated with this question.
     */
    function save_question_options($question) {
        // Get old versions of the objects
        if (!$oldoptions = get_records('question_stonesgame', 'question', $question->id, 'answer ASC')) {
            $oldoptions = array();
        }

        $answers = json_decode(stripslashes($question->answers));

        /*проверяем ответы на корректность, если что - удаляем
         *
         * foreach($answers as $key=>$a){
            if(!$a || !$a->draw || !$a->shape || !$a->text){
                unset($answers[$key]);
            }
        }*/

        if(!$answers){
            $result->notice = "Провал";//get_string("failedloadinganswers", "qtype_ubhotspots");
            return $result;
        }

        if (!$oldanswers = get_records("question_stonesgame_answer", "question",$question->id, "id ASC")) {
            $oldanswers = array();
        }

        // TODO - Javascript Interface for fractions in the editor
        $fraction = round(1 / count($answers), 2);

        foreach($answers as $a){

            if ($answer = array_shift($oldanswers)) {  // Existing answer, so reuse it

                $answer->answer     = addslashes(json_encode($a));
                $answer->fraction   = $fraction;
                $answer->feedback = '';
                if (!update_record("question_answers", $answer)) {
                    $result->error = "Could not update quiz answer! (id=$answer->id)";
                    return $result;
                }
            } else {

                unset($answer);
                $answer->answer   = addslashes(json_encode($a));
                $answer->question = $question->id;
                $answer->fraction = $fraction;
                $answer->feedback = '';
                if (!$answer->id = insert_record("question_answers", $answer)) {
                    $result->error = "Could not insert quiz answer! ";
                    return $result;
                }
            }

        }


        // delete old answer records
        if (!empty($oldanswers)) {
            foreach($oldanswers as $oa) {
                delete_records('question_answers', 'id', $oa->id);
            }
        }

        $update = true;
        $options = get_record("qtype_ubhotspots", "question", $question->id);
        if (!$options) {
            $update = false;
            $options = new stdClass;
            $options->question = $question->id;
        }

        $options->hseditordata = addslashes($question->hseditordata);

        if ($update) {
            if (!update_record("qtype_ubhotspots", $options)) {
                $result->error = "Could not update quiz ubhotspots options! (id=$options->id)";
                return $result;
            }
        } else {
            if (!insert_record("qtype_ubhotspots", $options)) {
                $result->error = "Could not insert quiz ubhotspots options!";
                return $result;
            }
        }

        return true;














        if (!$oldanswers = get_records('question_answers', 'question', $question->id, 'id ASC')) {
            $oldanswers = array();
        }

        if (!$oldoptions = get_records('question_stonesgame', 'question', $question->id, 'answer ASC')) {
            $oldoptions = array();
        }

        // Save the units.
        $result = $this->save_stonesgame_units($question);
        if (isset($result->error)) {
            return $result;
        } else {
            $units = &$result->units;
        }

        // Insert all the new answers
        foreach ($question->answer as $key => $dataanswer) {
            // Check for, and ingore, completely blank answer from the form.
            if (trim($dataanswer) == '' && $question->fraction[$key] == 0 &&
                    html_is_blank($question->feedback[$key])) {
                continue;
            }

            $answer = new stdClass;
            $answer->question = $question->id;
            if (trim($dataanswer) === '*') {
                $answer->answer = '*';
            } else {
                $answer->answer = $this->apply_unit($dataanswer, $units);
                if ($answer->answer === false) {
                    $result->notice = get_string('invalidnumericanswer', 'quiz');
                }
            }
            $answer->fraction = $question->fraction[$key];
            $answer->feedback = trim($question->feedback[$key]);

            if ($oldanswer = array_shift($oldanswers)) {  // Existing answer, so reuse it
                $answer->id = $oldanswer->id;
                if (! update_record("question_answers", $answer)) {
                    $result->error = "Could not update quiz answer! (id=$answer->id)";
                    return $result;
                }
            } else { // This is a completely new answer
                if (! $answer->id = insert_record("question_answers", $answer)) {
                    $result->error = "Could not insert quiz answer!";
                    return $result;
                }
            }

            // Set up the options object
            if (!$options = array_shift($oldoptions)) {
                $options = new stdClass;
            }
            $options->question  = $question->id;
            $options->answer    = $answer->id;
            if (trim($question->tolerance[$key]) == '') {
                $options->tolerance = '';
            } else {
                $options->tolerance = $this->apply_unit($question->tolerance[$key], $units);
                if ($options->tolerance === false) {
                    $result->notice = get_string('invalidnumerictolerance', 'quiz');
                }
            }

            // Save options
            if (isset($options->id)) { // reusing existing record
                if (! update_record('question_stonesgame', $options)) {
                    $result->error = "Could not update quiz stonesgame options! (id=$options->id)";
                    return $result;
                }
            } else { // new options
                if (! insert_record('question_stonesgame', $options)) {
                    $result->error = "Could not insert quiz stonesgame options!";
                    return $result;
                }
            }
        }
        // delete old answer records
        if (!empty($oldanswers)) {
            foreach($oldanswers as $oa) {
                delete_records('question_answers', 'id', $oa->id);
            }
        }

        // delete old answer records
        if (!empty($oldoptions)) {
            foreach($oldoptions as $oo) {
                delete_records('question_stonesgame', 'id', $oo->id);
            }
        }

        // Report any problems.
        if (!empty($result->notice)) {
            return $result;
        }

        return true;
    }

    /**
     * Deletes question from the question-type specific tables
     *
     * @return boolean Success/Failure
     * @param object $question  The question being deleted
     */
    function delete_question($questionid) {
        delete_records("question_stonesgame1", "question", $questionid);
        delete_records("question_stonesgame_student", "question", $questionid);
        delete_records("question_stonesgame_answer", "question", $questionid);
        delete_records("question_stonesgame_position", "question", $questionid);
        delete_records("question_stonesgame_result", "question", $questionid);
        return true;
    }

    function compare_responses(&$question, $state, $teststate) {
        if (isset($state->responses['']) && isset($teststate->responses[''])) {
            return $state->responses[''] == $teststate->responses[''];
        }
        return false;
    }



    function get_correct_responses(&$question, &$state) {
        $correct = parent::get_correct_responses($question, $state);
        $unit = $this->get_default_stonesgame_unit($question);
        if (isset($correct['']) && $correct[''] != '*' && $unit) {
            $correct[''] .= ' '.$unit->unit;
        }
        return $correct;
    }

    // ULPGC ecastro
    function get_all_responses(&$question, &$state) {
        $result = new stdClass;
        $answers = array();
        $unit = $this->get_default_stonesgame_unit($question);
        if (is_array($question->options->answers)) {
            foreach ($question->options->answers as $aid=>$answer) {
                $r = new stdClass;
                $r->answer = $answer->answer;
                $r->credit = $answer->fraction;
                $this->get_tolerance_interval($answer);
                if ($r->answer != '*' && $unit) {
                    $r->answer .= ' ' . $unit->unit;
                }
                if ($answer->max != $answer->min) {
                    $max = "$answer->max"; //format_float($answer->max, 2);
                    $min = "$answer->min"; //format_float($answer->max, 2);
                    $r->answer .= ' ('.$min.'..'.$max.')';
                }
                $answers[$aid] = $r;
            }
        }
        $result->id = $question->id;
        $result->responses = $answers;
        return $result;
    }

    function get_tolerance_interval(&$answer) {
        // No tolerance
        if (empty($answer->tolerance)) {
            $answer->tolerance = 0;
        }

        // Calculate the interval of correct responses (min/max)
        if (!isset($answer->tolerancetype)) {
            $answer->tolerancetype = 2; // nominal
        }

        // We need to add a tiny fraction depending on the set precision to make the
        // comparison work correctly. Otherwise seemingly equal values can yield
        // false. (fixes bug #3225)
        $tolerance = (float)$answer->tolerance + ("1.0e-".ini_get('precision'));
        switch ($answer->tolerancetype) {
            case '1': case 'relative':
                /// Recalculate the tolerance and fall through
                /// to the nominal case:
                $tolerance = $answer->answer * $tolerance;
                // Do not fall through to the nominal case because the tiny fraction is a factor of the answer
                 $tolerance = abs($tolerance); // important - otherwise min and max are swapped
                $max = $answer->answer + $tolerance;
                $min = $answer->answer - $tolerance;
                break;
            case '2': case 'nominal':
                $tolerance = abs($tolerance); // important - otherwise min and max are swapped
                // $answer->tolerance 0 or something else
                if ((float)$answer->tolerance == 0.0  &&  abs((float)$answer->answer) <= $tolerance ){
                    $tolerance = (float) ("1.0e-".ini_get('precision')) * abs((float)$answer->answer) ; //tiny fraction
                } else if ((float)$answer->tolerance != 0.0 && abs((float)$answer->tolerance) < abs((float)$answer->answer) &&  abs((float)$answer->answer) <= $tolerance){
                    $tolerance = (1+("1.0e-".ini_get('precision')) )* abs((float) $answer->tolerance) ;//tiny fraction
               }

                $max = $answer->answer + $tolerance;
                $min = $answer->answer - $tolerance;
                break;
            case '3': case 'geometric':
                $quotient = 1 + abs($tolerance);
                $max = $answer->answer * $quotient;
                $min = $answer->answer / $quotient;
                break;
            default:
                error("Unknown tolerance type $answer->tolerancetype");
        }

        $answer->min = $min;
        $answer->max = $max;
        return true;
    }




    function print_question_formulation_and_controls(&$question, &$state, $cmoptions, $options) {
        global $CFG;

        $readonly = empty($options->readonly) ? '' : 'disabled="disabled"';

        // Print formulation
        $questiontext = $this->format_text($question->questiontext,$question->questiontextformat, $cmoptions);


        $isfinished = question_state_is_graded($state->last_graded) || $state->event == QUESTION_EVENTCLOSE;
        $feedback = '';
        if ($isfinished && $options->generalfeedback){
            $feedback = $this->format_text($question->generalfeedback, $question->questiontextformat, $cmoptions);
        }

        $imgfeedback = array();

        if(($options->feedback || $options->correct_responses) && !empty($state->responses)){

        }

        include("$CFG->dirroot/question/type/stonesgame/question.html");

    }
}

// INITIATION - Without this line the question type is not in use.
question_register_questiontype(new question_stonesgame_qtype());
?>
