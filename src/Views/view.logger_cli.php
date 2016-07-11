<?php
if (!function_exists('getColoredString'))
{
    function getColoredString($string, $foreground_color, $background_color) {

        // Determine the color system
        $foreground_colors = array();
        $background_colors = array();

        $foreground_colors['black'] = '0;30';
        $foreground_colors['dark_gray'] = '1;30';
        $foreground_colors['blue'] = '0;34';
        $foreground_colors['light_blue'] = '1;34';
        $foreground_colors['green'] = '0;32';
        $foreground_colors['light_green'] = '1;32';
        $foreground_colors['cyan'] = '0;36';
        $foreground_colors['light_cyan'] = '1;36';
        $foreground_colors['red'] = '0;31';
        $foreground_colors['light_red'] = '1;31';
        $foreground_colors['purple'] = '0;35';
        $foreground_colors['light_purple'] = '1;35';
        $foreground_colors['brown'] = '0;33';
        $foreground_colors['yellow'] = '1;33';
        $foreground_colors['light_gray'] = '0;37';
        $foreground_colors['white'] = '1;37';
         
        $background_colors['black'] = '40';
        $background_colors['red'] = '41';
        $background_colors['green'] = '42';
        $background_colors['yellow'] = '43';
        $background_colors['blue'] = '44';
        $background_colors['magenta'] = '45';
        $background_colors['cyan'] = '46';
        $background_colors['light_gray'] = '47';

        $colored_string = "";
         
        // Check if given foreground color found
        if (isset($foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($background_colors[$background_color])) {
            $colored_string .= "\033[" . $background_colors[$background_color] . "m";
        }
         
        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";
         
        return $colored_string;   
    }
}

$mask = "|%5s |%-90s | %10s |\n";
$id = 1;

printf($mask, 'Id', 'Title', 'Runtime');
printf($mask, $id, getColoredString('FuzeWorks debug log', 'black', 'light_gray'), '0 ms');
foreach ($this->assigned_variables['Logs'] as $log) {
    $id++;

    $string = '';
    if ($log['type'] == 'WARNING')
    {
        $string .= getColoredString('[WARNING]', 'black', 'yellow') . ' - ';
        $string .= getColoredString($log['message'], 'black', 'yellow');
    }
    elseif ($log['type'] == 'ERROR')
    {
        $string .= getColoredString('[ERROR]', 'black', 'red') . ' - ';
        $string .= getColoredString($log['message'], 'black', 'red');
    }
    elseif ($log['type'] == "LEVEL_STOP")
    {
        continue;
    }
    else
    {
        $string .= getColoredString($log['message'], 'green', 'black');
    }


    printf($mask, 
        $id, 
        $string,
        (!empty($log['runtime']) ? 
            round($log['runtime'] * 1000, 4) . 'ms' : 
            ''));
}