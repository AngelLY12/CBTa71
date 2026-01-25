@extends('emails.layouts.base')

@section('title', 'Nuevo concepto de pago')

@section('header_title')
    Nuevo concepto de pago
@endsection

@section('greeting')
    Hola {{ $name }}
@endsection

@section('message_intro')
    Se ha creado un nuevo concepto de pago para ti con la siguiente información:
@endsection

@section('message_details')
    <p><strong>Concepto:</strong> {{ $conceptName }}</p>
    <p><strong>Monto:</strong> ${{ $amount }}</p>
    <p><strong>Fecha límite:</strong> {{ $endDate }}</p>
@endsection

@section('message_footer')
    Si tienes alguna duda sobre este concepto, por favor comunícate con la institución.
@endsection
