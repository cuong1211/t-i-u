<?php

$result = [
    [20, 0, 80, 'x'],
    [130, 'x', 'x', 'x'],
    ['x', 120, 'x', 50]
];

$loop = [];
$findLoop = static function ($x, $y, $list = []) use ($result, &$findLoop, &$loop) {
    $list[] = [$x, $y];
    $countLoop = count($loop);
    $countList = count($list);

    if ($countLoop > 0 && $countLoop < $countList) {
        unset($list);
        return;
    }

    if ($countList > 3) {
        [$xFirst, $yFirst] = reset($list);
        if ($x == $xFirst || $y == $yFirst) {
            $loop = $list;
        }
    }

    foreach($result as $row => $value) {
        if ($row != $x && $result[$row][$y] != 'x') {
            if (!in_array([$row, $y], $list)) {
                $findLoop($row, $y, $list);
            }
        }
    }

    foreach($result[$x] as $col => $value) {
        if ($col != $y && $result[$x][$col] != 'x') {
            if (!in_array([$x, $col], $list)) {
                $findLoop($x, $col, $list);
            }
        }
    }
};

$findLoop(2,0);

$show = [];
foreach ($loop as $e) {
    $show[] = sprintf('(%s)', implode(', ', $e));
}
echo implode(', ', $show)."\n";

exit;
$result = [];

$findLoop = static function ($x, $y, $list = []) use ($matrix, &$findLoop, &$result) {
    $list[] = [$x, $y];
    $countLoop = count($result);

    if ($countLoop > 0 && $countLoop < count($list)) {
        unset($list);
        return;
    }

    if (count($list) > 3) {
        [$xFirst, $yFirst] = reset($list);
        if ($x == $xFirst || $y == $yFirst) {
            $result = $list;
        }
    }

    foreach($matrix as $row => $value) {
        if ($row != $x && $matrix[$row][$y] != 'x') {
            if (!in_array([$row, $y], $list)) {
                $findLoop($row, $y, $list);
            }
        }
    }

    foreach($matrix[$x] as $col => $value) {
        if ($col != $y && $matrix[$x][$col] != 'x') {
            if (!in_array([$x, $col], $list)) {
                $findLoop($x, $col, $list);
            }
        }
    }
};

$findLoop(2, 0);

$show = [];
foreach ($result as $e) {
    $show[] = sprintf('(%s)', implode(', ', $e));
}
echo implode(', ', $show)."\n";

goto end;

uv:
$uv = [
    [3, 5, 7, 0],
    [1, 0, 0, 0],
    [0, 8, 0, 7]
];

$u = $v = [];

$u[0] = 0;

$uv = static function () use ($uv, &$u, &$v) {
    foreach ($uv as $row => $cols) {
        foreach ($cols as $col => $value) {
            if (isset($u[$row]) && $value) {
                $v[$col] = $value - $u[$row];
            }
            if (isset($v[$col]) && $value) {
                $u[$row] = $value - $v[$col];
            }
        }
    }
};
while (true) {
    $uv();
    if (count($u) == 3 && count($v) == 4) {
        break;
    }
}

var_dump($u, $v);
end:
