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

class Game {
	public $outer;
	public $inner;
	public $coord;
	public $turn;
	public $over;

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
		if ($this->coord) {
			// TODO: validate that ox,oy matches
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
	}
}

session_start();
if (!isset($_SESSION['game'])) {
	$_SESSION['game'] = new Game();
}
$game =& $_SESSION['game'];

if (isset($_GET['_']) && ($_ = $_GET['_']) && preg_match('/^i(\d)(\d)(\d)(\d)$/',$_,$m)) {
	$game->set($m[1],$m[2],$m[3],$m[4]);
	header('Location: ?', TRUE, 302);
	exit;
}
if (isset($_GET['flush']) && $_GET['flush']) {
	unset($_SESSION['game']);
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params['path'], $params['domain'],
			$params['secure'], $params['httponly']
		);
	}
	session_destroy();
	header('Location: ?', TRUE, 302);
	exit;
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Tactic-Tac-Toe</title>
<style type="text/css">
table.outer {
  margin:0;
  border-collapse:collapse;
  border:2px solid #999;
  padding:5px;
}
table.inner {
  margin:0;
  border-collapse:collapse;
  border:2px solid #999;
  padding:3px;
}
.cell {
  margin:0;
  border:1px solid #ccc;
  padding:0;
  display:block;
  width:18px; height:18px;
  line-height:18px;
  text-align:center; text-decoration:none;
  background:#fff; color:#666;
  background-color:rgba(255,255,255,0.5);
}
table.outer.p1,
.outer-cell.p1 table.inner {
  border-color:#c00;
  background:#c00;
}
table.outer.p2,
.outer-cell.p2 table.inner {
  border-color:#000;
  background:#000;
}
table.outer.p3,
.outer-cell.p3 table.inner {
  border-color:#c60;
  background:#c60;
}

.inner-cell.p1 .cell {
  background:#c00;
  color:#fff;
}
.inner-cell.p2 .cell {
  background:#000;
  color:#fff;
}
.inner_cell.p3 .cell {
  background:#c60;
  color:#fff;
}

.outer-cell.target table.inner {
  border-color:#0cf;
  outline:2px solid #0cf;
}
</style>
</head>
<body>

<?php
echo "<table class=\"outer p{$game->over}\">\n";
foreach ($game->outer->rows as $r=>$out_row) {
	$in_row = $game->inner[$r];
	echo " <tr>\n";
	foreach ($out_row as $c=>$out_cell) {
		$in_board = $in_row[$c];
		if ($game->over) {
			$target = '';
			$link = false;
		} elseif (!$game->coord) {
			$target = '';
			$link = true;
		} elseif ($game->coord[0] == $r && $game->coord[1] == $c) {
			$target = 'target';
			$link = true;
		} else {
			$target = '';
			$link = false;
		}
		echo "  <td class=\"outer-cell p{$out_cell} $target\" id=\"o$r$c\">\n";
		echo "   <table class=\"inner\">\n";
		foreach ($in_board->rows as $q=>$row) {
			echo "    <tr>\n";
			foreach ($row as $b=>$cell) {
				echo "     <td class=\"inner-cell p{$cell}\" id=\"i$r$c$q$b\">\n";
				switch ($cell) {
				case 2:
					echo "      <span class=\"cell\">O</span>\n";
					break;
				case 1:
					echo "      <span class=\"cell\">X</span>\n";
					break;
				case 0:
				default:
					if ($link) {
						echo "      <a class=\"cell\" href=\"?_=i$r$c$q$b\">&bull;</a>\n";
					} else {
					echo "      <span class=\"cell\">&bull;</span>\n";
					}
					break;
				}
				echo "     </td>\n";
			}
			echo "    </tr>\n";
		}
		echo "   </table>\n";
		echo "  </td>\n";
	}
	echo " </tr>\n";
}
echo "</table>\n";
?>

<div id="whose-turn">
<?php
if ($game->over) {
	echo "<p>Game Over!</p>";
	if ($game->over === true) {
		echo "Everyone loses!";
	} else {
		switch ($game->over) {
		case 2:
			echo "O";
			break;
		case 1:
			echo "X";
			break;
		default:
			echo "???";
		}
		echo " wins!";
	}
} else {
	echo "It is ";
	switch ($game->turn) {
	case 2:
		echo "O";
		break;
	case 1:
		echo "X";
		break;
	default:
		echo "???";
	}
	echo "'s turn!";
}
?>
</div>

<a href="?flush=1"?>Restart</a>

</body>
</html>
