<?php

class Verification {
    const MAX_DEPTH = 10;

    public static function check_student_answers($studentAnswers, $answers){
        //check answers and return results (MB save in DB or not)

        //обработка
        
        //check winner
       
        //check strategy
        $strategy = $studentAnswers["strategy"];
        
        $answertree = $answers["tree"];
        $answerwinner = $answers["winner"];
        
        $answertree->isStrategyCorrect($strategy, $answerwinner);
        
        //check maxcount
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