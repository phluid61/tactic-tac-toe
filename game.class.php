<?php

require_once('board.class.php');

class Game {
	public $outer;
	public $inner;
	public $coord;
	public $turn;
	public $over;
	public $message;

	public function __construct() {
		$this->outer = new Board();
		$this->inner = array(
			array(new Board(), new Board(), new Board()),
			array(new Board(), new Board(), new Board()),
			array(new Board(), new Board(), new Board()),
		);
		$this->coord = null;
		$this->turn = 1;
		$this->over = false;
	}

	public function set($ox, $oy, $ix, $iy) {
		$this->message = null;
		if ($this->coord) {
			// TODO: validate that ox,oy matches
			if ($this->coord[0] != $ox || $this->coord[1] != $oy) {
				$this->message = "Expected move in {$this->coord[0]},{$this->coord[1]}, not $ox,$oy";
				return false;
			}
		}
		$board = $this->inner[$ox][$oy];
		$old_winner = $board->winner;
		$board->set($ix, $iy, $this->turn);
		$new_winner = $board->winner;
		if ($new_winner != $old_winner) {
			$this->outer->set($ox,$oy,$new_winner);
		}

		if ($this->outer->winner) {
			// we have an weiner!
			$this->over = $this->outer->winner;
		} elseif ($this->outer->is_full) {
			// we have TWO LOSERS!!
			$this->over = true;
		}

		$this->turn = ($this->turn % 2)+1;
		if ($this->inner[$ix][$iy]->is_full) {
			$this->coord = null;
		} else {
			$this->coord = array($ix, $iy);
		}
		return true;
	}
}

