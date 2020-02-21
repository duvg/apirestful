@component('mail::message')
# Hola {{$user->name}}

Gracias por registrate, verifica tu cuenta usando el siguiente enlace:

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Confirmar mi cuenta
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent