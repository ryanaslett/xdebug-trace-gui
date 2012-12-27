<?php
require 'trace.config.php';
require_once "vendor/autoload.php";

use \velovint\XdebugTrace\Summary;
use \velovint\XdebugTrace\Reader;
use \velovint\XdebugTrace\ListOutput;
use \velovint\XdebugTrace\Reader\SpecificCallReader;

?>
<html>
    <head>
        <style type="text/css">
            @import url('trace.css');
        </style>
        <script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
        <script type="text/javascript" src="/jquery.tablesorter.min.js"></script>
        <title>Xdebug Trace File Parser</title>
    </head>
    <body>
        <h1>Xdebug Trace File Parser</h1>
        <h2>Settings <?= $config['directory'] ?></h2>
        <form method="get">
            <label>File
                <select name="file">
                    <option value=""> -- Select -- </option>
                    <?php
                    $files = new DirectoryIterator($config['directory']);
                    foreach ($files as $file)
                    {

                        if (substr_count($file->getFilename(), '.xt') == 0 || in_array($config['directory'] . '/' . $file->getFilename(),
                                                                                       $ownTraces))
                        {
                            continue;
                        }

                        $date = explode('.', $file->getFilename());
                        $date = date('Y-m-d H:i:s', $file->getCTime());


                        echo '<option value="' . $file->getFilename() . '"'
                            . (isset($_GET['file']) && $file->getFileName() == $_GET['file'] ? ' selected="selected"' : '')
                            . '> ' . $date . ' - ' . $file->getFilename() . '- ' 
                            . number_format($file->getSize() / 1024, 0, ',', '.') 
                            . ' KB</option>' . PHP_EOL;
                    }
                    ?>
                </select>
            </label>

            <label>If the memory jumps <input type="text" name="memory" value="<?= @$_GET['memory'] ?: XDEBUG_TRACE_GUI_MEMORY_TRIGGER ?>" style="text-align:right" size="5"/> MB, provide an alert</label>
            <label>If the execution time jumps <input type="text" name="time" value="<?= @$_GET['time'] ?: XDEBUG_TRACE_GUI_TIME_TRIGGER ?>" style="text-align:right" size="5"/> seconds, provide an alert</label>
            <label>Maximum call stack depth to analyze <input type="text" name="max_depth" value="<?= @$_GET['max_depth'] ?: Reader::DEFAULT_DEPTH ?>"  style="text-align:right" size="5" /></label>
            <label>Sort calls by 
                <select name="sort_by" id="sort_by"><option value="sortByCall">naturally</option>
                    <option value="sortByStats">time</option>
                </select> (takes effect on expand)</label>

            <input type="submit" value="parse" />

        </form>

        <?php
        if (!isset($_GET['file']))
        {
            exit;
        }
        /**
         * retrieve the xdebug.trace_format ini set.
         */
        $XDEBUG_TRACE_GUI_CUSTOM_NAMESPACE_LEN = strlen(XDEBUG_TRACE_GUI_CUSTOM_NAMESPACE);

        $traceFile = $config['directory'] . '/' . $_GET ['file'];


        $memJump = 1000000;
        if (isset($_GET['memory']))
        {
            $memJump = (float) $_GET['memory'] * 1000000;
        }

        $timeJump = 1;
        if (isset($_GET['time']))
        {
            $timeJump = (float) $_GET['time'];
        }
        if (isset($_GET["max_depth"])) {
            $maxDepth = $_GET["max_depth"];
        }
        $nestedCalls = isset($_GET["start_with_call"]) 
            ? $_GET["start_with_call"] 
            : null;

        if (!isset($_GET ['file']) || empty($_GET ['file']))
        {
            echo '<p>No file selected</p>';
        }
        else if (!file_exists($traceFile))
        {
            echo '<p>Invalid file</p>';
        }
        else
        {
            echo '<h2>Call Trace</h2><div id="trace">';
            $reader = new Reader($traceFile, $maxDepth);
            if (!is_null($nestedCalls)) {
                $reader = new SpecificCallReader($reader, $nestedCalls);
            }
            $summary = new Summary();
            $output = new ListOutput($timeJump, $memJump);
            
            $reader->init();
            while ($data = $reader->next())
            {
                if ($data[Reader::POINT] == "1") { $summary->add($data); }
                $output->printLine($data);
                
            }
            echo "</div>";
        }
            ?>
        <h2>Summary of function calls</h2>
        <table id="summary">
            <thead>
            <tr>
               <th>Function</th>
               <th>times</th>
               <th>sum Time, ms</th>
               <th>sum Memory, MiB</th>
               <th>avg Time, ms</th>
               <th>avg Memory, MiB</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($data = $summary->next()) {
                printf("<tr><td>%s</td><td>%d</td><td>%.3f</td><td>%+.4f</td><td>%.3f</td><td>%+.4f</td></tr>",
                    $data[Summary::NAME], $data[Summary::TIMES], 
                    $data[Summary::TOTAL_TIME] * 1000, $data[Summary::TOTAL_MEMORY] / (1024 * 1024), 
                    $data[Summary::AVG_TIME] * 1000, $data[Summary::AVG_MEMORY] / (1024 * 1024));
            } ?>
            </tbody>
        </table>
             
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#trace>ul').click(function(event) {
                list = $(event.target).children("ul");
                var sortBy = $("#sort_by").val();
                if (list.attr("sortedBy") == undefined) {
                    list.attr("sortedBy", "sortByCall");
                }
                if (list.is(":hidden") && list.attr("sortedBy") != sortBy) {
                    alert(sortBy);
                    list.children("li").sort(window[sortBy]).appendTo(list);
                    list.attr("sortedBy", sortBy);
                }
                list.toggle();
            });
            $("#summary").tablesorter();
        })
        function sortByStats(a, b) {
            return parseFloat($(b).children(".stat").text())
                - parseFloat($(a).children(".stat").text());
        }
        function sortByCall(a, b) {
            return parseInt(a.id.substr(4))
                - parseInt(b.id.substr(4));
        }
    </script>
    </body>
</html>
