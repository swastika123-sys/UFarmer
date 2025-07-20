#!/usr/bin/env php
<?php
/**
 * UFarmer Debug Monitor
 * Terminal-based log monitoring for PHP debugging
 * Usage: php debug_monitor.php [command]
 * Commands: tail, clear, live
 */

require_once __DIR__ . '/includes/debug.php';

function showUsage() {
    echo "UFarmer Debug Monitor\n";
    echo "Usage: php debug_monitor.php [command]\n\n";
    echo "Commands:\n";
    echo "  tail [lines]  - Show last N lines of log (default: 50)\n";
    echo "  clear         - Clear the log file\n";
    echo "  live          - Live monitoring (follow mode)\n";
    echo "  help          - Show this help message\n\n";
    echo "Examples:\n";
    echo "  php debug_monitor.php tail 100\n";
    echo "  php debug_monitor.php live\n";
}

function liveTail($logFile) {
    echo "Starting live monitoring... Press Ctrl+C to stop\n";
    echo "Log file: {$logFile}\n";
    echo str_repeat("-", 50) . "\n";
    
    $lastSize = file_exists($logFile) ? filesize($logFile) : 0;
    
    while (true) {
        if (file_exists($logFile)) {
            $currentSize = filesize($logFile);
            
            if ($currentSize > $lastSize) {
                $handle = fopen($logFile, 'r');
                fseek($handle, $lastSize);
                
                while (($line = fgets($handle)) !== false) {
                    echo $line;
                }
                
                fclose($handle);
                $lastSize = $currentSize;
            }
        }
        
        usleep(100000); // 0.1 seconds
    }
}

// Main execution
if ($argc < 2) {
    showUsage();
    exit(1);
}

$command = $argv[1];

switch ($command) {
    case 'tail':
        $lines = isset($argv[2]) ? (int)$argv[2] : 50;
        echo "Showing last {$lines} lines of debug log:\n";
        echo str_repeat("-", 50) . "\n";
        DebugLogger::tailLog($lines);
        break;
        
    case 'clear':
        DebugLogger::clearLog();
        echo "Debug log cleared.\n";
        break;
        
    case 'live':
        $logFile = __DIR__ . '/logs/debug.log';
        liveTail($logFile);
        break;
        
    case 'help':
    default:
        showUsage();
        break;
}
?>
