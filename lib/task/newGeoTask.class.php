<?php

class newGeoTask extends sfBaseTask
{
  
  protected 
    $_city = array(
      'Аксаково',
      'Аксиньино',
      'Александровка',
      'Апрелевка',
      'Афинеево',
      'Барвиха',
      'Бачурино',
      'Большое Покровское',
      'Большое Сареево',
      'Борзые',
      'Борисково',
      'Борисовка',
      'Борки',
      'Бузаево',
      'Бузланово',
      'Буньково',
      'Бурцево',
      'Бушарино',
      'Былово',
      'Бяконтово',
      'Ватутинки',
      'Веледниково',
      'Вешки',
      'Власово',
      'Воронино',
      'г.Пушкино, микрорайон Клязьма',
      'Гавриково',
      'Глухово',
      'Горки 2',
      'Горки-2',
      'Горки-8',
      'Грибово (Бородки)',
      'дачный поселок Мастера Искусств',
      'дачный поселок Новодарьино РАН',
      'деревня Аносино',
      'деревня Барвиха',
      'деревня Борки',
      'деревня Бородки',
      'деревня Бузланово',
      'деревня Бурцево',
      'деревня Веледниково',
      'деревня Витенево',
      'деревня Внуково',
      'деревня Воронино',
      'деревня Горышкино',
      'деревня Зименки',
      'деревня Коростово',
      'деревня Крекшино',
      'деревня Лапино',
      'деревня Лобаново',
      'деревня Лохино',
      'деревня Лупаново',
      'деревня Лызлово',
      'деревня Макарово',
      'деревня Максимовка',
      'деревня Матвейково',
      'деревня Митькино',
      'деревня Михалково',
      'деревня Молоденово',
      'деревня Немчиново',
      'деревня Николо-Урюпино',
      'деревня Никульское',
      'деревня Новинки',
      'деревня Новогрязново',
      'деревня Павловское',
      'деревня Пестово',
      'деревня Пирогово',
      'деревня Писково',
      'деревня Подольниха',
      'деревня Покровское',
      'деревня Полежайки',
      'деревня Раево',
      'деревня Румянцево',
      'деревня Сазонки',
      'деревня Сельская Новь',
      'деревня Сивково',
      'деревня Синьково',
      'деревня Сколково',
      'деревня Солослово',
      'деревня Спас-Каменка',
      'деревня Степановское',
      'деревня Степаньково',
      'деревня Тимошкино',
      'деревня Трусово',
      'деревня Хлябово',
      'деревня Чесноково',
      'деревня Чивирево',
      'деревня Шаганино',
      'деревня Шульгино',
      'деревня Якиманское',
      'Ершово',
      'Жаворонки',
      'Жуковка',
      'Завидово',
      'Загорянский',
      'Зайцево-Ямищево',
      'Заречье',
      'Звенигород',
      'Звягино',
      'Зеленоград',
      'Знаменское',
      'Ивантеевка',
      'Ивачково',
      'Ильинское',
      'Ильнское',
      'Иславское',
      'Каменка',
      'Клязьма-2',
      'Королев',
      'Котово',
      'Коттеджный поселок «Дубовая Роща»',
      'Коттеджный поселок «Жуковка-3»',
      'Коттеджный поселок «Сосновый бор»',
      'Коттеджный поселок «Уборы»',
      'Коттеджный поселок «Чистые пруды 2»',
      'коттеджный поселок Альпийская Долина',
      'коттеджный поселок Архангельское-2',
      'коттеджный поселок Визендорф',
      'коттеджный поселок Военнослужащий',
      'коттеджный поселок Высокий Берег',
      'коттеджный поселок Глухово',
      'коттеджный поселок Горки-6',
      'коттеджный поселок Графские Пруды',
      'коттеджный поселок Грибово',
      'коттеджный поселок Гринфилд',
      'коттеджный поселок Дачи Хонка',
      'коттеджный поселок Жуковка-3',
      'коттеджный поселок Западные Резиденции',
      'коттеджный поселок Зеленая Лощина',
      'коттеджный поселок Золотой Город',
      'коттеджный поселок Истринские Усадьбы',
      'коттеджный поселок Кедры',
      'коттеджный поселок Княжье Озеро',
      'коттеджный поселок Кунцево-2',
      'коттеджный поселок Любушкин Хутор',
      'коттеджный поселок Миллениум Парк',
      'коттеджный поселок Монтевиль',
      'коттеджный поселок Николина Поляна',
      'коттеджный поселок Николино',
      'коттеджный поселок Новое Глаголево',
      'коттеджный поселок Новые Вёшки',
      'коттеджный поселок Огниково Парк',
      'коттеджный поселок Олимпийская деревня Новогорск',
      'коттеджный поселок Ольгино',
      'коттеджный поселок Онегино',
      'коттеджный поселок Павлово-2',
      'коттеджный поселок Парк Рублево',
      'коттеджный поселок Пенаты',
      'коттеджный поселок Пестовское',
      'коттеджный поселок Прозорово',
      'коттеджный поселок Пушкинский Лес',
      'коттеджный поселок Резиденция Бенилюкс',
      'коттеджный поселок Резиденция Монолит',
      'коттеджный поселок Резиденция Рублево',
      'коттеджный поселок Риверсайд',
      'коттеджный поселок Росинка',
      'коттеджный поселок Соловьиный',
      'коттеджный поселок Усадьбы Усово',
      'коттеджный поселок Успенские Дачи',
      'коттеджный поселок Шервуд',
      'коттеджный поселок Юлия-1',
      'Красное',
      'Крекшино',
      'Крючково',
      'Кузеново',
      'Кузнецово',
      'Лайково',
      'Лесные дали',
      'Лесные поляны',
      'Летово',
      'Малаховка',
      'Мамоново',
      'Маслово',
      'микрорайон «Клязьма»',
      'Минзаг',
      'Молодeново',
      'Москва',
      'Мураново',
      'Мытищи',
      'Мякинино',
      'Нагорное',
      'Нахабино',
      'Немчиновка',
      'Николина Гора',
      'Николина гора',
      'Николо-Урюпино',
      'Николо-хованское',
      'Николо-Хованское',
      'Новинки',
      'Новоглаголево',
      'Новогорск',
      'Новосумино',
      'Обушково',
      'Одинцово',
      'Павловская cлобода',
      'Павловская слобода',
      'Палицы',
      'Первомайское',
      'Переделкино',
      'Переделкино.',
      'Перхушково',
      'Пионерский ',
      'Пироговский',
      'Писково',
      'Подпорино',
      'Подушкино',
      'Поздняково',
      'Покровское',
      'Поречье',
      'поселок Ашукино',
      'поселок Барвиха',
      'поселок Богородское',
      'поселок Ватутинки',
      'поселок Горки-10',
      'поселок Горки-2',
      'поселок городского типа Кокошкино',
      'поселок городского типа Львовский',
      'поселок городского типа Черкизово',
      'поселок дачного хозяйства Жуковка',
      'поселок Дубровицы',
      'поселок Заречье',
      'поселок Костино',
      'поселок Красное',
      'поселок Лесные Поляны',
      'поселок Назарьево',
      'поселок НИИ Радио',
      'поселок Николина Гора',
      'поселок Новоглаголево',
      'поселок Новое Лапино',
      'поселок Раздоры',
      'поселок Рождественно',
      'поселок Строитель',
      'Прислон',
      'Пушкино',
      'Раздоры',
      'Рассудово',
      'Рождественно',
      'Рублево-Успенское шоссе 17 км',
      'Румянцево',
      'садовое товарищество Весна',
      'садовое товарищество Грибки',
      'садовое товарищество Колос',
      'садовое товарищество Лира',
      'садовое товарищество Лужки',
      'садовое товарищество Сетунька',
      'садовое товарищество Урожай',
      'Сапожок',
      'село Ангелово',
      'село Виноградово',
      'село Жаворонки',
      'село Лайково',
      'село Лучинское',
      'село Немчиновка',
      'село Павловская Слобода',
      'село Петрово-Дальнее',
      'село Усово',
      'село Успенское',
      'Сивково',
      'Славково',
      'СНТ «Зеленый Ветер-1»  ',
      'Солослово',
      'Столбово',
      'Сысоево',
      'Таганьково',
      'Тарасково',
      'Тимошкино',
      'Трубачеевка',
      'Трудовая Северная',
      'Трусово',
      'Уборы',
      'Усово',
      'Успенское',
      'Фоминское',
      'Химки',
      'Целеево',
      'Чесноково',
      'Чигасово',
      'Шаганино',
      'Шишкин Лес',
      'Шульгино',
      'Юдино',
      'Юрлово',
      'Юрово',
      'Ярославского шоссе  17 км',
    ),
    $_city2 = array(
        'Авсюнино',
        'Андреевка',
        'Апрелевка',
        'Архангельское',
        'Ашукино',
        'Балашиха',
        'Барвиха',
        'Белоозёрский',
        'Белоомут',
        'Биокомбината',
        'Богородское',
        'Большевик',
        'Большие Вяземы',
        'Большие Дворы',
        'Большое Буньково ',
        'Бронницы',
        'Быково',
        'Ватутинки',
        'Вербилки',
        'Верея',
        'Видное',
        'Володарского',
        'Волоколамск',
        'Воскресенск',
        'Высоковск',
        'Глебовский',
        'Голицыно',
        'Горки-10',
        'Давыдово',
        'Деденево',
        'Дедовск',
        'Демихово',
        'Дзержинский',
        'Дмитров',
        'Долгопрудный',
        'Домодедово',
        'Дорохово',
        'Дрезна',
        'Дружба',
        'Дубна',
        'Дубовая Роща',
        'Егорьевск',
        'Железнодорожный',
        'Жуковский',
        'Мосрентген',
        'Загорянский',
        'Запрудня',
        'Зарайск',
        'Заречье',
        'Звенигород',
        'Знамя Октября',
        'Ивантеевка',
        'Икша',
        'Ильинский',
        'Ильинское',
        'Воровского',
        'Истра',
        'Калининец',
        'Кашира',
        'Киевский',
        'Климовск',
        'Клин',
        'Кокошкино',
        'Коломна',
        'Коммунарка',
        'Константиново',
        'Коренево',
        'Королев',
        'Котельники',
        'Красково',
        'Красноармейск',
        'Красногорск',
        'Краснозаводск',
        'Краснознаменск',
        'Кратово',
        'Кубинка',
        'Куровское',
        'Лесной',
        'Лесной Городок',
        'Лесные Поляны',
        'Ликино-Дулево',
        'ЛМС',
        'Лобня',
        'Лосино-Петровский',
        'Лотошино',
        'Лунёво',
        'Луховицы',
        'Лыткарино',
        'Львовский',
        'Люберцы',
        'Любучаны',
        'Малаховка',
        'Малино',
        'Марфино',
        'Менделеево',
        'Мещерино',
        'Михнево',
        'Можайск',
        'Монино',
        'Московский',
        'Мытищи',
        'Наро-Фоминск',
        'Нахабино',
        'Некрасовский',
        'Новоивановское',
        'Новопетровское',
        'Новосиньково',
        'Ногинск',
        'Оболенск',
        'Обухово',
        'Одинцово',
        'Ожерелье',
        'Озёры',
        'Октябрьский',
        'Орехово-Зуево',
        'Островцы',
        'Павловская Слобода',
        'Павловский Посад',
        'Первомайский',
        'Пересвет',
        'Пески',
        'Пироговский',
        'Поварово',
        'Подольск',
        'Воскресенское',
        'Правдинский',
        'Пролетарский',
        'Протвино',
        'Пушкино',
        'Пущино',
        'Развилка',
        'Раменское',
        'Реммаш',
        'Реутов',
        'Речицы',
        'Решетниково',
        'Ржавки',
        'Рогачево',
        'Родники',
        'Рождествено',
        'Рошаль',
        'Руза',
        'Свердловский',
        'Селятино',
        'Сергиев Посад',
        'Серебряные Пруды',
        'Серпухов',
        'Скоропусковский',
        'Снегири',
        'Солнечногорск',
        'Софрино',
        'Старая Купавна',
        'Старый Городок',
        'Столбовая',
        'Ступино',
        'Сычево',
        'Талдом',
        'Томилино',
        'Троицк',
        'Троицкое',
        'Тучково',
        'Уваровка',
        'Удельная',
        'Узуново',
        'Фрязино',
        'Фряново',
        'Химки',
        'Хорлово',
        'Хотьково',
        'Черкизово',
        'Черноголовка',
        'Черное',
        'Черусти',
        'Чехов',
        'Шатура',
        'Шатурторф',
        'Шаховская',
        'Шишкин Лес',
        'Щербинка',
        'Щёлково',
        'Электрогорск',
        'Электроизолятор',
        'Электросталь',
        'Электроугли',
        'Юбилейный',
        'Яковлевское',
        'Ям',
        'Яхрома',
        );
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = '';
    $this->name             = 'newGeo';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    foreach ($this->_city2 as $value2) {
        $coordsArray[$value2] = $this->getCoords($value2);
      }
    $filename = Tools::getFileNameForXmlFile(NULL, 'NearCity', NULL);
    $w = new XMLWriter();
    $w->openUri($filename);
    $w->setIndentString(str_repeat(" ", 2));
    $w->setIndent(true);
    $w->startDocument('1.0', 'utf-8');
    $w->startElement('City');  
    foreach ($this->_city as $value) {
      $coords1 = $this->getCoords($value);      
      //$value = preg_replace('/\s+/', '_', $value);
      //var_dump ($value);      
       $w->writeElement('Nearest', $value . ',' . $this->getNearestCity($coords1, $coordsArray));       
    }
    $w->endElement();//City
    $w->endDocument();
    $w->flush();
    unset($w);
    Tools::rollOutXmlFile(NULL, 'NearCity', NULL);
    
   }

  private function getCoords($address)
  {
    if (empty($address)) {
      return false;
    }

    $q = urlencode(sprintf(
      'Россия, Московская область, %s',
      $address));
    $url = sprintf(
      'http://geocode-maps.yandex.ru/1.x/?geocode=%s&results=1&key=%s&format=json',
      $q, 'AFSc8U0BAAAAFz4gBwIARHvsE1k9c3pWG0BT4oAmr3oPZWcAAAAAAAAAAACAnZ30_0RaHedtEyv8-C9cCZRlWQ=='
    );   
   
    $_content = curl_init($url);
    curl_setopt($_content, CURLOPT_RETURNTRANSFER, 1);
    $geo = curl_exec($_content);    
    curl_close($_content);
    
    $geo = json_decode($geo);

    if (isset($geo->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos)) {
      return array_reverse(explode(' ', $geo->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos));
    }
  }
  
  private function calculateTheDistance ($φA, $λA, $φB, $λB) 
  {
      // перевести координаты в радианы
      $lat1 = $φA * M_PI / 180;
      $lat2 = $φB * M_PI / 180;
      $long1 = $λA * M_PI / 180;
      $long2 = $λB * M_PI / 180;

      // косинусы и синусы широт и разницы долгот
      $cl1 = cos($lat1);
      $cl2 = cos($lat2);
      $sl1 = sin($lat1);
      $sl2 = sin($lat2);
      $delta = $long2 - $long1;
      $cdelta = cos($delta);
      $sdelta = sin($delta);

      // вычисления длины большого круга
      $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
      $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

      //
      $ad = atan2($y, $x);
      $dist = $ad * 6371;

      return $dist;
  }
  
  private function getNearestCity($coords1, array $coordsArray)
  {
    $current = 0;
    $lowest = 500;
    $city = null;
    foreach ($coordsArray as $key => $coords2) {
      
      $current = $this->calculateTheDistance($coords1[0],$coords1[1],$coords2[0],$coords2[1]);
      if($current < $lowest) {
        $lowest = $current;
        $city = $key;
      }
      
    }
   return $city;
  }
}
