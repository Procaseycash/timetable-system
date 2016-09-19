<?php
$connect = mysqli_connect('localhost', 'root', '');
if ($connect) {
    $db = mysqli_select_db($connect, 'time_table');
} else {
    echo "error";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title> Time Table</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"/>
    <style>
        .table {
            font-size: 10px;
            color: #000;

        }

        button {
            color: #000;
            background: 0;
            padding: 0;
            margin: 0;
            border: 0;
        }

        button:hover {
            color: darkmagenta;
        }

        button:active {
            color: deeppink;
        }
    </style>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
<div class="well">
    <button href="javascript:void(0)" onclick="window.print()">Print Time Table</button>
    &nbsp;
    <button href="javascript:void(0)" onclick="window.location.reload();">Generate Time Table</button>

    <?php
    /**
     * getting Subjects By Class and setting the size to fit in with the Numbers of Columns
     */
    $i = 0;
    $class = array();
    $get = mysqli_query($connect, "select distinct class_level from course where class_level like '%SSS%'");
    while ($val = mysqli_fetch_assoc($get)) {
        $class[$i] = $val['class_level'];
        $i++;
    }
    $class_len = count($class);
    $interval = mysqli_query($connect, "select * from time_interval");
    while ($fetch = mysqli_fetch_assoc($interval)) {
        $timeHead .= $fetch['start'] . '-' . $fetch['end'] . ',';
      //  $timeId .= $fetch['id'] . ',';
        $timeP .= $fetch['priority'] . ',';
    }
    $timeHead = explode(',', $timeHead);
    //$timeId = explode(',', $timeId);
   // $timeP = explode(',', $timeP);

    $lenTimeInterval = count($timeHead) - 1;
    $total_columns = ($lenTimeInterval) * 5;
    $subjects = array();
    $concat = '';
    //$concat.='MATHEMATICS,ENGLISH LANGUAGE,';
    for ($j = 0; $j < $class_len; $j++) {
        $get = mysqli_query($connect, "select * from course where class_level='{$class[$j]}'");
        while ($sub = mysqli_fetch_assoc($get)) {
            $concat .= $sub['course_description'] . ',';
        }
        $subjects[$j] = explode(',', $concat);
        array_pop($subjects[$j]);
        $lenSubClass[$j] = count($subjects[$j]);
        $concat = '';
    }
    ?>

    <?php
    /**
     * Handling and Populating Empty subjects' Columns to fit in the Number of Classes Available.
     *
     */
    $columnBased = array();
    $columnClass = array();
    for ($j = 0; $j < $total_columns; $j++) {
        $lenSub = count(explode(',', $columnBased[$j]));
        $lenSub = $lenSub - 1;
        $p = 0;
        $k = 0;
        while ($lenSub < $class_len) {
            shuffle($subjects[$p]);
            if (((strpos($columnBased[$j], substr($subjects[$p][$k], 0, 5))) == null) &&
                ((strpos($columnClass[$j], $class[$p])) == null) && $lenSub < $class_len
            ) {
                if ($k > $lenSubClass[$p]-1) {
                    if ($p > $class_len-1) {
                        break;
                    }
                    $p++;
                    $k = 0;
                }
                $columnBased[$j] .= ',' . $subjects[$p][$k];
                $columnClass[$j] .= ',' . $class[$p];

                $lenSub = count(explode(',', $columnBased[$j]));
                $lenSub = $lenSub - 1;
                $k++;
            } else {

                if ($k > $lenSubClass[$p]-1) {
                    if ($p > $class_len-1) {
                        break;
                    }
                        $p++;
                    $k = 0;
                }
               $k++;
            }
        }
    }
    ?>
    <?php
    /**
     * Plotting the Time Table in 5 by Length of Time Interval
     *
     */
    ?>
    <table style="text-align:center;" class="table table-responsive table-striped table-bordered">
        <tr>
            <td>Days/Time</td>
            <?php
            forEach ($timeHead as $key) {
                if ($key != '') {
                    echo "<td>$key</td>";
                }
            }
            ?>
            <?php
            $p = array();
            $count = 0;
            $k = 0;
            $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');
            for ($i = 0; $i < 5; $i++) {
                echo "<tr>";
                echo "<td>{$days[$i]}</td>";
                for ($j = 0; $j < $lenTimeInterval; $j++) {
                    if ($timeP[$j] == 'break') {
                        echo "<td style='border:0;'>Break Time</td>";
                        $p[$count] = $j;
                        $count++;
                    } else {
                        $index = ($i + $j + (($lenTimeInterval - 3) * $i));
                        if ($columnBased[$index] != null) {
                            echo "<td>";
                            $columnB = explode(',', $columnBased[$index]);
                            $columnC = explode(',', $columnClass[$index]);
                            $len = count($columnB);
                            $read = 0;
                            for ($m = 0; $m < $len; $m++) {
                                if ($columnB[$m] != null) {
                                    $read++;
                                    echo "<button type='button'>{$columnB[$m]} ({$columnC[$m]})</button><br />";
                                }
                            }
                            $remainder = $class_len - $read;
                            if ($remainder > 0) {
                                echo "<span class='badge'>{$remainder}</span>";
                            }
                            echo "</td>";
                        } else {
                            echo "<td>";
                            $index = ($i + $p[$k] + (($lenTimeInterval - 3) * $i));
                            $columnB = explode(',', $columnBased[$index]);
                            $columnC = explode(',', $columnClass[$index]);
                            $len = count($columnB);
                            $read = 0;
                            for ($m = 0; $m < $len; $m++) {
                                if ($columnB[$m] != null) {
                                    $read++;
                                    echo "<button type='button'>{$columnB[$m]} ({$columnC[$m]})</button><br />";
                                }
                            }
                            $remainder = $class_len - $read;
                            if ($remainder > 0) {
                                echo "<span class='badge'>{$remainder}</span>";
                            }
                            $k++;
                            echo "</td>";
                        }
                    }
                }
                echo "</tr>";
            }
            ?>
    </table>

    <?php
    /**
     * End Plotting the Time Table in 5 by Length of Time Interval
     *
     */
    ?>
    <?php
    /**
     * fetching Each Class Time Table Generated
     */
    $classType = strtoupper('SSS1A');
    $selectSub=array();
    $selectClass=array();
    for ($j = 0; $j < $total_columns; $j++) {
        $columnBased[$j];
        $columnB = explode(',', $columnBased[$j]);
        $columnC = explode(',', $columnClass[$j]);
        $len = count($columnB);
        for ($m = 0; $m < $len; $m++) {
            if ($columnC[$m] != null) {
                if (strtoupper($columnC[$m]) == $classType) {
                    $selectSub[$j]=$columnB[$m];
                    $selectClass[$j]=$columnC[$m];
                    break;
                }

            }
        }
    }

    ?>

    <table style="text-align:center;" class="table table-responsive table-striped table-bordered">
        <tr>
            <td>Days/Time</td>
            <?php
            forEach ($timeHead as $key) {
                if ($key != '') {
                    echo "<td>$key</td>";
                }
            }
            ?>
            <?php
            $p = array();
            $count = 0;
            $k = 0;
            $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');
            for ($i = 0; $i < 5; $i++) {
                echo "<tr>";
                echo "<td>{$days[$i]}</td>";
                for ($j = 0; $j < $lenTimeInterval; $j++) {
                    if ($timeP[$j] == 'break') {
                        echo "<td style='border:0;'>Break Time</td>";
                        $p[$count] = $j;
                        $count++;
                    } else {
                        $index = ($i + $j + (($lenTimeInterval - 3) * $i));
                        if ($selectSub[$index] != null) {
                            echo "<td>";
                            $columnB = explode(',', $selectSub[$index]);
                            $columnC = explode(',', $selectClass[$index]);
                            $len = count($columnB);
                            for ($m = 0; $m < $len; $m++) {
                                if ($columnB[$m] != null) {

                                    echo "<button type='button'>{$columnB[$m]} ({$columnC[$m]})</button><br />";
                                }
                            }
                            echo "</td>";
                        } else {
                            echo "<td>";
                            $index = ($i + $p[$k] + (($lenTimeInterval - 3) * $i));
                            $columnB = explode(',', $selectSub[$index]);
                            $columnC = explode(',', $selectClass[$index]);
                            $len = count($columnB);
                            for ($m = 0; $m < $len; $m++) {
                                if ($columnB[$m] != null) {
                                    echo "<button type='button'>{$columnB[$m]} ({$columnC[$m]})</button><br />";
                                }
                            }
                            $k++;
                            echo "</td>";
                        }
                    }
                }
                echo "</tr>";
            }
            ?>
    </table>


</div>
</body>
</html>
<?php mysqli_close($connect); ?>
<!--What to do next
1. Avoid Duplicate of subject in the same column.
2. Make Mathematics, Further Mathematics and English Priority if possible.
3. Filtering base by class.
4. Avoid Subject done in the same class on same column
-->

