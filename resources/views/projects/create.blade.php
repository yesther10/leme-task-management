@extends('adminlte::page')

@section('title', 'Crear Proyecto')

@section('content_header')
    <h1>Crear Proyecto</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
            @include('projects._form')
            <button type="submit" class="btn btn-primary">Crear</button>
        </form>
    </div>
</div>
@stop

@section('plugins.BsCustomFileInput', true)
@section('plugins.Select2', true)
@section('js')
<script>
$(function () {
    $('.select2').select2();
});
</script>
@endsection
