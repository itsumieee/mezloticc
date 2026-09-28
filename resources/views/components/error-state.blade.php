@props(['label' => 'Notice', 'message' => 'Something went wrong.'])

<section class="status-state error-state" role="alert">
    <span class="status-state-label">{{ $label }}</span>
    <p>{{ $message }}</p>
</section>
