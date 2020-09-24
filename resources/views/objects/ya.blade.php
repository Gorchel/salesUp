<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <script
        src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=1a73413b-9630-45e3-a6a0-657353bd6219&lang=ru_RU" type="text/javascript">
    </script>
    <title>SalesUp</title>
    <style>
        .form-group {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form action="/webhook_objects" method="POST">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div id="map" style="width: 100%; height: 400px; display: inline-block;"></div>
                </div>
            </div>

            <input type="hidden" name="center_longitude" value="{{!empty($longitude) ? $longitude : 37.622093}}">
            <input type="hidden" name="center_latitude" value="{{!empty($latitude) ? $latitude : 55.753994}}">

            @if ($type == 'estate-properties')
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 text-center">
                        <input type="hidden" name="token" value="{{$token}}">
                        <input type="hidden" name="id" value="{{$id}}">
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 text-center">
                        <input type="submit" class="btn btn-sm btn-info" value="Сохранить">
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="row">
                        <div class="col-lg-12 form-group">
                            <label for="Адрес">Адрес</label>
                            <input type="text" class="form-control input-sm" name="address" readonly="readonly" value="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label for="Широта">Широта</label>
                            <input type="text" class="form-control input-sm" name="latitude" readonly="readonly" value="">
                        </div>
                        <div class="col-lg-6 form-group">
                            <label for="Долгота">Долгота</label>
                            <input type="text" class="form-control input-sm" name="longitude" readonly="readonly" value="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 form-group">
                            <label for="Метро">Метро</label><input type="text" class="form-control input-sm" name="metro" readonly="readonly" value="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 form-group">
                            <label for="Район">Район</label>
                            <input type="text" name="district" class="form-control input-sm" readonly="readonly" value="">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>

    <script type="text/javascript">
        $(document).ready(function() {
            // Функция ymaps.ready() будет вызвана, когда
            // загрузятся все компоненты API, а также когда будет готово DOM-дерево.
            ymaps.ready(init);
        });

        function init() {
            var start_coordinates = [$('[name="center_latitude"]').val(), $('[name="center_longitude"]').val()];

            var myPlacemark,
                myMap = new ymaps.Map('map', {
                    center: start_coordinates,
                    zoom: 16
                }, {
                    searchControlProvider: 'yandex#search'
                });

            setPlacemark(start_coordinates);

            // Слушаем клик на карте.
            myMap.events.add('click', function (e) {
                // console.log(e);
                var coords = e.get('coords');


                // console.log(coords);
                // Если метка уже создана – просто передвигаем ее.
                if (myPlacemark) {
                    myPlacemark.geometry.setCoordinates(coords);
                    $('[name="latitude"]').val(coords[0]);
                    $('[name="longitude"]').val(coords[1]);
                }
                // Если нет – создаем.
                else {
                    setPlacemark(coords);
                }

                getAddress(coords);
                getMetro(coords);
                getDisctrict(coords);
            });

            function setPlacemark(coords) {
                myPlacemark = createPlacemark(coords);
                myMap.geoObjects.add(myPlacemark);
                // Слушаем событие окончания перетаскивания на метке.
                myPlacemark.events.add('dragend', function () {
                    var placemarkCooed = myPlacemark.geometry.getCoordinates();

                    getAddress(placemarkCooed);
                    getMetro(placemarkCooed);
                    getDisctrict(placemarkCooed);

                    $('[name="latitude"]').val(placemarkCooed[0]);
                    $('[name="longitude"]').val(placemarkCooed[1]);
                });
            }

            // Создание метки.
            function createPlacemark(coords) {
                return new ymaps.Placemark(coords, {
                    // iconCaption: 'поиск...'
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
                    // console.log(firstGeoObject);
                    $('[name="address"]').val(firstGeoObject.getAddressLine());
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
                    // // Задаем изображение для иконок меток.
                    // res.geoObjects.options.set('preset', 'islands#redCircleIcon');
                    // res.geoObjects.events
                    //     // При наведении на метку показываем хинт с названием станции метро.
                    //     .add('mouseenter', function (event) {
                    //         var geoObject = event.get('target');
                    //         myMap.hint.open(geoObject.geometry.getCoordinates(), geoObject.getPremise());
                    //     })
                    //     // Скрываем хинт при выходе курсора за пределы метки.
                    //     .add('mouseleave', function (event) {
                    //         myMap.hint.close(true);
                    //     });
                    // Добавляем коллекцию найденных геообъектов на карту.
                    // console.log(res.geoObjects.get(0).getPremise());
                    var objects = res.geoObjects;
                    $('[name="metro"]').val(splitLastAddress(objects.get(0).getAddressLine()));

                    // myMap.geoObjects.add(objects);
                    // Масштабируем карту на область видимости коллекции.
                    // myMap.setBounds(res.geoObjects.getBounds());
                });
            }

            function getDisctrict(coords) {
                $('[name="district"]').val('');

                ymaps.geocode(coords, {
                    kind: 'district',
                }).then(function (res) {
                    // console.log(replaceAddress(res.geoObjects.get(0).getAddressLine()));
                    $('[name="district"]').val(replaceAddress(res.geoObjects.get(0).getAddressLine()));
                });
            }

            function splitLastAddress(str) {
                var strArr = str.split(',');

                return strArr[strArr.length - 1];
            }

            function replaceAddress(str) {
                var strArr = str.split(','),
                    length = strArr.length,
                    returnStr = '';


                for (var i = 2; i < length; i++) {
                    returnStr = returnStr + strArr[i] + ',';
                }

                return returnStr.replace(/,$/,"");
            }
        }
    </script>

</html>
