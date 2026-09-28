@props(['label' => 'Empty', 'message' => 'No data.'])

<section class="status-state empty-state" role="status">
    <span class="status-state-label">{{ $label }}</span>
    <span class="status-state-mark" aria-hidden="true"></span>
    <p>{{ $message }}</p>
</section>
