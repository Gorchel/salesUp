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
    </div>

    @parent
@overwrite
