<?php

abstract class Tools
{
  private static
    $translit_table = array(
      '"' => '',
      'а' => 'a',
      'б' => 'b',
      'в' => 'v',
      'г' => 'g',
      'д' => 'd',
      'е' => 'e',
      'ё' => 'yo',
      'ж' => 'zh',
      'з' => 'z',
      'и' => 'i',
      'й' => 'y',
      'к' => 'k',
      'л' => 'l',
      'м' => 'm',
      'н' => 'n',
      'о' => 'o',
      'п' => 'p',
      'р' => 'r',
      'с' => 's',
      'т' => 't',
      'у' => 'u',
      'ф' => 'f',
      'х' => 'h',
      'ц' => 'ts',
      'ч' => 'ch',
      'ш' => 'sh',
      'щ' => 'sch',
      'ъ' => 'i',
      'ы' => 'y',
      'ь' => '',
      'э' => 'eh',
      'ю' => 'yu',
      'я' => 'ya',
    );

  public static
    $slink = array (
      '65a2819958f331b5e1f6ccee64006bc7' => array (
        'type' => 'elitenew',
        'lots' => '1213,2309',
      ),
      '2ff311681a91164a83a76e0bcb5f1b7a' => array (
        'type' => 'outoftown',
        'lots' => '5119,5064,5063,5051,5050,5049,5047,5046,5044,1130,4189,4176',
      ),
      'd4d0eabc69d5cea7e031a92ee6e79f7c' => array (
        'estate' => 'Четыре солнца',
        'type' => 'elitenew',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      'ab0bf5cc20b5e5eeea89275351f07110' => array (
        'estate' => 'Усадьба Трубецких',
        'type' => 'eliteflat',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      'ef6198277c680e083839910a2a19f3a5' => array (
        'street' => 'Central Street',
        'type' => 'comsell',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      '7bd23dab8f8d482a3a2d053e87634dcf' => array (
        'cottageVillage' => 'Довиль',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      '8382f99d86df4b9c42081b5696fe42f7' => array (
        'cottageVillage' => 'Павлово',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      '2bd13a0a54657224284322e1359ce03e' => array (
        'cottageVillage' => 'Трувиль',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      'a1e3ba31b1f573609d9bc7c36830d5c6' => array (
        'cottageVillage' => 'Целеево',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      '394aaa77e4b666bb1bdc0ccac8df9e8b' => array (
        'cottageVillage' => 'Булгаков',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      '6f7d2149fb7fb0ae6043e45c5885e213' => array (
        'cottageVillage' => 'Николино',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      '3cb4ea6e187816c6105faade8333b7c9' => array (
        'cottageVillage' => 'Жуковка XXI',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      'a8d238f1b3df47bd5271163301df1249' => array (
        'cottageVillage' => 'Прозорово',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      '6a958d405bca153d806e00fa92913f36' => array (
        'cottageVillage' => 'Графский лес',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      '5e97b61495d57307dbd977c0f6cc6451' => array (
        'cottageVillage' => 'Новое Лапино',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      'd1df670dad9c880bbf79ad031dcfe4ad' => array (
        'cottageVillage' => 'LeVitan (ЛеВитан)',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      'e63b1c202489b41e2f8229124d3324c1' => array (
        'cottageVillage' => 'Серебряный Век',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      'ea6cfef0197194e20746be3f4549667d' => array (
        'cottageVillage' => 'Бельгийская деревня',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      '64f9cb599a8488acb077bc99edb72313' => array (
        'cottageVillage' => 'Западная Резиденция',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      'be99c7e224524bdc538dc9dfc4485fff' => array (
        'cottageVillage' => 'Резиденции Бенилюкс',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      'e8f119005dfb1653e9a780b7c7e98dd7' => array (
        'cottageVillage' => 'Rubin Estate (Рубин Эстейт)',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
      'e2d06985cae69094e4bff2a4b6470b5f' => array (
        'cottageVillage' => 'Гольф и яхт-клуб «Пестово»',
        'type' => 'outoftown',
        'per_page' => 50,
        'no_price_ok' => 'on',
      ),
    ),

    $_address_replace_table = array(
      'numbers' => array(
        '-ая' => '-я',
        '-ый' => '-й',
        '-ой' => '-й',
        '-ий' => '-й',
        '-ое' => '-е',
      ),
      'types' => array(
        'улица'    => 'ул.',

        'проезд'   => 'пр.',
        'пр-д'     => 'пр.',

        'проспект' => 'просп.',
        'пр-т'     => 'просп.',

        'бульвар'  => 'бул.',
        'б-р'      => 'бул.',

        'тупик'    => 'туп.',

        'переулок' => 'пер.',

        'набережная' => 'наб.',

        'шоссе' => 'ш.'
      ),
      'particulars' => array(
        '/,(.*?)$/' => '',
        '/БЦ\s.*?$/u' => '',

        '/[Сс]ивцев\s[Вв]ражек.*/u' => 'Сивцев Вражек пер.',
        '/^(.*?)\s([Мм]ал[аоы][яей]|[Бб]ольш[аоы][яей]|[Нн]ов[аоы][яей]|[Cc]тар[аоы][яей])\s(.*?)/u' => '$2 $1 $3',
        '/^(.*?)\s([Мм]ал[аоы][яей]|[Бб]ольш[аоы][яей]|[Нн]ов[аоы][яей]|[Cc]тар[аоы][яей])/u' => '$2 $1',

        '/(.*)\s?(ул\.|пр\.|ш\.|просп\.|бул\.|туп\.|пер\.|наб\.|пл\.)\s+(.+)/u' => '$1 $3 $2',
        //'/(ул\.|пр\.|ш\.|просп\.|бул\.|туп\.|пер\.|наб\.|пл\.)\s+(.*)/u' => '$2 $1',

        '/(.*?)\s?([\d]+-[йея])\s+(.*?)/u' => '$2 $1 $3',

        '/\s+/' => ' ',
        '/\s*\.\s+/' => ' ',
        '/\s\.$/' => '.',
      )
  );

  public static function slugify($text)
  {
    $text = mb_strtolower($text);
    $text = str_replace(array_keys(self::$translit_table), array_values(self::$translit_table), $text);
    $text = preg_replace('/\W+/', '-', $text);

    return trim($text, '-');
  }

  public static function getOfferCategoriesSorted($type)
  {
    switch ($type) {
      case 'comsell':
      case 'comrent':
        return array_combine(
          Param::$_listable_by_property[$type]['objecttype'],
          Param::$_listable_by_property[$type]['objecttype']
        );

      case 'outoftown':
        $options = sfConfig::get('app_wards');
        $order = array(22,16,8,14,15,25,11,9,12,6,27,19,1,2,3,4,5,7,10,13,17,18,20,21,23,24,26);
        break;

      case 'cottage':
        $options = sfConfig::get('app_wards');
        $order = array(22,16,14,11,9,6,1,2,3,4,5,7,8,10,12,13,15,14,17,18,19,20,21,23,24,25,26,27);
        break;

      default:
        $options = sfConfig::get('app_districts');
        $order = array(9,1,2,14,12,28,7,26,8,11,3,4,15,5,10,6,13,16,30,17,18,25,19,20,21,22,23,24,27,29,32,31);
        break;
    }

    uksort($options, function($a, $b) use ($order) {
      return array_search($a, $order) - array_search($b, $order);
    });

    return $options;
  }

  public static function removeCacheFile($file = 'config_app.yml.php')
  {
    $fs = new sfFilesystem();
    $fs->remove(sfFinder::type('file')->name($file)->in(sfConfig::get('sf_cache_dir')));
  }

  public static function getFrontendContext()
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    return sfContext::createInstance($configuration, 'prod');
  }

  public static function getValueOfPreset($preset, $revert = false)
  {
    $preset = trim($preset);
    if(empty($preset)) {
      return null;
    }
    $config = sfConfig::getAll();
    $result = array();
    foreach($config['app_districts'] as $id => $value) {
      if(!empty($config['app_search_presets']['districts'][$id])) {
        $key = $config['app_search_presets']['districts'][$id];
        $result[$key] = $value;
      }
    }
    foreach($config['app_wards'] as $id => $value) {
      if(!empty($config['app_search_presets']['wards'][$id])) {
        $key = $config['app_search_presets']['wards'][$id];
        $result[$key] = $value;
      }
    }
    foreach($config['app_search_presets']['objecttype'] as $key=>$value) {
      $result[$value] = $key;
    }

    if ($revert) {
      return in_array($preset, $result) ? array_search($preset, $result) : null;
    }
    return !empty($result[$preset]) ? $result[$preset] : null;
  }

  public static function prepareStreet($address)
  {
    $address = strtr($address, array(
      '  ' => ' ',
      ' - ' => '-'
    ));
    $address = strtr($address, array(
      'ё'  => 'е',
      '9/11' => '',
      ',"Юсупов Двор", галерея элитных офисных особняков.' => '',
      ',"Юсупов Двор",галерея элитных офисных особняков.' => '',
      ' Вал' => ' вал',
      'Тверская-Ямская' => 'Тверская Ямская',
      'Тверской-Ямской' => 'Тверской Ямской'
    ));

    $address = strtr($address, self::$_address_replace_table['types']);
    $address = strtr($address, self::$_address_replace_table['numbers']);
    $address = preg_replace(array_keys(self::$_address_replace_table['particulars']), array_values(self::$_address_replace_table['particulars']), $address);

    $address .= ($address != '' ? '.' : '');

    return strtr($address, array(
      '..'   => '.',
      ' .' => '.'
    ));
  }

  public static function getAddressAbbreviations() {
    return array_unique(self::$_address_replace_table['types']);
  }

  public static function getXmlWriter($partner, $type, $main_dir = 'export')
  {
    $web = sfConfig::get('sf_web_dir');
    $xml = $web . '/' . $main_dir;
    if(!is_dir($xml)) {
      mkdir($xml, 0775);
    }
    $partner = $xml . '/' . $partner;
    if(!is_dir($partner)) {
      mkdir($partner, 0775);
    }

    $w = new XMLWriter();
    $w->openUri(sprintf('%s/%s.tmp', $partner, $type));
    $w->setIndentString(str_repeat(" ", 2));
    $w->setIndent(true);
    return $w;
  }

  public static function getFileNameForXmlFile($partner, $type, $page = 1, $main_dir = 'export')
  {
    $web = sfConfig::get('sf_web_dir');
    $xml = $web . '/' . $main_dir;
    if(!is_dir($xml)) {
      mkdir($xml, 0775);
    }
    $partner = $xml . '/' . $partner;
    if(!is_dir($partner)) {
      mkdir($partner, 0775);
    }
    if($page > 1) {
      $type = sprintf('%s_%s', $type, $page);
    }
    return sprintf('%s/%s.tmp', $partner, $type);
  }

  public static function rollOutXmlFile($partner, $type, $page = 1, $main_dir = 'export')
  {
    if($page > 1) {
      $type = sprintf('%s_%s', $type, $page);
    }
    $path = sprintf('%s/%s/%s/%s', sfConfig::get('sf_web_dir'), $main_dir, $partner, $type);
    $file = $path . '.%s';
    rename(sprintf($file, 'tmp'), sprintf($file, 'xml'));
  }

  public static function uw($text) {
    return iconv('utf-8', 'windows-1251', $text);
  }
}