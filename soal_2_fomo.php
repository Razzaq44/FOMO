<?php

$grid = [
    "########",
    "#......#",
    "#.###..#",
    "#...#.##",
    "#X#....#",
    "########",
];

function findX(array $grid)
{
    foreach ($grid as $row => $line) {
        $col = strpos($line, 'X');

        if ($col !== false) {
            return [$row, $col];
        }
    }

    return null;
}

function moveNorth($row, $col)
{
    return [$row - 1, $col];
}

function moveEast($row, $col)
{
    return [$row, $col + 1];
}

function moveSouth($row, $col)
{
    return [$row + 1, $col];
}

function move(array $grid)
{
    [$startRow, $startCol] = findX($grid);

    $northPosition = [];

    $row = $startRow;
    $col = $startCol;

    while (true) {
        [$row, $col] = moveNorth($row, $col);

        if ($grid[$row][$col] === '#') {
            break;
        }

        $northPosition[] = [$row, $col];
    }

    $eastPositions = [];

    foreach ($northPosition as [$row, $col]) {
        $currentRow = $row;
        $currentCol = $col;

        while (true) {
            [$currentRow, $currentCol] = moveEast($currentRow, $currentCol);

            if ($grid[$currentRow][$currentCol] === '#') {
                break;
            }

            $eastPositions[] = [$currentRow, $currentCol];
        }
    }

    $finalPositions = [];

    foreach ($eastPositions as [$row, $col]) {
        $currentRow = $row;
        $currentCol = $col;

        $key = "$currentRow,$currentCol";
        $finalPositions[$key] = [$currentRow, $currentCol];

        while (true) {
            [$currentRow, $currentCol] = moveSouth($currentRow, $currentCol);

            if ($grid[$currentRow][$currentCol] === '#') {
                break;
            }

            $key = "$currentRow,$currentCol";

            $finalPositions[$key] = [$currentRow, $currentCol];
        }
    }

    return array_values($finalPositions);
}

$result = move($grid);

foreach ($result as [$row, $col]) {
    echo "($row, $col)\n";
}

function drawGrid(array $grid, array $result)
{
    $gridCopy = $grid;

    foreach ($result as [$row, $col]) {
        $gridCopy[$row][$col] = '$';
    }

    foreach ($gridCopy as $line) {
        echo $line . "\n";
    }
}


drawGrid($grid, $result);
?>