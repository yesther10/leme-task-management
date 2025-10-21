@extends('adminlte::page')

@section('title', 'Tareas')

@section('content_header')
    <h1>Tareas</h1>
@stop

@section('content')

    <a href="{{ route('tasks.create') }}" class="btn btn-success mb-3">Crear Tarea</a>
    <div class="row mb-3">
    <div class="col-md-3">
        <select id="filterStatus" class="form-control">
            <option value="">Todos los estados</option>
            @foreach(App\Enums\TaskStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select id="filterPriority" class="form-control">
            <option value="">Todas las prioridades</option>
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
                <th>Proyecto</th>
                <th>Archivos</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Vencimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(function () {
        $('#tasks-table').DataTable({
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
                        alert('Tarea marcada como completada.');
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