<?php

namespace App\Filament\Pages\Settings;

use App\Models\Currency;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class Settings extends BaseSettings
{
  public function schema(): array
  {
    return [
      Tabs::make('Settings')
        ->schema([
          Tabs\Tab::make('General')->schema([
            TextInput::make('app_name')
              ->label(__('App Name'))
              ->required(),
          ]),

          Tabs\Tab::make('Currency')->schema([
            Placeholder::make('currency_preview')
              ->label(__('Currency Preview'))
              ->content(fn(Get $get) => $this->generateCurrencyPreview($get))
              ->reactive(),

            Select::make('currency_id')
              ->label(__('Currency'))
              ->placeholder(__('Select a Currency'))
              ->options(
                Currency::all()
                  ->pluck('name', 'id')
                  ->mapWithKeys(function ($name, $id) {
                    // Fetch the currency symbol/icon
                    $currency = Currency::find($id);
                    $symbol = $currency ? $currency->icon : '';

                    // Return the icon and name for each option
                    return [$id => $symbol . ' ' . $name];
                  })
              )
              ->searchable()
              ->required()
              ->reactive(),

            Select::make('decimal_separator')
              ->label(__('Decimal Separator'))
              ->options([
                '.' => __('Dot (.)'),
                ',' => __('Comma (,)'),
              ])
              ->default('.')
              ->required()
              ->searchable()
              ->reactive(),

            Select::make('thousand_separator')
              ->label(__('Thousand Separator'))
              ->options([
                '.' => __('Dot (.)'),
                ',' => __('Comma (,)'),
                '' => __(key: 'None'),
              ])
              ->default(',')
              ->searchable()
              ->reactive(),

            TextInput::make('number_of_decimals')
              ->label(__('Number of Decimals'))
              ->default(2)
              ->maxValue(4)
              ->minValue(0)
              ->numeric()
              ->required()
              ->reactive()
              ->rules(['regex:/^\d$/']),

            Select::make('currency_position')
              ->label(__('Currency Position'))
              ->options([
                'prefix' => __('Prefix ($ 123)'),
                'suffix' => __('Suffix (123 $)'),
              ])
              ->default('prefix')
              ->required()
              ->searchable()
              ->reactive(),

            Select::make('rounding_method')
              ->label(__('Rounding Method'))
              ->options([
                '1' => __('Round Half Up'),
                '2' => __('Round Half Down'),
                '3' => __('Round to Nearest'),
              ])
              ->default('3')
              ->required()
              ->reactive()
              ->searchable()
              ->helperText(fn($state) => $this->getRoundingMethodDescription($state)),

            Toggle::make('space_between_symbol')
              ->label(__('Space Between Symbol'))
              ->default(true)
              ->inline(false)
              ->helperText(__('Enable space between the symbol and amount'))
              ->reactive(),
          ]),
        ])
        ->columns(2),
    ];
  }

  public function getRoundingMethodDescription($state)
  {
    switch ($state) {
      case '1':
        return __('Rounds up at .5 and above.');
      case '2':
        return __('Rounds down at .5 and below.');
      case '3':
        return __('Rounds to the nearest, with ties rounded to the even number.');
      default:
        return '';
    }
  }

  public function generateCurrencyPreview(Get $get)
  {
    $currencyId = (int) $get('currency_id');
    $decimalSeparator = $get('decimal_separator');
    $thousandSeparator = $get('thousand_separator');
    $numberOfDecimals = (int) $get('number_of_decimals');
    $currencyPosition = $get('currency_position');
    $symbolCase = $get('symbol_case');
    $roundingMethod = (int) $get('rounding_method');
    $spaceBetweenSymbol = (bool) $get('space_between_symbol');

    if ($numberOfDecimals < 0 || $numberOfDecimals > 4) {
      $numberOfDecimals = 4;
    }

    $currency = Currency::find($currencyId);
    $symbol = $currency ? $currency->icon : '₹';

    $amount = 1234567.89;

    $amount = $this->applyRounding($amount, $roundingMethod, $numberOfDecimals);

    $formattedAmount = number_format($amount, $numberOfDecimals, $decimalSeparator, $thousandSeparator);

    $symbol = $this->formatSymbolCase($symbol, $symbolCase);

    return $this->buildFormattedCurrency($formattedAmount, $symbol, $currencyPosition, $spaceBetweenSymbol);
  }

  public function applyRounding($amount, $roundingMethod, $numberOfDecimals)
  {
    switch ($roundingMethod) {
      case 1:
        return ceil($amount);
      case 2:
        return floor($amount);
      case 3:
        return round($amount, $numberOfDecimals);
      default:
        return round($amount, $numberOfDecimals);
    }
  }

  public function formatSymbolCase($symbol, $symbolCase)
  {
    if ($symbolCase === 'uppercase') {
      return strtoupper($symbol);
    } elseif ($symbolCase === 'lowercase') {
      return strtolower($symbol);
    }
    return $symbol;
  }

  public function buildFormattedCurrency($formattedAmount, $symbol, $currencyPosition, $spaceBetweenSymbol)
  {
    if ($currencyPosition === 'prefix') {
      return $symbol . ($spaceBetweenSymbol ? ' ' : '') . $formattedAmount;
    } else {
      return $formattedAmount . ($spaceBetweenSymbol ? ' ' : '') . $symbol;
    }
  }
}
