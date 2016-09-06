<?php

/**
 * Toolkit
 *
 * @author     Garin Studio
 */
abstract class Currency
{
  private static $rates;
  private static $currencies = array('RUR', 'EUR', 'USD');

  private static function checkCurrency($currency)
  {
    if (!is_array($currency)) {
      $currency = array($currency);
    }
    $rates = self::getRates();
    foreach ($currency as $c) {
      if (!isset($rates[$c])) {
        throw new sfException("Currency $c not supported yet");
      }
    }
  }

  public static function getRates()
  {
    if (null == self::$rates) {
      $cache  = KreCache::getInstance();
      $backup = new sfFileCache(array(
        'cache_dir' => sfConfig::get('sf_data_dir') . '/currency',
        'lifetime'  => 7 * 24 * 3600
      ));
      $cache_key = sprintf('currencies_%s', date('Y-m-d'));

      if ($cache->has($cache_key)) {
        self::$rates = json_decode($cache->get($cache_key), true);
        if (count(self::$rates) != count(self::$currencies)) {
          self::$rates = json_decode($backup->get('backup'), true);
        }
      }
      else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=' . date('d.m.Y.'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        if ($data) {
          $doc = new DOMDocument();
          $doc->loadXml($data);
          $rates = array('RUR' => 1);

          foreach ($doc->getElementsByTagName('Valute') as $item) {
            $code = $item->getElementsByTagName('CharCode')->item(0)->nodeValue;
            $rate = $item->getElementsByTagName('Value')->item(0)->nodeValue;
            if(in_array($code, self::$currencies)){
              $rates[$code] = (float) str_replace(',', '.', $rate);
            }
          }

          foreach ($rates as $currency => $rate) {
            foreach ($rates as $s_currency => $s_rate) {
              self::$rates[$currency][$s_currency] = $rate / $s_rate;
            }
          }

          $cache->set($cache_key, json_encode(self::$rates));
        }
        else {
          return self::$rates = json_decode($backup->get('backup'), true);
        }

        if (count(self::$rates) == count(self::$currencies)) {
          $backup->set('backup', json_encode(self::$rates));
        }
      }
    }

    return self::$rates;
  }

  public static function convert($amount, $from, $to)
  {
    if ($from == $to) {
      return $amount;
    }
    else {
      self::checkCurrency(array($from, $to));
      return round($amount * self::$rates[$from][$to], 4);
    }
  }

  public static function formatPrice($amount, $currency = 'RUR', $convert = null)
  {
    if ($convert) {
      $amount = self::convert($amount, $currency, $convert);
      $currency = $convert;
    }

    return self::signCurrency(self::formatNumber($amount), $currency);
  }

  public static function formatNumber($amount)
  {
    $amount = str_split((string) round($amount));
    $value = '';
    $c = 0;
    for ($i = count($amount) - 1; $i >= 0; $i--) {
      $value = $amount[$i] . ($c++ % 3 ? '' : '&nbsp;') . $value;
    }
    return $value;
  }

  public static function signCurrency($value, $currency)
  {
    if     ($currency == 'USD') $sign = '$';
    elseif ($currency == 'EUR') $sign = '&euro;';
    elseif ($currency == 'RUR') $sign = '&nbsp;руб.';

    if (isset($sign)) {
      return $value . $sign;
    }
    else {
      return $value;
    }
  }
}
