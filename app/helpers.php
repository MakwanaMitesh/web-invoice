<?php

use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('formatCurrency')) {
  /**
   * Format the given amount based on the currency settings stored in the database.
   *
   * @param float|int $amount
   * @return string
   */
  function formatCurrency($amount)
  {
    // Cache the settings for 60 minutes to avoid redundant database queries
    // $settings = Cache::remember('currency_settings', 60, function () {
    //   return \App\Models\Setting::pluck('value', 'key')->toArray();
    // });

    $settings = Setting::pluck('value', 'key')->toArray();

    // Ensure we get a valid currency_id from settings and convert it to an integer
    $currencyId = (int) preg_replace('/[^0-9]/', '', $settings['currency_id'] ?? '1');
    $currencyId = $currencyId <= 0 ? 1 : $currencyId; // Default to 1 if currencyId is invalid

    // Convert string values to the correct types
    $decimalSeparator = $settings['decimal_separator'] ?? '.';
    $thousandSeparator = $settings['thousand_separator'] ?? ',';
    $numberOfDecimals = (int) preg_replace('/[^0-9]/', '', $settings['number_of_decimals'] ?? 2);
    $currencyPosition = $settings['currency_position'] ?? 'prefix';
    $symbolCase = $settings['symbol_case'] ?? 'uppercase';
    $roundingMethod = (int) preg_replace('/[^0-9]/', '', $settings['rounding_method'] ?? 3);
    $spaceBetweenSymbol = filter_var($settings['space_between_symbol'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
    // Fetch the currency symbol based on the currency ID
    $currency = Currency::find($currencyId);
    $symbol = $currency ? $currency->icon : '$'; // Default to $ if no symbol found

    // Apply rounding based on the selected rounding method
    $amount = applyRounding($amount, $roundingMethod, $numberOfDecimals);

    // Ensure correct number formatting with separators
    $formattedAmount = number_format($amount, $numberOfDecimals, $decimalSeparator, $thousandSeparator);

    // Handle the symbol case (uppercase, lowercase, or default)
    $symbol = formatSymbolCase($symbol, $symbolCase);

    // Build the final formatted currency string with symbol positioning
    return buildFormattedCurrency($formattedAmount, $symbol, $currencyPosition, $spaceBetweenSymbol);
  }

  /**
   * Apply rounding to the amount based on the selected rounding method.
   *
   * @param float $amount
   * @param int $roundingMethod
   * @param int $numberOfDecimals
   * @return float
   */
  function applyRounding($amount, $roundingMethod, $numberOfDecimals)
  {
    switch ($roundingMethod) {
      case 1: // Round Half Up
        return ceil($amount);
      case 2: // Round Half Down
        return floor($amount);
      case 3: // Round to Nearest
        return round($amount, $numberOfDecimals);
      default:
        return round($amount, $numberOfDecimals);
    }
  }

  /**
   * Format the symbol case (upper, lower, or default).
   *
   * @param string $symbol
   * @param string $symbolCase
   * @return string
   */
  function formatSymbolCase($symbol, $symbolCase)
  {
    if ($symbolCase === 'uppercase') {
      return strtoupper($symbol);
    } elseif ($symbolCase === 'lowercase') {
      return strtolower($symbol);
    }
    return $symbol; // Default case
  }

  /**
   * Build the final formatted currency string.
   *
   * @param string $formattedAmount
   * @param string $symbol
   * @param string $currencyPosition
   * @param bool $spaceBetweenSymbol
   * @return string
   */
  function buildFormattedCurrency($formattedAmount, $symbol, $currencyPosition, $spaceBetweenSymbol)
  {
    // Strip unwanted quotes if they're there
    $formattedAmount = str_replace('"', '', $formattedAmount); // Remove any double quotes from the formatted amount
    $symbol = str_replace('"', '', $symbol); // Remove any double quotes from the symbol

    // Apply the space between the symbol and the amount if necessary
    $space = $spaceBetweenSymbol ? ' ' : '';

    // Trim any quotes around the position value
    $currencyPosition = trim($currencyPosition, '"');

    // Build final output based on currency position
    if ($currencyPosition == 'prefix') {
      return $symbol . $space . $formattedAmount;
    } else {
      return $formattedAmount . $space . $symbol;
    }
  }
}
