Hola {{ $user->name }}
Gracias por registrate, verifica tu cuenta usando el siguiente enlace:

{{ route('verify', $user->verification_token) }}