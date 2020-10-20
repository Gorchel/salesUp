@extends('layouts')

@section("content")
        <form action="/weebhook_estate_get" id="submitForm">
            <div class="row" style="margin-bottom: 40px;"></div>
            <input type="hidden" name="token" value="{{$token}}">
            <input type="hidden" name="id" value="{{$id}}">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="streetCheck" name="street_check" value="1" checked="checked">
                        <label class="custom-control-label" for="streetCheck">Улица, Дом</label>
                    </div>
                    <input type="text" class="form-control input-sm" name="street" value="{{$address}}">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="typeCheck" name="type_check" value="1" checked="checked">
                        <label class="custom-control-label" for="typeCheck">По профилю компании</label>
                    </div>
                    <select name="type[]" id="type" class="form-control" multiple="multiple">
                        @foreach ($objectTypes as $key => $value)
                            <option value="{{$value}}" {{in_array($value, $profileCompanies) ? 'selected="selected"' : ''}}>{{$value}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="districtCheck" name="district_check" value="1" checked="checked">
                        <label class="custom-control-label" for="districtCheck">Район</label>
                    </div>
                    <input type="text" class="form-control input-sm" name="district" value="{{isset($districtArray[0]) ? $districtArray[0] : ''}}">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="metroCheck" name="metro_check" value="1" checked="checked">
                        <label class="custom-control-label" for="metroCheck">Метро</label>
                    </div>
                    <select name="metro[]" id="metro" class="form-control" multiple="multiple">
                        @foreach ($metroSelect as $key => $value)
                            <option value="{{$value}}" {{strpos($metro,mb_strtolower($value)) == true ? 'selected="selected"' : ''}}>{{$value}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="customCheckOne" name="footage_check" value="1" checked="checked">
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
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="customCheckTwo" name="budget_volume_check" value="1" checked="checked">
                                <label class="custom-control-label" for="customCheckTwo">Арендная ставка в месяц</label>
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
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="customCheckThree" name="budget_footage_check" value="1" checked="checked">
                                <label class="custom-control-label" for="customCheckThree">Арендная ставка за кв. м в месяц</label>
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
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="notCompanyCheck" name="disabled_company_check" value="1" checked="checked">
                        <label class="custom-control-label" for="notCompanyCheck">Не предлагать компаниям</label>
                    </div>
                    <input type="text" class="form-control input-sm" name="disabled_company" value="{{$disabledCompanies}}">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group text-center">
                    <input type="submit" class="btn btn-success" id="submit" value="Создать сделку">
                </div>
            </div>
        </form>
    @parent
@overwrite

@section('js')
    @parent

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/11.0.2/bootstrap-slider.min.js" integrity="sha512-f0VlzJbcEB6KiW8ZVtL+5HWPDyW1+nJEjguZ5IVnSQkvZbwBt2RfCBY0CBO1PsMAqxxrG4Di6TfsCPP3ZRwKpA==" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            var footageSlider = new Slider('#footage', {}).on('change', function (ev) {
                updateSliderInput('footage', ev.newValue);
            });

            var budgetVolumeSlider = new Slider('#budget_volume', {}).on('change', function (ev) {
                updateSliderInput('budget_volume', ev.newValue);
            });
            var budgetFootageSlider = new Slider('#budget_footage', {}).on('change', function (ev) {
                updateSliderInput('budget_footage', ev.newValue);
            });

            $('#type').select2({
                closeOnSelect: false
            });

            $('#metro').select2({
                closeOnSelect: false
            });

            updateSliderInput('footage',footageSlider.getValue());
            updateSliderInput('budget_volume',budgetVolumeSlider.getValue());
            updateSliderInput('budget_footage',budgetFootageSlider.getValue());

            $('body').on('change','.change_value', function() {
               var key = $(this).data('key'),
                   startVal = parseInt($('input[name="' + key + '_start_input"]').data('value')),
                   realVal = parseInt($('input[name="' + key + '_value_input"]').data('value')),
                   finishVal = parseInt($('input[name="' + key + '_finish_input"]').data('value'));

               if (key === 'footage') {
                   footageSlider.setValue([updateSlider(realVal, startVal), updateSlider(realVal, finishVal)]);
               } else if (key === 'budget_volume') {
                   budgetVolumeSlider.setValue([updateSlider(realVal, startVal), updateSlider(realVal, finishVal)]);
               } else {
                   budgetFootageSlider.setValue([updateSlider(realVal, startVal), updateSlider(realVal, finishVal)]);
               }
            });

            $("input[type=text]").keydown(function(event){
                if(event.keyCode == 13){
                    event.preventDefault();
                    return false;
                }
            });
        });

        function updateSlider(realVal, value)
        {
            return ((value - realVal) / realVal) * 100;
        }

        function updateSliderInput(key, valueArr)
        {
            var value = parseInt($('input[name="' + key + '_value_input"]').data('value'));

            $('input[name="' + key + '_start_input"]').val(numberWithSpaces(getPercent(value, valueArr[0])));
            $('input[name="' + key + '_finish_input"]').val(numberWithSpaces(getPercent(value, valueArr[1])));
        }

        function getPercent(value, percent) {
            return value + parseInt((value / 100) * parseInt(percent));
        }

        function numberWithSpaces(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
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
    </style>
@show
