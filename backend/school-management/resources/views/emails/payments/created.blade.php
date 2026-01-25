@extends('emails.layouts.base')

@section('title', 'Confirmación de pago')

@section('header_title')
    Confirmación de pago
@endsection

@section('greeting')
    Hola {{ $recipientName }}
@endsection

@section('message_intro')
    Hemos recibido tu pago correctamente.
@endsection

@section('message_details')
    <p><strong>Concepto:</strong> {{ $conceptName }}</p>
    <p><strong>Monto:</strong> ${{ $amount }}</p>
    <p><strong>Fecha de pago:</strong> {{ $createdAt }}</p>
    <p><strong>Sesión de pago:</strong> {{ $stripeSessionId }}</p>
    <p>
        <strong>URL de la sesión:</strong>
        <a href="{{ $url }}" target="_blank">
            {{ $url }}
        </a>
    </p>
@endsection

@section('message_footer')
    Gracias por tu puntualidad. Te avisaremos cuando haya sido validado tu pago.
@endsection
