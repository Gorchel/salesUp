<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script
        src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
        crossorigin="anonymous"></script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=1a73413b-9630-45e3-a6a0-657353bd6219&lang=ru_RU" type="text/javascript">
    </script>
    <title>SalesUp</title>
</head>
<body>
    <div id="map" style="width: 800px; height: 600px; margin: 0px auto;"></div>
</body>

    <script type="text/javascript">
        $(document).ready(function() {
            // Функция ymaps.ready() будет вызвана, когда
            // загрузятся все компоненты API, а также когда будет готово DOM-дерево.
            ymaps.ready(init);
        });

        function init() {
            var myPlacemark,
                myMap = new ymaps.Map('map', {
                    center: [55.753994, 37.622093],
                    zoom: 12
                }, {
                    searchControlProvider: 'yandex#search'
                });

            // Слушаем клик на карте.
            myMap.events.add('click', function (e) {
                console.log(e);
                var coords = e.get('coords');
                console.log(coords);
                // Если метка уже создана – просто передвигаем ее.
                if (myPlacemark) {
                    myPlacemark.geometry.setCoordinates(coords);
                }
                // Если нет – создаем.
                else {
                    myPlacemark = createPlacemark(coords);
                    myMap.geoObjects.add(myPlacemark);
                    // Слушаем событие окончания перетаскивания на метке.
                    myPlacemark.events.add('dragend', function () {
                        getAddress(myPlacemark.geometry.getCoordinates());
                    });
                }
                getAddress(coords);
                getMetro(coords);
            });

            // Создание метки.
            function createPlacemark(coords) {
                return new ymaps.Placemark(coords, {
                    iconCaption: 'поиск...'
                }, {
                    preset: 'islands#violetDotIconWithCaption',
                    draggable: true
                });
            }

            // Определяем адрес по координатам (обратное геокодирование).
            function getAddress(coords) {
                myPlacemark.properties.set('iconCaption', 'поиск...');
                ymaps.geocode(coords).then(function (res) {
                    var firstGeoObject = res.geoObjects.get(0);
                    console.log(firstGeoObject);
                    myPlacemark.properties
                        .set({
                            // Формируем строку с данными об объекте.
                            iconCaption: [
                                // Название населенного пункта или вышестоящее административно-территориальное образование.
                                firstGeoObject.getLocalities().length ? firstGeoObject.getLocalities() : firstGeoObject.getAdministrativeAreas(),
                                // Получаем путь до топонима, если метод вернул null, запрашиваем наименование здания.
                                firstGeoObject.getThoroughfare() || firstGeoObject.getPremise()
                            ].filter(Boolean).join(', '),
                            // В качестве контента балуна задаем строку с адресом объекта.
                            balloonContent: firstGeoObject.getAddressLine()
                        });
                });
            }

            function getMetro(coords) {
                ymaps.geocode(coords, {
                    kind: 'metro',
                    results: 1
                }).then(function (res) {
                    // Задаем изображение для иконок меток.
                    res.geoObjects.options.set('preset', 'islands#redCircleIcon');
                    res.geoObjects.events
                        // При наведении на метку показываем хинт с названием станции метро.
                        .add('mouseenter', function (event) {
                            var geoObject = event.get('target');
                            myMap.hint.open(geoObject.geometry.getCoordinates(), geoObject.getPremise());
                        })
                        // Скрываем хинт при выходе курсора за пределы метки.
                        .add('mouseleave', function (event) {
                            myMap.hint.close(true);
                        });
                    // Добавляем коллекцию найденных геообъектов на карту.
                    myMap.geoObjects.add(res.geoObjects);
                    // Масштабируем карту на область видимости коллекции.
                    // myMap.setBounds(res.geoObjects.getBounds());
                });
            }
        }
    </script>

</html>
