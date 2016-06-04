<?php

class GameTree
{
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

class State
{
    private static $counter;//long
    private $index;//long
    private $step;//long //номер хода в игре (несколько состояний могут иметь один и тот же номер хода)
    private $stonesInHeaps;//ArrayList<Integer>
    private $win;//-1 0 1
    private $player;//int сделавший данный ход - тот, кто получил такое состояние, а не тот, кто только начнет ходить
    private $summ;//сумма всех камней в кучах
    private $nextStates;//ArrayList<State> //то, почему Ход/State - дерево
    private $operation;//Operation

    public function __construct()//ArrayList<Integer>
    {
        $this->index = State::$counter;
        State::$counter++;

        $this->stonesInHeaps = array();

    }

    public static function withStonesInHeaps($stonesInHeaps)//construct
    {
        $instance = new self();

        $instance->stonesInHeaps = $stonesInHeaps;
        $instance->updateSumm();
        $instance->win = -1;
        $instance->player = -1;

        return $instance;
    }

    public static function withStateAndOperation($state, $operation)//construct
    {
        $instance = new self();

        $instance->index = State::$counter;
        State::$counter++;
        $instance->stonesInHeaps = $state->stonesInHeaps;//клонировать никого не надо, а если stdclass, то уже будет по ссылке
        $instance->updateSumm();

        if ($state->player == 0) {        //противоположный игрок теперь
            $instance->player = 1;
        } else if ($state->player == 1) {   //противоположный игрок теперь
            $instance->player = 0;
        } else {//значение игрока еще не было установлено - корневое состояние
            $instance->player = -1;
        }

        $instance->step = $state->step + 1;
        $instance->win = -1;
        $instance->operation = $operation;

        return $instance;
    }

    public function updateSumm()
    {
        $result = 0;

        forEach ($this->stonesInHeaps as $stonesInOneHeapI => $stonesInOneHeap) {
            $result += $stonesInOneHeap;
        }

        $this->summ = $result;
    }

    public function setHeap($index, $newCountOfStones)
    {
        $this->stonesInHeaps[$index] = $newCountOfStones;
        $this->updateSumm();
    }



    public function printHeaps()
    {
        echo "(";
        $size = count($this->stonesInHeaps);

        $i = 0;
        forEach ($this->stonesInHeaps as $heap => $value) {
            if ($i != $size - 1) {
                echo $value . ", ";
            } else {
                echo $value;
            }
            $i++;
        }
        echo ")";
    }


}

/*Test State
$state = State::withStonesInHeaps(array("0" => "8", "1" => "2"));
$state->printHeaps();

$state1 = State::withStonesInHeaps(array("0" => "8", "1" => "2"));

$state = State::withStateAndOperation($state1, new Operation('+', 5));
$state->printHeaps();

$state->setHeap(1, 10);//нумерация с нуля, все нормально
$state->printHeaps();
*/



/* array - unset - delete, then - array_values()- переиндексация
 * extends ArrayObject
 * $student = Student::withRow( $row );
 * */

class Operation
{
    private $x;
    private $operator;

    public function __construct($operator, $x)
    {
        $this->operator = $operator;
        $this->x = $x;
    }

    public function apply($number)
    {
        switch ($this->operator) {
            case '+': {
                return $number + $this->x;
            }
            case 'x': {
                return $number * $this->x;
            }

        }

        return -1;
    }
}

/*
 * Test Operation
 * $operation = new Operation('x', 3);
$operation1 = new Operation('+', 1);

echo $operation->apply(4);
echo " ";
echo $operation1->apply(4); */


class Task
{

}




