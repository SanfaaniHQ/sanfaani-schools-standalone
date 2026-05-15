@props([
    'label' => 'Data table',
])

<section class="ui-table-wrap" role="region" aria-label="{{ $label }}" tabindex="0">
    <table {{ $attributes->merge(['class' => 'enterprise-table']) }}>
        {{ $slot }}
    </table>
</section>
