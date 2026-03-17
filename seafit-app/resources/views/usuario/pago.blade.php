@extends('moldes.inicio')

@section('titulo', 'Gestión de Pago - SeaFit')

@section('contenido')
    <div class="main-container-perfil">
        {{-- BARRA LATERAL --}}
        <aside class="sidebar-perfil">
            <h2 class="sidebar-titulo">Panel de Socio</h2>
            <nav class="sidebar-nav">
                <a href="{{ route('perfil') }}" class="nav-item">
                    <span class="material-symbols-outlined">person</span> Mi Perfil
                </a>
                <a href="{{ route('mis.reservas') }}" class="nav-item">
                    <span class="material-symbols-outlined">calendar_month</span> Mis Reservas
                </a>
                <a href="{{ route('pago.gestion') }}" class="nav-item active">
                    <span class="material-symbols-outlined">payments</span> Gestión de Pago
                </a>
                <a href="{{ route('configuracion') }}" class="nav-item">
                    <span class="material-symbols-outlined">settings</span> Configuración
                </a>
            </nav>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="content-perfil">
            <header class="header-bienvenida">
                <h1>Gestión de Pago</h1>

                {{-- Mensajes Dinámicos (Éxito o Info) --}}
                @if(session('info'))
                    <div
                        style="background: #fffbeb; color: #92400e; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #fde68a; font-weight: 500;">
                        ⚠️ {{ session('info') }}
                    </div>
                @endif
                @if(session('success'))
                    <div
                        style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #bbf7d0; font-weight: 500;">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                <p>Administra tu suscripción, métodos de pago y revisa tu historial de facturas.</p>
            </header>

            {{-- RESUMEN DE FACTURACIÓN --}}
            <section class="seccion-blanca">
                <div class="seccion-header">
                    <h3>Resumen de Facturación</h3>
                </div>

                @if($user->tarifa != 'cancelada')
                    <div
                        style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin: 0; color: #0A1931; font-weight: 800;">Próximo cobro (Membresía Total)</h4>
                                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 0.9rem;">Se cargará automáticamente a tu
                                    método principal.</p>
                            </div>
                            <div style="text-align: right;">
                                <span style="display: block; font-size: 1.5rem; font-weight: 900; color: #1A3878;">
                                    {{ $user->tarifa == 'anual' ? '250,00€' : ($user->tarifa == 'trimestral' ? '75,00€' : '29,99€') }}
                                </span>
                                <span style="font-size: 0.75rem; color: #94a3b8;">Fecha: 24/12/2026</span>
                            </div>
                        </div>
                        <div
                            style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; color: #64748b;">Plan actual: <strong>Acceso Total
                                    {{ ucfirst($user->tarifa) }}</strong></span>

                            {{-- Formulario de Cancelación --}}
                            <form action="{{ route('plan.cancelar') }}" method="POST"
                                onsubmit="return confirm('¿Estás seguro de que deseas cancelar tu suscripción?')">
                                @csrf
                                <button type="submit"
                                    style="background:none; border:none; color:#1A3878; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:5px; padding:0; font-family: inherit; font-size: 0.85rem;">
                                    <span class="material-symbols-outlined" style="font-size:18px;">cancel</span> Cancelar
                                    suscripción
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div
                        style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 20px; text-align: center;">
                        <span class="material-symbols-outlined" style="font-size: 40px; color: #ef4444;">cancel</span>
                        <h4 style="margin: 10px 0 0 0; color: #991b1b;">Suscripción Inactiva</h4>
                        <p style="color: #b91c1c;">Has cancelado tu plan. Ya no se te cobrará nada más.</p>
                    </div>
                @endif
            </section>

            {{-- MÉTODOS DE PAGO GUARDADOS --}}
            <section class="seccion-blanca">
                <div class="seccion-header">
                    <h3>Métodos de Pago Guardados</h3>
                </div>

                {{-- Tarjeta Principal --}}
                <div
                    style="border: 2px solid #86efac; background: #f0fdf4; border-radius: 12px; padding: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="material-symbols-outlined" style="font-size: 32px; color: #0A1931;">credit_card</span>
                        <div>
                            <h4 style="margin: 0; color: #0A1931;">Visa **** 4242</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: #166534;">Principal | Exp. 12/28</p>
                        </div>
                    </div>
                    <div>
                        <span style="color: #22c55e; font-weight: 700; font-size: 0.8rem; margin-right: 15px;">Método
                            Principal</span>
                    </div>
                </div>

                {{-- Segundo Método (PayPal) --}}
                <div
                    style="border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 12px; padding: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="material-symbols-outlined"
                            style="font-size: 32px; color: #0A1931;">account_balance_wallet</span>
                        <div>
                            <h4 style="margin: 0; color: #0A1931;">PayPal ({{ $user->email }})</h4>
                            <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Método Secundario</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 15px;">
                        {{-- Formulario Establecer Principal --}}
                        <form action="{{ route('pago.principal') }}" method="POST">
                            @csrf
                            <button type="submit"
                                style="color: #1A3878; background: none; border: none; font-weight: 700; font-size: 0.8rem; cursor: pointer;">Establecer
                                Principal</button>
                        </form>

                        {{-- Formulario Eliminar --}}
                        <form action="{{ route('pago.eliminar') }}" method="POST"
                            onsubmit="return confirm('¿Seguro que quieres borrar este método de pago?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                style="color: #ef4444; background: none; border: none; font-weight: 700; font-size: 0.8rem; cursor: pointer;">Eliminar</button>
                        </form>
                    </div>
                </div>

                <a href="{{ route('pago.nuevo') }}"
                    style="display: flex; align-items: center; gap: 8px; color: #1A3878; font-weight: 700; text-decoration: none; font-size: 0.9rem;">
                    <span class="material-symbols-outlined">add_circle</span> Añadir Nuevo Método de Pago
                </a>
            </section>

            {{-- HISTORIAL DE FACTURAS --}}
            <section class="seccion-blanca">
                <div class="seccion-header"
                    style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 15px;">
                    <h3>Historial de Facturas</h3>
                </div>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 style="margin: 0; color: #0A1931; font-weight: 700;">Factura #SF202611</h4>
                            <p style="margin: 2px 0 0 0; color: #64748b; font-size: 0.8rem;">17 Mar 2026 | Acceso Total
                                {{ ucfirst($user->tarifa) }}</p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <span
                                style="font-weight: 900; color: #0A1931;">{{ $user->tarifa == 'anual' ? '250,00€' : '29,99€' }}</span>

                            <a href="{{ route('factura.descargar', 'SF202611') }}"
                                style="color: #1A3878; font-weight: 700; font-size: 0.8rem; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                PDF <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
@endsection