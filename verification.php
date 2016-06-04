<?php

class Verification {
    /*
public final int COUNT_OF_PLAYERS;
public final ArrayList<Operation> OPERATIONS;
public final ArrayList<Integer> INITIAL_STONES_IN_HEAPS;
public final int END_OF_GAME_SUM;
public final int FIRST_PLAYER;
public final int MAX_DEPTH;

private final int COUNT_OF_BRANCHES;

private int winner;
private State startState;*/
    //final
    private $countOfPlayers;
    private $operations;
    private $initialStonesInHeaps;
    private $endOfGameSum;
    private $firstPlayer;
    private $maxDepth;


    private $winner;
    private $startState;





}

class State{
    
}

class Operation{
    private $x;
    private $operator;

    public function __construct($operator, $x){
        $this->operator = $operator;
        $this->x = $x;
    }

    public function apply($number){
        switch($this->operator){
            case '+':{
                return $number + $this->x;
            }
            case 'x':{
                return $number * $this->x;
            }

        }

        return -1;
    }
}

/*$operation = new Operation('x', 3);
$operation1 = new Operation('+', 1);

echo $operation->apply(4);
echo " ";
echo $operation1->apply(4); */


class Task{

}

