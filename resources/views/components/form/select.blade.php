@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => '— Pilih —',
    'required' => false,
    'help' => null,
])

<div class="mb-3">
    @if ($label)
        <label for="{{ $name }}" class="form-label small fw-medium">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'form-select',
            'is-invalid' => $errors->has($name),
        ]) }}
    >
        @if ($placeholder !== false)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected((string) old($name, $value) === (string) $optValue)>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

    @if ($help && ! $errors->has($name))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
