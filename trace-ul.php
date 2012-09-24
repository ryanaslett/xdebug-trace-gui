<?php
require 'trace.config.php';
require_once "src/XdebugTraceReader.php";

?>
<html>
    <head>
        <style type="text/css">
            @import url('trace.css');
        </style>
        <script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
        <title>Xdebug Trace File Parser</title>
    </head>
    <body>
        <h1>Xdebug Trace File Parser</h1>
        <h2>Settings <?= $config['directory'] ?></h2>
        <form method="get">
            <label>File
                <select name="file">
                    <option value="" selected="selected"> -- Select -- </option>
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


                        echo '<option value="' . $file->getFilename() . '"> ' . $date . ' - ' . $file->getFilename() . '- ' . number_format($file->getSize() / 1024,
                                                                                                                                            0,
                                                                                                                                            ',',
                                                                                                                                            '.') . ' KB</option>';
                    }
                    ?>
                </select>
            </label>

            <label>If the memory jumps <input type="text" name="memory" value="<?= XDEBUG_TRACE_GUI_MEMORY_TRIGGER ?>" style="text-align:right" size="5"/> MB, provide an alert and show nested calls</label>
            <label>If the execution time jumps <input type="text" name="time" value="<?= XDEBUG_TRACE_GUI_TIME_TRIGGER ?>" style="text-align:right" size="5"/> seconds, provide an alert and show nested calls</label>
            <label>Sort calls by 
                <select name="sort_by" id="sort_by"><option value="sortByCall">naturally</option>
                    <option value="sortByStats">time</option>
                </select> (takes effect on expand)</label>

            <input type="submit" value="parse" />

        </form>

        <br /><a href="#sumary">Resum</a>
        <?php
        if (!isset($_GET['file']))
        {
            exit;
        }
        ?>
        <h2>Output</h2>
        <?php
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

            echo '<div id="trace">';
            $reader = new XdebugTraceReader($traceFile);
            
            $previousLevel = 0;
            while ($data = $reader->next())
            {
                @list($level, $id, $point, $time, 
                    $memory, $function, $type, $file, 
                    $filename, $line, $numParms) = $data;
                if ($level > $previousLevel) {
                    if ($level >= 3) { ob_start(); }
                    echo "<ul>\n"; 
                }
                elseif ($level < $previousLevel) {
                    echo "</ul>\n";
                    $dropNestedCalls = $level >= 3;
                    $flushNestedCalls = $level >= 3 
                        && ($reader->getMemoryUsage($data) > $memJump 
                            || $reader->getExecutionTime($data) > $timeJump);
                    if ($flushNestedCalls) {
                        ob_end_flush();
                    } elseif ($dropNestedCalls) {
                        ob_end_clean();
                    }
                }
                if (!$point) {
                    printf('<li id="call%d">%s() %s:%d', 
                        $id, $function, $filename, $line);
                } else {
                    $executionTime = $reader->getExecutionTime($data);
                    $memoryUsage = $reader->getMemoryUsage($data);
                    $warning = $executionTime > $timeJump
                        || $memoryUsage > $memJump;
                    printf(' <span class="stat%s">%.3fms / %+.4f Mb</span></li>%s',
                        $warning ? " warning" : "",
                        $executionTime * 1000,
                        $memoryUsage / (1024 * 1024),
                        PHP_EOL);
                }
                $previousLevel = $level;
                
            }
        }
            ?>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#trace>ul').click(function(event) {
                list = $(event.target).children("ul");
                var sortBy = $('#sort_by').val();
                if (list.is(":hidden") && list.sortedBy != sortBy) {
                    list.children("li").sort(window[sortBy]).appendTo(list);
                    list.sortedBy = sortBy;
                }
                list.toggle();
            })
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
