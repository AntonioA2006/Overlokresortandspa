@extends('layouts.app')

@section('title', 'Recepción')

@section('content')
    <section class="section">
        <div class="container stack">
            <div class="section__header">
                <h1 class="section__title">Panel de recepción</h1>
                <p class="section__subtitle">Escaneo de QR, verificación de identidad y entrega de habitación.</p>
            </div>

            <div class="card">
                <div class="card__body stack">
                    <p class="card__text">Ingresa el token del QR para localizar una reservación.</p>
                    <div class="form-group">
                        <label class="form-label" for="reception-token">Token de reservación</label>
                        <input
                            id="reception-token"
                            class="form-input"
                            type="text"
                            data-reception-token-input
                            placeholder="Pega o escanea el token seguro"
                        >
                    </div>
                    <div class="reception-toolbar">
                        <button type="button" class="btn btn--primary" data-reception-lookup>
                            Buscar reservación
                        </button>
                        <a href="{{ route('reception.scan') }}" class="btn btn--ghost">Modo escaneo</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
