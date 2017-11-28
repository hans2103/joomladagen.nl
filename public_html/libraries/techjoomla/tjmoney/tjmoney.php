<?php
/**
 * @package    Techjoomla.Libraries
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * It contains multiple functions for currencies
 *
 * @since  1.0
 */
class TjMoney
{
	private $code;

	private $subunit;

	private $symbol;

	private $numericCode;

	private $alphabeticCode;

	private $currency;

	/**
	 * The constructor
	 *
	 * @param   STRING  $code  code
	 *
	 * @since  1.0
	 */
	public function __construct($code)
	{
		try
		{
			// Initialize object
			$this->setCode($code);
			$this->setSubunit($this);
			$this->setSymbol($this);
			$this->setAlphabeticCode($this);
			$this->setNumericCode($this);
			$this->setCurrency($this);
			$this->setDecimal($this);
		}
		catch (Exception $e)
		{
			echo 'Caught exception: ' . $e->getMessage(), "\n";
		}
	}

	/**
	 * Return code
	 *
	 * @return STRING
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Return subunit
	 *
	 * @return Int
	 */
	public function getSubunit()
	{
		return $this->subunit;
	}

	/**
	 * Return symbol
	 *
	 * @return STRING
	 */
	public function getSymbol()
	{
		return $this->symbol;
	}

	/**
	 * Return Numeeric code
	 *
	 * @return Int
	 */
	public function getNumericCode()
	{
		return $this->numericCode;
	}

	/**
	 * Return Aplhabetic code
	 *
	 * @return STRING
	 */
	public function getAlphabeticCode()
	{
		return $this->alphabeticCode;
	}

	/**
	 * Return currency name
	 *
	 * @return STRING
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * Return currency name
	 *
	 * @return STRING
	 */
	public function getDecimal()
	{
		return $this->decimal;
	}

	/**
	 * The function to Check is this currency exist
	 *
	 * @return  BOOLEAN
	 *
	 * @since   1.0
	 */
	public function isContains()
	{
		$isContains = isset($this->getCurrencies()[$this->getCode()]);

		if (!$isContains)
		{
			throw new Exception('Cannot find ISO currency ' . $this->getCode());
		}

		return $isContains;
	}

	/**
	 * The function to set currency code
	 *
	 * @param   string  $code  code.
	 *
	 * @return  OBJECT
	 *
	 * @since   1.0
	 */
	public function setCode($code)
	{
		if (!is_string($code))
		{
			throw new Exception('Currency code should be string');
		}

		$this->code = $code;

		return $this;
	}

	/**
	 * The function to set decimal
	 *
	 * @return  OBJECT
	 *
	 * @since   1.0
	 */
	public function setDecimal()
	{
		if ($this->isContains())
		{
			$this->decimal = $this->getCurrencies()[$this->getCode()]['decimal'];
		}

		return $this;
	}

	/**
	 * The function to set Subunit
	 *
	 * @return  OBJECT
	 *
	 * @since   1.0
	 */
	public function setSubunit()
	{
		if ($this->isContains())
		{
			$this->subunit = $this->getCurrencies()[$this->getCode()]['minorUnit'];
		}

		return $this;
	}

	/**
	 * The function to set currency name
	 *
	 * @return  OBJECT
	 *
	 * @since   1.0
	 */
	public function setCurrency()
	{
		if ($this->isContains())
		{
			if (empty($this->getCurrencies()[$this->getCode()]['currency']))
			{
				throw new Exception('Cannot find ISO currency Name for ' . $this->getCode());
			}

			$this->currency = $this->getCurrencies()[$this->getCode()]['currency'];

			return $this;
		}
	}

	/**
	 * The function to set Symbol
	 *
	 * @return  OBJECT
	 *
	 * @since   1.0
	 */
	public function setSymbol()
	{
		if (!$this->isContains())
		{
			throw new UnknownCurrencyException('Cannot find ISO currency ' . $this->getCode());
		}

		// Curency Mvdol doesn't have symbol then will show alphabetic code BOV
		if (empty($this->getCurrencies()[$this->getCode()]['symbol']))
		{
			$this->symbol = $this->getCurrencies()[$this->getCode()]['alphabeticCode'];
		}
		else
		{
			$this->symbol = $this->getCurrencies()[$this->getCode()]['symbol'];
		}

		return $this;
	}

	/**
	 * The function to set Numeric Code
	 *
	 * @return  OBJECT
	 *
	 * @since   1.0
	 */
	public function setNumericCode()
	{
		if ($this->isContains())
		{
			if (empty($this->getCurrencies()[$this->getCode()]['numericCode']))
			{
				throw new Exception('Cannot find ISO currency Numeric Code for ' . $this->getCode());
			}

			$this->numericCode = $this->getCurrencies()[$this->getCode()]['numericCode'];

			return $this;
		}
	}

	/**
	 * The function to set Alphabetic Code
	 *
	 * @return  OBJECT
	 *
	 * @since   1.0
	 */
	public function setAlphabeticCode()
	{
		if ($this->isContains())
		{
			if (empty($this->getCurrencies()[$this->getCode()]['alphabeticCode']))
			{
				throw new Exception('Cannot find ISO currency Alphabetic Code for ' . $this->getCode());
			}

			$this->alphabeticCode = $this->getCurrencies()[$this->getCode()]['alphabeticCode'];

			return $this;
		}
	}

	/**
	 * The function to iterator
	 *
	 * @return  ARRAY
	 *
	 * @since   1.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator(
			array_map(
				function ($code) {
					return new Currency($code);
				},
				array_keys($this->getCurrencies())
			)
		);
	}

	/**
	 * The function to get rounded currency
	 *
	 * @param   FLOAT  $amount  Ammount
	 *
	 * @return  FLOAT
	 *
	 * @since   1.0
	 */
	public function getRoundedValue($amount)
	{
		try
		{
			$isContain = $this->iscontains();

			if ($isContain)
			{
				$amt = round($amount, $this->getSubunit());
			}

			return $amt;
		}
		catch (Exception $e)
		{
			echo 'Caught exception: ' . $e->getMessage(), "\n";
		}
	}

	/**
	 * The function to return formatted amount
	 *
	 * @param   FLOAT   $amount  Amount
	 * @param   STRING  $config  CurrencyDisplayFormat
	 *
	 * @return  STRING
	 *
	 * @since   1.0
	 */
	public function displayFormattedValue($amount, $config)
	{
		$formattedAmount = "";

		// Get rounded amount amount as per the currecy before formatting
		$roundedAmt = $this->getRoundedValue($amount);

		// Do decimal formatting

		if (!empty($this->getDecimal()))
		{
			$roundedAmt = str_replace(".", $this->getDecimal(), $roundedAmt);
		}

		// Decide to display currency code or symbol

		$codeOrSymbol = $this->getSymbol();

		if (strtolower($config->CurrencyCodeOrSymbol) === 'code')
		{
			$codeOrSymbol = $this->getCode();
		}

		// Check Display symbol/ Code before or after amount
		if (str_replace(' ', '', $config->CurrencyDisplayFormat) == '{AMOUNT}{CURRENCY_SYMBOL}')
		{
			$formattedAmount = $roundedAmt . " " . $codeOrSymbol;
		}
		else
		{
			$formattedAmount = $codeOrSymbol . " " . $roundedAmt;
		}

		return $formattedAmount;
	}

	/**
	 * The function to compare currencies
	 *
	 * @param   FLOAT   $currencyOne  currencyOne
	 * @param   STRING  $currencyTwo  currencyTwo
	 *
	 * @return  ARRAY
	 *
	 * @since   1.0
	 */
	private static function  compare($currencyOne, $currencyTwo)
	{
		return strcasecmp($currencyOne["currency"], $currencyTwo["currency"]);
	}

	/**
	 * The function to return currencies
	 *
	 * @return  ARRAY
	 *
	 * @since   1.0
	 */
	public static function getCurrencies()
	{
		try
		{
			$rawCurrencies = self::loadCurrencies();

			// Sort Currency Ascending order
			usort($rawCurrencies, array('TjMoney','compare'));

			foreach ($rawCurrencies as $key => $value)
			{
				$currencies[$value['alphabeticCode']] = $value;
			}
		}
		catch (Exception $e)
		{
			echo 'Caught exception: ' . $e->getMessage(), "\n";
		}

		return $currencies;
	}

	/**
	 * The function to load currencies
	 *
	 * @return  ARRAY
	 *
	 * @since   1.0
	 */
	private static function loadCurrencies()
	{
		$file = __DIR__ . '/resources/currency.php';

		if (file_exists($file))
		{
			return require $file;
		}

		throw new Exception('Failed to load currency ISO codes.');
	}
}
