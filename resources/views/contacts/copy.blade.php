@extends('layouts')

@section("content")
<div class="container">
    <div class="row">
        <div class="col-lg-12 text-center" style="margin-top: 20px;">
            <!-- Target -->
            <input id="foo" value="https://github.com/zenorocha/clipboard.js.git" class="form-control">
            <hr/>
            <!-- Trigger -->
            <button class="btn btn-info" id="copy-contacts" data-clipboard-target="#foo">Скопировать</button>
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
