@extends('adminlte::page')

@section('title', 'Tarefas')

@section('content_header')
    <h1>Tarefas</h1>
@stop

@section('content')

    <a href="{{ route('tasks.create') }}" class="btn btn-success mb-3">Criar Tarefa</a>
    <div class="row mb-3">
    <div class="col-md-3">
        <select id="filterStatus" class="form-control">
            <option value="">Todos os estados</option>
            @foreach(App\Enums\TaskStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select id="filterPriority" class="form-control">
            <option value="">Todas as prioridades</option>
            @foreach(App\Enums\TaskPriority::cases() as $priority)
                <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
            @endforeach
        </select>
    </div>
</div>

    <table class="table table-bordered table-striped" id="tasks-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Projeto</th>
                <th>Arquivos</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Vencimiento</th>
                <th>Ações</th>
            </tr>
        </thead>
    </table>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(function () {
        $('#tasks-table').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            },
            processing: true,
            serverSide: true,
            ajax: {
                url :'{!! route('tasks.index') !!}',
                data: function (d) {
                    d.status = $('#filterStatus').val();
                    d.priority = $('#filterPriority').val();
                }
            },

            columns: [
                { data: 'id', name: 'id' },
                { data: 'title', name: 'title' },
                { data: 'project', name: 'project' },
                { data: 'files', name: 'files', orderable: false, searchable: false },
                { data: 'priority', name: 'priority' },
                { data: 'status', name: 'status' },
                { data: 'due_date', name: 'due_date' },
                { data: 'action', orderable: false, searchable: false },
            ]
        });

    });
  
    $(document).ready(function () {
        $('#tasks-table').on('click', '.btn-complete', function () {
            var taskId = $(this).data('id');
            if (confirm('¿Marcar esta tarea como completada?')) {
                $.ajax({
                    url: '/tasks/' + taskId + '/complete',
                    type: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('#tasks-table').DataTable().ajax.reload(null, false); // recarga tabla sin resetear pag
                        alert('Tarefa marcada como completada.');
                    },
                    error: function () {
                        alert('Error al actualizar la tarea.');
                    }
                });
            }
        });

        $('#filterStatus, #filterPriority').change(function () {
            $('#tasks-table').DataTable().ajax.reload();
        });
    });
</script>
@stop