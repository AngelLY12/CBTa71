@extends('emails.layouts.base')

@section('title', 'Recuperar contraseña')

@section('header_title')
    Recuperar contraseña
@endsection

@section('greeting')
    Hola {{ $user->name . $user->last_name }}
@endsection

@section('message_intro')
    Para restablecer tu contraseña debes ingresar al siguiente enlace:
@endsection

@section('message_details')
    <p>
        <a href="{{ $resetUrl }}" target="_blank">
            Restablecer contraseña
        </a>
    </p>

    <p>
        Si no solicitaste restablecer la contraseña, ignora este mensaje.
    </p>
@endsection

@section('message_footer')
    Este enlace expirará en 60 minutos.
@endsection
