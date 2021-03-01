@extends('layouts')

@section("content")
    <div class="row">
        <div class="col-md-12">
            @if (isset($errors))
                @forelse ($errors as $error)
                    <p><b>{{$error['name']}}</b> {{$error['text']}}</p>
                @empty
                @endforelse
            @endif

            <p>{{$msg}}</p>
        </div>
        <div class="col-md-6">
            <a class="btn btn-sm btn-info" href="/weebhook_orders_filter?{{http_build_query($request)}}">Назад</a>
        </div>
    </div>

    @parent
@overwrite
