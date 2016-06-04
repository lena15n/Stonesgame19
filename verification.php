<?php

class Verification{
    public function __construct(){
        //
    }

    public function check_student_answers($question){
        $this->build_answers();

        $this->check();
    }

    function build_answers(){
        /*
        winner
        strategy (tree)
        maxcount
        tree
        */

        $tree = new GameTree();
        

    }

    function check(){
        //
        return false;
    }

}