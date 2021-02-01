@extends('layouts')

@section("content")
        <form action="/weebhook_orders_get" id="submitForm">
            <div class="row" style="margin-bottom: 40px;"></div>
            <input type="hidden" name="token" value="{{$token}}">
            <input type="hidden" name="id" value="{{$id}}">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input"  id="select-all-checkboxes" value="1">
                        <label class="custom-control-label" for="select-all-checkboxes">Все</label>
                    </div>
                    <select name="object_type" id="object_type" class="form-control">
{{--                        <option value="1" {{$objectType == 1 ? 'selected="selected"' : ''}}>Сдам</option>--}}
{{--                        <option value="2" {{$objectType == 2 ? 'selected="selected"' : ''}}>Продам</option>--}}
                        <option value="4" {{$objectType == 4 ? 'selected="selected"' : ''}}>Аренда</option>
                        <option value="3" {{$objectType == 3 ? 'selected="selected"' : ''}}>Продажа</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <select name="city_type" id="city_type" class="form-control">
                        <option value="2" {{$cityTypeId == 2 ? 'selected="selected"' : ''}}>Санкт-Петербург</option>
                        <option value="1" {{$cityTypeId == 1 ? 'selected="selected"' : ''}}>Москва</option>
                    </select>
                </div>
            </div>
{{--            Тип недвижимости--}}
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="type_of_property_check" name="type_of_property_check" value="1">
                        <label class="custom-control-label" for="type_of_property_check">По типу недвижимости</label>
                    </div>
                    <select name="type_of_property[]" id="type_of_property" class="form-control" multiple="multiple">
                        @foreach ($typeOfProperties as $key => $value)
                            <option value="{{$value}}" {{in_array($value, $typeOfPropertyObj) ? 'selected="selected"' : ''}}>{{$value}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
{{--            Улица, дом--}}
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="streetCheck" name="street_check" value="1">
                        <label class="custom-control-label" for="streetCheck">Улица, Дом</label>
                    </div>
                    <input type="text" class="form-control input-sm" name="street" value="{{$address}}">
                </div>
            </div>
            @if (in_array($objectType, [1,2]))
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="type_of_activity_check" name="type_of_activity_check" value="1">
                            <label class="custom-control-label" for="type_of_activity_check">По Виду деятельности</label>
                        </div>
                        <select name="type_of_activity[]" id="type_of_activity" class="form-control" multiple="multiple">
                            @foreach ($objectTypes as $key => $value)
                                <option value="{{$value}}" {{in_array($value, $profileCompanies) ? 'selected="selected"' : ''}}>{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="districtCheck" name="district_check" value="1">
                        <label class="custom-control-label" for="districtCheck">Район</label>
                    </div>
                    <input type="text" class="form-control input-sm" name="district" value="{{isset($districtArray[0]) ? $districtArray[0] : ''}}">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="regionCheck" name="region_check" value="1">
                        <label class="custom-control-label" for="regionCheck">Регион</label>
                    </div>
                    <input type="text" class="form-control input-sm" name="region" value="{{isset($regionArray[0]) ? $regionArray[0] : ''}}">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="metroCheck" name="metro_check" value="1">
                        <label class="custom-control-label" for="metroCheck">Метро</label>
                    </div>
                    <select name="metro[]" id="metro" class="form-control" multiple="multiple">
                        @foreach ($metroSelect as $key => $value)
                            <option value="{{$value}}" {{in_array($value, $metro) ? 'selected="selected"' : ''}}>{{$value}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if (!empty($objectSlider['footage']))
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="customCheckOne" name="footage_check" value="1"  checked="checked">
                                    <label class="custom-control-label" for="customCheckOne">По площади (кв/м)</label>
                                </div>
                                <input id="footage" type="text" name="footage" class="btm-color" value="" data-slider-min="-100" data-slider-max="100" data-slider-step="5" data-slider-value="[-20,20]" style="width: 80%;"/>&nbsp;<b> %</b>
                            </div>
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-4">
                                        <input type="text" class="form-control change_value" data-key="footage" name="footage_start_input" value="0">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="footage_value_input" data-value="{{intval($objectSlider['footage'])}}" value="{{number_format(intval($objectSlider['footage']),0,' ',' ')}}" readonly="readonly">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control change_value" data-key="footage" name="footage_finish_input" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row hidden">
                    <input id="footage" type="hidden" class="btm-color" value="" style="width: 80%;"/>&nbsp;<b></b>
                </div>
            @endif
            @if (!empty($objectSlider['budget_volume']))
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="customCheckTwo" name="budget_volume_check" value="1"  checked="checked">
                                    <label class="custom-control-label" for="customCheckTwo">По бюджету, руб.мес</label>
                                </div>
                                <input id="budget_volume" name="budget_volume" type="text" class="btm-color" value="" data-slider-min="-100" data-slider-max="100" data-slider-step="5" data-slider-value="[-20,20]" style="width: 80%;"/>&nbsp;<b> %</b>
                            </div>
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-4">
                                        <input type="text" class="form-control change_value" data-key="budget_volume" name="budget_volume_start_input" value="0">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="budget_volume_value_input" data-value="{{intval($objectSlider['budget_volume'])}}" value="{{number_format(intval($objectSlider['budget_volume']),0,' ',' ')}}" readonly="readonly">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control change_value" data-key="budget_volume" name="budget_volume_finish_input" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row hidden">
                    <input id="budget_volume" type="hidden" class="btm-color" value="" style="width: 80%;"/>&nbsp;<b></b>
                </div>
            @endif
            @if (in_array($objectType, [1,4]) && !empty($objectSlider['budget_footage']))
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="customCheckThree" name="budget_footage_check" value="1"  checked="checked">
                                    <label class="custom-control-label" for="customCheckThree">По бюджету за 1 кв/м в мес</label>
                                </div>
                                <input id="budget_footage" name="budget_footage" type="text" class="btm-color" value="" data-slider-min="-100" data-slider-max="100" data-slider-step="5" data-slider-value="[-20,20]" style="width: 80%;"/>&nbsp;<b> %</b>
                            </div>
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-4">
                                        <input type="text" class="form-control change_value" data-key="budget_footage" name="budget_footage_start_input" value="0">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="budget_footage_value_input" data-value="{{intval($objectSlider['budget_footage'])}}" value="{{number_format(intval($objectSlider['budget_footage']),0,' ',' ')}}" readonly="readonly">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control change_value" data-key="budget_footage" name="budget_footage_finish_input" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row hidden">
                    <input id="budget_footage" type="text" class="btm-color" value="" data-slider-min="-100" data-slider-max="100" data-slider-step="5" data-slider-value="[-20,20]" style="width: 80%;"/>&nbsp;<b> %</b>
                </div>
            @endif

            @if (in_array($objectType, [2,3]))
                <div class="row change_obg_type" data-type="2">
                    <div class="col-lg-10 offset-lg-1 form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="payback_period_check" name="payback_period_check" value="1" >
                                    <label class="custom-control-label" for="payback_period_check">Предполагаемый срок окупаемости в мес</label>
                                </div>
                                <input id="payback_period" name="payback_period" type="text" class="btm-color" value="" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="[12,24]" style="width: 80%;"/>&nbsp;<b>&nbsp;мес.</b>
                            </div>
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="col-4">
                                        <input type="text" class="form-control change_value" data-key="payback_period" name="payback_period_start_input" value="0">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="payback_period_value_input" data-value="16" value="16" readonly="readonly">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control change_value" data-key="payback_period" name="payback_period_finish_input" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row hidden">
                    <input id="payback_period" type="text" class="btm-color" value="" data-slider-min="-100" data-slider-max="100" data-slider-step="5" data-slider-value="[-20,20]" style="width: 80%;"/>&nbsp;<b> %</b>
                </div>
            @endif
            @if (in_array($objectType, [4]))
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_landlord_check" name="is_landlord_check" value="1">
                            <label class="custom-control-label" for="is_landlord_check">С Арендаторами</label>
                        </div>
                        <select name="is_landlord" id="is_landlord" class="form-control">
                            <option value="Да" {{in_array('Да',$isLandlord)  ? 'selected="selected"' : ''}}>Да</option>
                            <option value="Нет" {{in_array('Нет',$isLandlord) ? 'selected="selected"' : ''}}>Нет</option>
                        </select>
                    </div>
                </div>
            @endif
            @if (in_array($objectType, [1,2]))
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="client_type_check" name="client_type_check" value="1">
                            <label class="custom-control-label" for="client_type_check">Клиент</label>
                        </div>
                        <select name="client_type" id="client_type" class="form-control">
                            <option value="сетевой">сетевой</option>
                            <option value="не сетевой">не сетевой</option>
                        </select>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group text-center">
                    <input type="submit" class="btn btn-success" id="submit" value="Создать сделку">
                </div>
            </div>
        </form>
        <div class="row hidden" id="loader">
            <div class="col-lg-10 offset-lg-1 form-group text-center">
                <h1 сlass="text-center">Загрузка</h1>
            </div>
        </div>
    @parent
@overwrite

@section('js')
    @parent

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/11.0.2/bootstrap-slider.min.js" integrity="sha512-f0VlzJbcEB6KiW8ZVtL+5HWPDyW1+nJEjguZ5IVnSQkvZbwBt2RfCBY0CBO1PsMAqxxrG4Di6TfsCPP3ZRwKpA==" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // changeObjType();

            var footageSlider = new Slider('#footage', {}).on('change', function (ev) {
                updateSliderInput('footage', ev.newValue);
            });

            var budgetVolumeSlider = new Slider('#budget_volume', {}).on('change', function (ev) {
                updateSliderInput('budget_volume', ev.newValue);
            });

            var budgetFootageSlider = new Slider('#budget_footage', {}).on('change', function (ev) {
                updateSliderInput('budget_footage', ev.newValue);
            });

            var paybackPeriodSlider = new Slider('#payback_period', {}).on('change', function (ev) {
                updateSliderInputWithoutPercent('payback_period', ev.newValue);
            });

            $('#type_of_property').select2({
                closeOnSelect: false
            });

            $('#type_of_activity').select2({
                closeOnSelect: false
            });

            $('#metro').select2({
                closeOnSelect: false
            });

            updateSliderInput('footage',footageSlider.getValue());
            updateSliderInput('budget_volume',budgetVolumeSlider.getValue());
            updateSliderInput('budget_footage',budgetFootageSlider.getValue());
            updateSliderInputWithoutPercent('payback_period',paybackPeriodSlider.getValue());

            $('body').on('change','.change_value', function() {
               var key = $(this).data('key'),
                   startVal = parseInt($('input[name="' + key + '_start_input"]').val().split(' ').join('')),
                   realVal = parseInt($('input[name="' + key + '_value_input"]').data('value')),
                   finishVal = parseInt($('input[name="' + key + '_finish_input"]').val().split(' ').join(''));

               if (key === 'footage') {
                   footageSlider.setValue([updateSlider(realVal, startVal), updateSlider(realVal, finishVal)]);
               } else if (key === 'budget_volume') {
                   budgetVolumeSlider.setValue([updateSlider(realVal, startVal), updateSlider(realVal, finishVal)]);
               } else if (key === 'budget_footage') {
                   budgetFootageSlider.setValue([updateSlider(realVal, startVal), updateSlider(realVal, finishVal)]);
               } else {
                   paybackPeriodSlider.setValue([updateSliderWithoutPercent(startVal), updateSliderWithoutPercent(finishVal)]);
               }
            });

            $("input[type=text]").keydown(function(event){
                if(event.keyCode == 13){
                    event.preventDefault();
                    return false;
                }
            });

            $('body').on('change','select#object_type', function() {
                changeLocation();
            });

            $('body').on('change','select#city_type', function() {
                changeLocation();
            });

            $('body').on('change', '#select-all-checkboxes', function() {
                var value = $(this).prop('checked');

                $.each($('.custom-control-input'), function() {
                    $(this).prop('checked', value);
                });
            })

            $('#submitForm').on('submit', function(e) {
                e.preventDefault();

                $(this).hide();
                $('#loader').show();

                location.href = location.origin + '/weebhook_estate_get?' + $(this).serialize();
            })
        });

        function changeLocation()
        {
            location.href = location.href + '&object_type=' + $("select#object_type option:selected").val() + '&city_type=' + $("select#city_type option:selected").val();
        }

        function updateSlider(realVal, value)
        {
            return ((value - realVal) / realVal) * 100;
        }

        function updateSliderWithoutPercent(realVal)
        {
            return realVal;
        }

        function updateSliderInput(key, valueArr)
        {
            var value = parseInt($('input[name="' + key + '_value_input"]').data('value')),
                start_val = getPercent(value, valueArr[0]),
                finish_val = getPercent(value, valueArr[1]),
                start_input = $('input[name="' + key + '_start_input"]'),
                finish_input = $('input[name="' + key + '_finish_input"]');

            start_input.val(numberWithSpaces(start_val));
            start_input.data('value', start_val);
            finish_input.val(numberWithSpaces(finish_val));
            finish_input.data('value', finish_val);
        }

        function updateSliderInputWithoutPercent(key, valueArr)
        {
            var value = parseInt($('input[name="' + key + '_value_input"]').data('value')),
                start_val = valueArr[0],
                finish_val = valueArr[1],
                start_input = $('input[name="' + key + '_start_input"]'),
                finish_input = $('input[name="' + key + '_finish_input"]');

            start_input.val(numberWithSpaces(start_val));
            start_input.data('value', start_val);
            finish_input.val(numberWithSpaces(finish_val));
            finish_input.data('value', finish_val);
        }

        function getPercent(value, percent) {
            return value + parseInt((value / 100) * parseInt(percent));
        }

        function numberWithSpaces(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        }

        function changeObjType() {
            var obj_type = $("select#object_type option:selected").val();

            $.each($('.change_obg_type'), function() {
                var _this = $(this),
                    data_type = _this.data('type');

                if (data_type == obj_type) {
                    if (_this.hasClass('hidden')) {
                        _this.removeClass('hidden');

                        _this.find('input[type=checkbox]').prop( "checked", true );
                    }
                } else {
                    if (!_this.hasClass('hidden')) {
                        _this.addClass('hidden');

                        _this.find('input[type=checkbox]').prop( "checked", false );
                    }
                }
            });
        }
    </script>
@overwrite

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/11.0.2/css/bootstrap-slider.min.css" integrity="sha512-3q8fi8M0VS+X/3n64Ndpp6Bit7oXSiyCnzmlx6IDBLGlY5euFySyJ46RUlqIVs0DPCGOypqP8IRk/EyPvU28mQ==" crossorigin="anonymous" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .btm-color .slider-selection {
            background: #00e5ff;
        }

        .hidden {
            display: none;
        }
    </style>
@show
