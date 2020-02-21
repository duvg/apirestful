@component('mail::message')
# Hola {{$user->name}}

Has cambiado tu correo electronico, porfavor verificalo usando el siguiente enlace:

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Confirmar mi cuenta
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent