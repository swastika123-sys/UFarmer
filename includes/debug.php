<?php
/**
 * UFarmer Debug Utility
 * Provides terminal-like debugging for PHP development
 */

class DebugLogger {
    private static $logFile;
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) return;
        
        self::$logFile = __DIR__ . '/../logs/debug.log';
        
        // Ensure logs directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Initialize with empty log file if it doesn't exist
        if (!file_exists(self::$logFile)) {
            file_put_contents(self::$logFile, '');
        }
        
        self::$initialized = true;
        
        // Set up custom error handler
        set_error_handler([self::class, 'errorHandler']);
        set_exception_handler([self::class, 'exceptionHandler']);
        
        self::$initialized = true;
        self::log('DEBUG', 'Debug logger initialized');
    }
    
    public static function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;
        
        // Write to log file
        if (!empty(self::$logFile) && file_exists(dirname(self::$logFile))) {
            file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
        }
        
        // Also output to terminal if running in CLI or development mode
        if (php_sapi_name() === 'cli' || (defined('DEBUG') && DEBUG)) {
            echo $logEntry;
        }
    }
    
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    public static function debug($message, $context = []) {
        if (defined('DEBUG') && DEBUG) {
            self::log('DEBUG', $message, $context);
        }
    }
    
    public static function httpRequest($method, $uri, $statusCode) {
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        self::log('HTTP', "{$method} {$uri} - {$statusCode}", [
            'ip' => $clientIP,
            'user_agent' => $userAgent
        ]);
    }
    
    public static function errorHandler($severity, $message, $filename, $lineno) {
        $errorTypes = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];
        
        $errorType = $errorTypes[$severity] ?? 'UNKNOWN';
        $relativeFilename = str_replace(__DIR__ . '/../', '', $filename);
        
        self::log('PHP_ERROR', "[{$errorType}] {$message} in {$relativeFilename}:{$lineno}");
        
        // Return false to continue with normal error handling
        return false;
    }
    
    public static function exceptionHandler($exception) {
        $message = $exception->getMessage();
        $file = str_replace(__DIR__ . '/../', '', $exception->getFile());
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();
        
        self::log('EXCEPTION', "[" . get_class($exception) . "] {$message} in {$file}:{$line}");
        self::log('TRACE', $trace);
    }
    
    public static function tailLog($lines = 50) {
        if (!file_exists(self::$logFile)) {
            echo "Log file not found.\n";
            return;
        }
        
        $file = new SplFileObject(self::$logFile, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);
        
        while (!$file->eof()) {
            echo $file->current();
            $file->next();
        }
    }
    
    public static function clearLog() {
        if (file_exists(self::$logFile)) {
            file_put_contents(self::$logFile, '');
            self::log('INFO', 'Log file cleared');
        }
    }
}

// Auto-initialize if not in CLI mode
if (php_sapi_name() !== 'cli') {
    DebugLogger::init();
}
?>
