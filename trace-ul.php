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

            <label>If the memory jumps <input type="text" name="memory" value="<?= XDEBUG_TRACE_GUI_MEMORY_TRIGGER ?>" style="text-align:right" size="5"/> MB, provide an alert</label>
            <label>If the execution time jumps <input type="text" name="time" value="<?= XDEBUG_TRACE_GUI_TIME_TRIGGER ?>" style="text-align:right" size="5"/> seconds, provide an alert</label>

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


        $memJump = 1;
        if (isset($_GET['memory']))
        {
            $memJump = (float) $_GET['memory'];
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
                    if ($level >=3 
                        && $reader->getMemoryUsage($data) > 100000) {
                        ob_end_flush();
                    } elseif ($level >= 3) {
                        ob_end_clean();
                    }
                }
                if (!$point) {
                    printf("<li>%s() %s:%d", $function, $filename, $line);
                } else {
                    $executionTime = $reader->getExecutionTime($data);
                    $memoryUsage = $reader->getMemoryUsage($data);
                    $warning = $executionTime > $timeJump
                        || $memoryUsage > $memJump * 1000000;
                    printf(' <span class="stat%s">%.3fms / %+.4f Mb</span></li>',
                        $warning ? " warning" : "",
                        $executionTime * 1000,
                        $memoryUsage / (1024 * 1024));
                }
                $previousLevel = $level;
                
            }
        }
            ?>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            //alert($('#trace>ul').length);
            $('#trace>ul').click(function(event) {
                //alert(event.target);
                $(event.target).children("ul").toggle();
            })
        })
    </script>
    </body>
</html>
