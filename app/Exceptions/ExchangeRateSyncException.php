<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when the manual "Update Exchange Rates" action can't complete, so the
 * Filament action can show the admin a clear reason instead of a stack trace.
 */
class ExchangeRateSyncException extends RuntimeException {}
