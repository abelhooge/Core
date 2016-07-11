<?php

$string = '<h3>FuzeWorks debug log</h3>';
$layer = 0;
foreach ($this->assigned_variables['Logs'] as $log) {
    if ($log['type'] == 'LEVEL_START') {
        ++$layer;
        $color = 255 - ($layer * 25);
        $string .= '<div style="background: rgb(188 , 232 ,' . $color . ');border: 1px black solid;margin: 5px 0;padding: 5px 20px;">';
        $string .= '<div style="font-weight: bold; font-size: 11pt;">' . $log['message'] . '<span style="float: right">' . (!empty($log['runtime']) ? '(' . round($log['runtime'] * 1000, 4) . 'ms)' : '') . '</span></div>';
    } elseif ($log['type'] == 'LEVEL_STOP') {
        --$layer;
        $string .= '</div>';
    } elseif ($log['type'] == 'ERROR') {
        $string .= '<div style="' . ($layer == 0 ? 'padding-left: 21px;' : '') . 'font-size: 11pt; background-color:#f56954;">[' . $log['type'] . ']' . (!empty($log['context']) && is_string($log['context']) ? '<u>[' . $log['context'] . ']</u>' : '') . ' ' . $log['message'] . '
        	<span style="float: right">' . (!empty($log['logFile']) ? $log['logFile'] : '') . ' : ' . (!empty($log['logLine']) ? $log['logLine'] : '') . '(' . round($log['runtime'] * 1000, 4) . ' ms)</span></div>';
    } elseif ($log['type'] == 'WARNING') {
        $string .= '<div style="' . ($layer == 0 ? 'padding-left: 21px;' : '') . 'font-size: 11pt; background-color:#f39c12;">[' . $log['type'] . ']' . (!empty($log['context']) && is_string($log['context']) ? '<u>[' . $log['context'] . ']</u>' : '') . ' ' . $log['message'] . '
        	<span style="float: right">' . (!empty($log['logFile']) ? $log['logFile'] : '') . ' : ' . (!empty($log['logLine']) ? $log['logLine'] : '') . '(' . round($log['runtime'] * 1000, 4) . ' ms)</span></div>';
    } elseif ($log['type'] == 'INFO') {
        $string .= '<div style="' . ($layer == 0 ? 'padding-left: 21px;' : '') . 'font-size: 11pt;">' . (!empty($log['context']) ? '<u>[' . $log['context'] . ']</u>' : '') . ' ' . $log['message'] . '<span style="float: right">(' . round($log['runtime'] * 1000, 4) . ' ms)</span></div>';
    } elseif ($log['type'] == 'DEBUG') {
        $string .= '<div style="' . ($layer == 0 ? 'padding-left: 21px;' : '') . 'font-size: 11pt; background-color:#CCCCCC;">[' . $log['type'] . ']' . (!empty($log['context']) ? '<u>[' . $log['context'] . ']</u>' : '') . ' ' . $log['message'] . '<span style="float: right">(' . round($log['runtime'] * 1000, 4) . ' ms)</span></div>';
    }
}

echo $string;