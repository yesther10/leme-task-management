@csrf
<div class="form-group">
    <x-adminlte-input name="title" id="title" label="Título" igroup-size="md" enable-old-support
        value="{{ old('title', $project->title ?? '') }}" required />
    @error('title')<span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    <x-adminlte-textarea name="description" id="description" label="Descrição" igroup-size="md">
        {{ old('description', $project->description ?? '') }}
    </x-adminlte-textarea>
    @error('description')<span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <x-adminlte-input type="date" name="start_date" id="start_date" label="Data de início" igroup-size="md"
            value="{{ old('start_date', isset($project) ? $project->start_date->format('Y-m-d') : '') }}" required />
        @error('start_date')<span class="text-danger">{{ $message }}</span>@enderror
    </div>

    <div class="form-group col-md-6">
        <x-adminlte-input type="date" name="due_date" id="due_date" label="Data de conclusão prevista"
            igroup-size="md" value="{{ old('due_date', isset($project) ? $project->due_date->format('Y-m-d') : '') }}"
            required />
        @error('due_date')<span class="text-danger">{{ $message }}</span>@enderror
    </div>
</div>

<div class="form-group">
    <label for="file_path">Arquivo anexado (PDF ou imagem)</label>
    <x-adminlte-input-file name="file_path" id="file_path" label="Selecionar arquivo" legend="Selecionar" />
    @if (!empty($project->file_path))
        <p class="mt-2">Arquivo atual:
            <a href="{{ Storage::url($project->file_path) }}" target="_blank">Ver</a>
        </p>
    @endif
    @error('file_path')<span class="text-danger">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    <label>Membros do Projeto</label>
    <select name="members[]" class="form-control select2" multiple="multiple" style="width: 100%;">
        @foreach($users as $user)
            <option value="{{ $user->id }}" 
                @if(isset($project) && $project->members->contains($user->id)) selected @endif>
                {{ $user->name }} ({{ $user->email }})
            </option>
        @endforeach
    </select>
</div>
