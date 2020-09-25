@extends('layouts')

@section("content")
        <form action="/weebhook_estate_filter" method="POST">
            <div class="row" style="margin-bottom: 40px;"></div>
            <input type="hidden" name="token" value="{{$token}}">
            <input type="hidden" name="id" value="{{$id}}">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group text-center">
                    <label for="">По площади</label>
                    <b>-20%</b>&nbsp;<input id="area" type="text" class="btm-color" value="" data-slider-min="-20" data-slider-max="20" data-slider-step="1" data-slider-value="[-10,10]"/>&nbsp;<b>20%</b>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 offset-lg-1 form-group text-center">
                    <label for="">По бюджету</label>
                    <b>-20%</b>&nbsp;<input id="budget" type="text" class="btm-color" value="" data-slider-min="-20" data-slider-max="20" data-slider-step="1" data-slider-value="[-10,10]"/>&nbsp;<b>20%</b>
                </div>
            </div>
        </form>
    @parent
@overwrite

@section('js')
    @parent

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/11.0.2/bootstrap-slider.min.js" integrity="sha512-f0VlzJbcEB6KiW8ZVtL+5HWPDyW1+nJEjguZ5IVnSQkvZbwBt2RfCBY0CBO1PsMAqxxrG4Di6TfsCPP3ZRwKpA==" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            var areaSlider = new Slider('#area', {});
            var budgetSlider = new Slider('#budget', {});
        });
    </script>
@overwrite

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/11.0.2/css/bootstrap-slider.min.css" integrity="sha512-3q8fi8M0VS+X/3n64Ndpp6Bit7oXSiyCnzmlx6IDBLGlY5euFySyJ46RUlqIVs0DPCGOypqP8IRk/EyPvU28mQ==" crossorigin="anonymous" />

    <style>
        .btm-color .slider-selection {
            background: #00e5ff;
        }
    </style>
@show
