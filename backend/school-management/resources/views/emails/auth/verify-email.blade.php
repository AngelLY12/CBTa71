@extends('emails.layouts.base')

@section('title', 'Verifica tu correo electrónico')

@section('header_title')
    Verifica tu correo electrónico
@endsection

@section('greeting')
    Hola {{ $user->name . $user->last_name }}
@endsection

@section('message_intro')
    Para completar el proceso de registro debes hacer la verificación de correo:
@endsection

@section('message_details')
    <p>
        <a href="{{ $verifyUrl }}" target="_blank">Verificar mi email</a>
    </p>
    <p>
        Si no creaste esta cuenta, o solicitaste la verificación ignora este mensaje.
    </p>
@endsection

@section('message_footer')
    Este enlace expirará en 60 minutos.
@endsection
