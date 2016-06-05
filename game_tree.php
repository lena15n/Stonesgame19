<?php

class GameTree
{
    //final params
    const countOfPlayers = 2;
    private $operations;
    private $initialStonesInHeaps;
    private $moreOrEqual;
    private $endOfGameSum;
    private $firstPlayer;
    private $maxDepth;


    private $winner;
    private $startState;


    public function __construct($operations, $stonesInHeaps, $moreOrEqual, $endOfGameSum, $firstGamer, $maxDepth)
    {
        $this->operations = $operations;
        $this->initialStonesInHeaps = $stonesInHeaps;
        $this->moreOrEqual = $moreOrEqual;
        $this->endOfGameSum = $endOfGameSum;
        $this->firstPlayer = $firstGamer;
        $this->maxDepth = $maxDepth;
        $this->startState = State::withStonesInHeaps($this->initialStonesInHeaps);
        $this->startState->setStep(0);//вершина дерева игры, номер сделанного хода
        $this->winner = -1;
    }


    public function start()
    {
        $queue = array();
        array_push($queue, $this->startState);
        $this->buildGameTreeBranch($queue);

        $this->findWinner();

        $this->cutBranches($this->startState);

        //$this->toJSON($this->startState);
        echo "debug\r\n";
        echo State::$counter;
    }

    public function getMaxCount(){
        //TODO

        $maxCount = 0;
        return $maxCount;
    }

    public function getTree(){
        //TODO
        //MB перевод в json
        $tree = 0;
        return $tree;
    }

    public function getWinner()
    {
        if ($this->winner == -1) {
            $this->start();
        }

        return $this->winner;
    }

    private function buildGameTreeBranch($queue)
    {
        if (!empty($queue)) {//можно и без очереди, просто контроль по step
            $currentState = array_shift($queue);//извлекаем первый эл т из очереди
            /*if (currentState.getStep() >= MAX_DEPTH){//шоб было на всякий случай
                return;
            }*/

            if (!($currentState->getWin() == 1)) {
                $currentState->setNextStates($this->calculateAllPossibleStatesOnIter($currentState));

                $states = $currentState->getNextStates();

                forEach ($states as $key => $state) {
                    array_push($queue, $state);
                }
            }

            $this->buildGameTreeBranch($queue);
        }

    }

    private function calculateAllPossibleStatesOnIter($currentState)
    {
        $possibleStates = array();

        echo "Current state is: ";
        $currentState->printHeaps();
        echo "\r\n";

        $winStates = array();

        forEach ($this->operations as $key => $operation) {
            echo "Operation: ";
            $operation->printOperation();

            $currentStonesInHeaps = $currentState->getStonesInHeaps();
            $heapNum = 0;

            forEach ($currentStonesInHeaps as $innerKey => $stonesOfOneHeap) {
                $possibleState = State::withStateAndOperation($currentState, $operation);
                if ($possibleState->getPlayer() == -1) {
                    $possibleState->setPlayer($this->firstPlayer);
                }

                $possibleState->setHeap($heapNum, $operation->apply($stonesOfOneHeap));

                if ($this->moreOrEqual) {
                    if ($possibleState->getSum() >= $this->endOfGameSum) {
                        $possibleState->setWin();
                        array_push($possibleStates, $possibleState);
                        array_push($winStates, $possibleState);

                        echo "Winner state! -> ";
                        $possibleState->printHeaps();
                    } else {
                        array_push($possibleStates, $possibleState);

                        echo "Next possible state -> ";
                        $possibleState->printHeaps();
                    }
                }
                else {
                    if ($possibleState->getSum() > $this->endOfGameSum) {
                        $possibleState->setWin();
                        array_push($possibleStates, $possibleState);
                        array_push($winStates, $possibleState);

                        echo "Winner state! -> ";
                        $possibleState->printHeaps();
                    } else {
                        array_push($possibleStates, $possibleState);

                        echo "Next possible state -> ";
                        $possibleState->printHeaps();
                    }
                }

                $heapNum++;
            }

            echo "--------------\r\n";
        }

        if (count($winStates) > 0) {

            //$possibleStates = array_intersect($possibleStates, $winStates);

            //оставляет только выигрышные

            foreach ($possibleStates as $key => $possibleState) {
                if (!in_array($possibleState, $winStates)) {
                    unset($possibleStates[$key]);
                }
            }

            array_values($possibleStates);
        }


        echo "______________________________\r\n";

        return $possibleStates;
    }

    private function findWinner()
    {
        $this->setWinAndLooseStates($this->startState);//запуск покраски для всех веток

        if ($this->startState->getWin() == 0) {
            $this->winner = $this->firstPlayer;
        } else {
            $this->winner = $this->firstPlayer == 0 ? 1 : 0;//'противоположный первому игроку' игрок
        }

        $this->cutBranches($this->startState);
    }

    private function setWinAndLooseStates($currentState)
    {// запуск из уровня 1 (не корень)
        $count = 0;
        $states = $currentState->getNextStates();

        if ($currentState->getWin() == -1) {//если победа уже на первом ходе
            forEach ($states as $key => $state) {
                if ($state->getWin() == 1) {//вершина листовая
                    $currentState->setLoose();//при хотя бы одной победе противника помечаем состояние проигрышным
                } else if ($state->getWin() == 0) {//смотрим сколько проигрышных состояний противника
                    $count++;
                } else if ($state->getWin() == -1) {
                    $this->setWinAndLooseStates($state);

                    if ($state->getWin() == 1) {
                        $currentState->setLoose();
                    } else {
                        $count++;
                    }
                }
            }

            if ($count == count($states)) {
                $currentState->setWin();
            } else {
                $currentState->setLoose();
            }
        }
    }

    private function cutBranches($currentState)
    {
        //знаем выигрышные и проигрышные вершины,
        //устраняем случаи: из проигрышной ветки вдруг приходим к выигрышу
        //т е из проигрышной вершины (player = x) выходит проигрышная вершина (player = y)
        if ($currentState->getNextStates() != null) {
            $nextStates = $currentState->getNextStates();
            $excessStates = array();

            //собираем индексы детей, которых надо удалить из текущей
            forEach ($nextStates as $key => $nextState) {
                if ($currentState->getWin() == 0 && $nextState->getWin() == 0) {
                    array_push($excessStates, $nextState);
                } else {
                    $this->cutBranches($nextState);
                }

            }
            //удаляем
            //$nextStates = array_diff($nextStates, $excessStates);

            foreach ($nextStates as $key => $nextState) {
                if (in_array($nextState, $excessStates)) {
                    unset($nextStates[$key]);
                }
            }

            array_values($nextStates);
        }

    }

}

/* Test Game*/
$operation1 = new Operation('x', 2);
$operation2 = new Operation('+', 1);
$operations = array(0 => $operation1, 1 => $operation2);

$stones = array(0 => 7, 1 => 31);

$game = new GameTree($operations, $stones, true, 73, 0, 10);
$game->start();


class State
{
    public static $counter;//long
    private $index;//long
    private $step;//long //номер хода в игре (несколько состояний могут иметь один и тот же номер хода)
    private $stonesInHeaps;//ArrayList<Integer>
    private $win;//-1 0 1
    private $player;//int сделавший данный ход - тот, кто получил такое состояние, а не тот, кто только начнет ходить
    private $sum;//сумма всех камней в кучах
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
        $instance->updateSum();
        $instance->win = -1;
        $instance->player = -1;

        return $instance;
    }

    public static function withStateAndOperation($state, $operation)//construct
    {
        $instance = new self();

        $instance->stonesInHeaps = $state->stonesInHeaps;//клонировать никого не надо, а если stdclass, то уже будет по ссылке
        $instance->updateSum();

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

    public function updateSum()
    {
        $result = 0;

        forEach ($this->stonesInHeaps as $key => $stonesInOneHeap) {
            $result += $stonesInOneHeap;
        }

        $this->sum = $result;
    }


    public function printHeaps()
    {
        echo "(";
        $size = count($this->stonesInHeaps);

        $i = 0;
        forEach ($this->stonesInHeaps as $key => $heap) {
            if ($i != $size - 1) {
                echo $heap . ", ";
            } else {
                echo $heap;
            }
            $i++;
        }
        echo ")";
    }

    public function setHeap($index, $newCountOfStones)
    {
        $this->stonesInHeaps[$index] = $newCountOfStones;
        $this->updateSum();
    }


    public function getStep()
    {
        return $this->step;
    }

    public function setStep($step)
    {
        $this->step = $step;
    }

    public function getWin()
    {
        return $this->win;
    }

    public function setWin()
    {
        $this->win = 1;
    }

    public function setLoose()
    {
        $this->win = 0;
    }

    public function getPlayer()
    {
        return $this->player;
    }

    public function setPlayer($player)
    {
        $this->player = $player;
    }

    public function getSum()
    {
        return $this->sum;
    }

    public function setSum($sum)
    {
        $this->sum = $sum;
    }

    public function getNextStates()
    {
        return $this->nextStates;
    }

    public function setNextStates($nextStates)
    {
        $this->nextStates = $nextStates;
    }

    public function getStonesInHeaps()
    {
        return $this->stonesInHeaps;
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

    public function printOperation()
    {
        echo $this->operator . $this->x . "\r\n";
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




