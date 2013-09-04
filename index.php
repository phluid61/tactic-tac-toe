<?php

require_once('game.class.php');

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

if ($game->message) {
	echo "<p>{$game->message}</p>";
}

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
