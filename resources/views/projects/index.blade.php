@extends('adminlte::page')

@section('title', 'Projetos')

@section('content_header')
    <h1>Projetos</h1>
@stop

@section('content')
    <a href="{{ route('projects.create') }}" class="btn btn-success mb-3">Criar Projeto</a>
    <table class="table table-bordered table-striped" id="projects-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Responsável</th>
                <th>Membros</th>
                <th>Data Inicio</th>
                <th>Data Fin</th>
                <th>Arquivo</th>                
                <th>Ações</th>
            </tr>
        </thead>
    </table>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@stop

@section('plugins.Datatables', true)
@section('js')
{{-- <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script> --}}
<script>
    $(function () {
        $('#projects-table').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            },
            processing: true,
            serverSide: true,
            ajax: '{!! route('projects.index') !!}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'title', name: 'title' },
                { data: 'owner', name: 'owner' },
                { data: 'members_count', name: 'members_count', searchable: false },
                { data: 'start_date', name: 'start_date' },
                { data: 'due_date', name: 'due_date' },
                { data: 'attachment_link', name: 'attachment_link', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });
    });
</script>
@stop
