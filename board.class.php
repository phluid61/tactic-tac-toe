<?php

class Board {
	public $rows = array(
		array(0,0,0),
		array(0,0,0),
		array(0,0,0),
	);
	public $winner = 0;
	public $is_full = false;
	public function set($x, $y, $p) {
		if ($x < 0 || $x > 2 || $y < 0 || $y > 2) {
			throw new OutOfRangeException("out of range ($x,$y)");
		}
		if ($q = $this->rows[$x][$y]) {
			throw new RuntimeException("cell ($x,$y) already set [$q, $p]");
		}
		$this->rows[$x][$y] = $p;
		// check for weiner
		if (!$this->winner) {
			for ($i = 0; $i < 3; $i++) {
				if ($this->rows[$i][0] & $this->rows[$i][1] & $this->rows[$i][2]) {
					$this->winner = $this->rows[$i][0];
					break;
				} elseif ($this->rows[0][$i] & $this->rows[1][$i] & $this->rows[2][$i]) {
					$this->winner = $this->rows[0][$i];
					break;
				}
			}
			if ($this->rows[0][0] & $this->rows[1][1] & $this->rows[2][2]) {
				$this->winner = $this->rows[1][1];
			} elseif ($this->rows[0][2] & $this->rows[1][1] & $this->rows[2][0]) {
				$this->winner = $this->rows[1][1];
			}
		}
		// check if full
		if (!$this->is_full) {
			$full = true;
			foreach ($this->rows as $r) {
				foreach ($r as $c) {
					if (!$c) {
						$full = false;
						break 2;
					}
				}
			}
			$this->is_full = $full;
			if ($full && !$this->winner) {
				$this->winner = 3;
			}
		}
	}
}

