<?php
////////////////////////////////////////////////////////////////////////////////
//                                                                            //
//   This exception handler replaces the deprecated                           //
//   `trigger_error($message, E_USER_ERROR);`                                 //
//   that requires an older PHP version.                                      //
//                                                                            //
////////////////////////////////////////////////////////////////////////////////

if (!defined("PHORUM")) return;

/**
 * Thrown for internal Phorum errors: illegal API arguments, missing required
 * fields, inconsistent data and similar conditions from which the request
 * cannot continue safely.
 */
class PhorumError extends Exception
{
}

/**
 * Handles exceptions that reach the top of the call stack.
 *
 * Both PhorumError and any other uncaught exception are logged and the
 * request is ended. The message itself is only shown when display_errors
 * is enabled, so that internal details do not reach visitors.
 */
class PhorumErrorHandler
{
    /**
     * Install this handler as the exception handler for the request.
     */
    public function register()
    {
        set_exception_handler(array($this, "handle"));
    }

    /**
     * Log the exception and end the request.
     *
     * @param $exception - The uncaught exception.
     */
    public function handle($exception)
    {
        $internal = $exception instanceof PhorumError;
        $prefix   = $internal ? "Phorum error" : "Uncaught " . get_class($exception);
        $message  = $prefix . ": " . $exception->getMessage() .
                    " at " . $exception->getFile() . ":" . $exception->getLine();

        error_log($message);

        // Feed the event log when the event_logging module is enabled.
        // The module is not always loaded, so check before using it.
        if (function_exists("event_logging_writelog") &&
            defined("EVENTLOG_LVL_ALERT")) {
            event_logging_writelog(array(
                "message"  => $message,
                "loglevel" => EVENTLOG_LVL_ALERT,
                "details"  => $exception->getTraceAsString()
            ));
        }

        // Drop whatever was rendered before the error occurred, so that the
        // notice below is not appended to a half finished page.
        if (function_exists("phorum_ob_clean")) {
            phorum_ob_clean();
        }

        if (!headers_sent()) {
            header("HTTP/1.1 500 Internal Server Error");
            header("Content-Type: text/html; charset=utf-8");
        }

        print "An error occurred in the application.<br />\n";
        if (ini_get("display_errors")) {
            print htmlspecialchars($message) . "<br />\n";
        }

        exit(1);
    }
}
