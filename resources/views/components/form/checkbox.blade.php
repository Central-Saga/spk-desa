@props([
    'name',
    'label' => null,
    'value' => '1',
    'checked' => false,
    'help' => null,
])

<div class="mb-3 form-check">
    <input
        type="checkbox"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked(old($name, $checked))
        {{ $attributes->class([
            'form-check-input',
            'is-invalid' => $errors->has($name),
        ]) }}
    >
    @if ($label)
        <label for="{{ $name }}" class="form-check-label small">{{ $label }}</label>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror

    @if ($help && ! $errors->has($name))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
