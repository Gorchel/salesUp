@extends('layouts')

@section("content")
        <form action="/weebhook_estate_get">
            <div class="row" style="margin-bottom: 40px;"></div>
            <input type="hidden" name="token" value="{{$token}}">
            <input type="hidden" name="id" value="{{$id}}">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group text-center">
                    <label for="">По профилю компании</label><br/>
                    <select name="type[]" id="type" class="form-control" multiple="multiple">
                        @foreach ($objectTypes as $key => $value)
                            <option value="{{$key}}">{{$value}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group text-center">
                    <label for="">По площади (кв/м)</label><br/>
                    <b>oт %</b>&nbsp;<input id="footage" type="text" name="footage" class="btm-color" value="" data-slider-min="-100" data-slider-max="100" data-slider-step="5" data-slider-value="[-20,20]"/>&nbsp;<b>до %</b>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group text-center">
                    <label for="">Арендная ставка в месяц</label><br/>
                    <b>oт %</b>&nbsp;<input id="budget_volume" name="budget_volume" type="text" class="btm-color" value="" data-slider-min="-100" data-slider-max="100" data-slider-step="5" data-slider-value="[-20,20]"/>&nbsp;<b>до %</b>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group text-center">
                    <label for="">Арендная ставка за кв. м в месяц</label><br/>
                    <b>oт %</b>&nbsp;<input id="budget_footage" name="budget_footage" type="text" class="btm-color" value="" data-slider-min="-100" data-slider-max="100" data-slider-step="5" data-slider-value="[-20,20]"/>&nbsp;<b>до %</b>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group text-center">
                    <input type="submit" class="btn btn-success" value="Создать сделку">
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
            var footageSlider = new Slider('#footage', {});
            var budgetVolumeSlider = new Slider('#budget_volume', {});
            var budgetFootageSlider = new Slider('#budget_footage', {});
            $('#type').select2();
        });
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
