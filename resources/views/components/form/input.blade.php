@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
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

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'form-control',
            'is-invalid' => $errors->has($name),
        ]) }}
    >

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

    @if ($help && ! $errors->has($name))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
