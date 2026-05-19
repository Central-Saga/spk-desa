@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => 4,
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

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'form-control',
            'is-invalid' => $errors->has($name),
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

    @if ($help && ! $errors->has($name))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
