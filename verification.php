<?php

class Verification {
    const MAX_DEPTH = 10;

    public static function check_student_answers($studentAnswers, $answers){
        //check answers and return results (MB save in DB or not)

        //обработка

        $result = true;

        //check winner
        $winner = $studentAnswers["winner"];
        $answerwinner = $answers["winner"];

        $result = ($winner == $answerwinner);

        //check strategy
        $strategy = $studentAnswers["strategy"];
        $answergame = $answers["game"];//лучше хранить всю game, а не $answertree = $answers["tree"];
        $result = ($answergame->isStrategyCorrect($strategy, $answerwinner)) && $result;
        
        //check maxcount
        $maxcount = $studentAnswers["maxcount"];
        $result = ($answergame->isMaxCountCorrect($strategy, $maxcount)) && $result;

        return $result;
    }

    public static function build_answers($params){
        $answers = array();

        
        $operations = $params->operations;
        $initialStonesInHeaps = $params->initialStonesInHeaps;
        $moreOrEqual = $params->moreOrEqual;
        $endOfGameSum = $params->endOfGameSum;
        $firstPlayer = $params->firstPlayer;

        $maxDepth = Verification::MAX_DEPTH;


        $gameTree = new GameTree($operations, $initialStonesInHeaps, $moreOrEqual, $endOfGameSum, $firstPlayer, $maxDepth);
        $gameTree->start();
        $winner = $gameTree->getWinner();//int to string !!!
        $tree = $gameTree->getTree();


        array_push($answers, $winner);
        array_push($answers, $tree);

        return $answers;
        //TODO: send answers to method which write them to DB
    }
}