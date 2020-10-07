@extends('layouts')

@section("content")
    <div class="row">
        <div class="col-md-12">
            @foreach ($errors as $error)
                <p><b>{{$error['name']}}</b> {{$error['text']}}</p>
            @endforeach

            <p>{{$msg}}</p>
        </div>
    </div>

    @parent
@overwrite
