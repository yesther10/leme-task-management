@csrf
<div class="form-group">
    <label for="title">Título</label>
    <x-adminlte-input name="title" id="title" label="Título" igroup-size="md" enable-old-support
        value="{{ old('title', $project->title ?? '') }}" required />
    @error('title')<span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    <label for="description">Descripción</label>
    <x-adminlte-textarea name="description" id="description" label="Descripción" igroup-size="md">
        {{ old('description', $project->description ?? '') }}
    </x-adminlte-textarea>
    @error('description')<span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="start_date">Fecha de Inicio</label>
        <x-adminlte-input type="date" name="start_date" id="start_date" label="Fecha de Inicio" igroup-size="md"
            value="{{ old('start_date', isset($project) ? $project->start_date->format('Y-m-d') : '') }}" required />
        @error('start_date')<span class="text-danger">{{ $message }}</span>@enderror
    </div>

    <div class="form-group col-md-6">
        <label for="due_date">Fecha Prevista de Conclusión</label>
        <x-adminlte-input type="date" name="due_date" id="due_date" label="Fecha Prevista de Conclusión"
            igroup-size="md" value="{{ old('due_date', isset($project) ? $project->due_date->format('Y-m-d') : '') }}"
            required />
        @error('due_date')<span class="text-danger">{{ $message }}</span>@enderror
    </div>
</div>

<div class="form-group">
    <label for="file_path">Archivo adjunto (PDF o Imagen)</label>
    <x-adminlte-input-file name="file_path" id="file_path" label="Seleccionar archivo" legend="Seleccionar" />
    @if (!empty($project->file_path))
        <p class="mt-2">Archivo actual:
            <a href="{{ Storage::url($project->file_path) }}" target="_blank">Ver</a>
        </p>
    @endif
    @error('file_path')<span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    <label>Miembros del Proyecto</label>
    <select name="members[]" class="form-control select2" multiple="multiple" style="width: 100%;">
        @foreach($users as $user)
            <option value="{{ $user->id }}" 
                @if(isset($project) && $project->members->contains($user->id)) selected @endif>
                {{ $user->name }} ({{ $user->email }})
            </option>
        @endforeach
    </select>
</div>
