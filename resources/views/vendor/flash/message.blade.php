{{-- $messages — из FlashBag (ViewServiceProvider): оба канала flash --}}
@foreach ($messages as $message)
  <div class="alert alert-{{ $message['level'] === 'error' ? 'danger' : $message['level'] }}" role="alert">
    {{ $message['message'] }}
  </div>
@endforeach
