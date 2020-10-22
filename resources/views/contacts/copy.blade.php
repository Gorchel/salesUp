@extends('layouts')

@section("content")
<div class="container">
    <div class="row">
        <div class="col-lg-12 text-center" style="margin-top: 20px;">
            @if(!empty($contactEmails))
                <!-- Target -->
                    <textarea id="foo" class="form-control" rows="10">
                        {{implode(',',$contactEmails)}}
                    </textarea>
                <hr/>
                <!-- Trigger -->
                <button class="btn btn-info" id="copy-contacts" data-clipboard-target="#foo">Скопировать</button>
            @else
                <h1 class="text-center">В контактах сделки отсутствуют email!</h1>
            @endif
        </div>
    </div>
</div>
@parent
@overwrite

@section('js')
    @parent
    <script src="/js/clipboard.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script>
        var clipboard = new ClipboardJS('#copy-contacts');

        clipboard.on('success', function(e) {
            Swal.fire({
                title: 'Успешно!',
                text: 'Данные скопированы',
                icon: 'success',
                confirmButtonText: 'Ок'
            });

            e.clearSelection();
        });
    </script>
@overwrite

@section('css')
    @parent
@overwrite

