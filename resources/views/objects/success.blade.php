@extends('layouts')

@section("content")
    <div class="row text-center">
        <div class="col-lg-12">
            <b>Сделка: </b><span>№{{$deal['attributes']['number']}} создана.</span>
        </div>
        <div class="col-lg-12">
            <b>Объект недвижимости: </b><span>{{$object['attributes']['name']}}</span>
        </div>
        <div class="col-lg-12">
            <b>Найдено: </b><span>{{$ordersCount}} заявок</span>
        </div>
    </div>
    @parent
@overwrite

@section('js')
    @parent
@overwrite

@section('css')
    @parent
@show
