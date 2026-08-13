@extends('layouts.usuario')

@section('title', 'Gestionar usuarios')

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Gestionar usuarios</h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <span>Administracion</span>
        <span class="text-slate-300">•</span>
        <span>Gestionar Usuarios</span>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Alertas PHP --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-100 flex items-center gap-3 shadow-sm animate-fade-in-down">
                <div class="p-2 bg-emerald-100 rounded-full text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        {{-- TABLA DE USUARIOS --}}
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            
            {{-- Toolbar --}}
            <div class="p-6 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Listado de Usuarios</h2>
                </div>
                <a href="{{ route('admin.users.create') }}" class="group relative inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-semibold text-sm transition-all shadow-lg shadow-indigo-500/30 active:scale-95">
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo Usuario
                </a>
            </div>

            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[11px] uppercase tracking-wider font-bold border-b border-slate-100">
                            <th class="px-6 py-4">Nombres</th>
                            <th class="px-6 py-4">Usuario</th>
                            <th class="px-6 py-4 text-center">Rol</th>
                            <th class="px-6 py-4 text-center">Estado de cuenta</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                
                                {{-- Avatar y Nombre --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-blue-600 text-white flex items-center justify-center font-bold text-sm border-2 border-white shadow-sm">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-700 text-sm">
                                                {{ $user->apellido_paterno }} {{ $user->apellido_materno }} {{ $user->name }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 font-mono">ID: {{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Username --}}
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-500 font-mono bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                        {{ $user->username }}
                                    </span>
                                </td>

                                {{-- Rol --}}
                                <td class="px-6 py-4 text-center">
                                    @if($user->role === 'admin')
                                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black bg-violet-50 text-violet-600 border border-violet-100 tracking-tighter">ADMINISTRADOR</span>
                                    @elseif($user->role === 'operador')
                                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black bg-cyan-50 text-cyan-600 border border-cyan-100 tracking-tighter">OPERADOR</span>
                                    @elseif($user->role === 'visor_cronograma')
                                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black bg-teal-50 text-teal-600 border border-teal-100 tracking-tighter">VISOR CRONOGRAMA</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black bg-slate-100 text-slate-500 border border-slate-200 tracking-tighter">USUARIO</span>
                                    @endif
                                </td>

                                {{-- Switch AJAX Moderno --}}
                                <td class="px-6 py-4">
                                    <div class="flex justify-center">
                                        @if($user->id !== Auth::id())
                                            <label class="inline-flex items-center cursor-pointer group">
                                                <input type="checkbox" class="sr-only peer status-toggle" 
                                                       data-id="{{ $user->id }}" 
                                                       {{ $user->status === 'active' ? 'checked' : '' }}>
                                                
                                                <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-100 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                                
                                                <span class="ms-3 text-[10px] font-bold tracking-tighter transition-colors status-text {{ $user->status === 'active' ? 'text-indigo-600' : 'text-slate-400' }}">
                                                    {{ $user->status === 'active' ? 'ACTIVO' : 'BLOQUEADO' }}
                                                </span>
                                            </label>
                                        @else
                                            <span class="text-[10px] font-bold text-slate-300 italic uppercase">Tu cuenta</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Acciones --}}
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($users->hasPages())
                <div class="p-4 border-t border-slate-50 bg-slate-50/30 flex justify-center">{{ $users->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Notificación Toast --}}
    <div id="toast-success" class="fixed bottom-10 right-10 z-50 flex items-center p-4 text-emerald-600 bg-white rounded-2xl shadow-2xl border border-emerald-100 transform transition-all duration-500 translate-y-20 opacity-0 pointer-events-none">
        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
            <i data-lucide="check" class="w-5 h-5"></i>
        </div>
        <span class="text-xs font-bold uppercase tracking-tight text-slate-700" id="toast-message">Estado actualizado</span>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggles = document.querySelectorAll('.status-toggle');
            const toast = document.getElementById('toast-success');
            const toastMessage = document.getElementById('toast-message');

            function showToast(message) {
                toastMessage.textContent = message;
                toast.classList.remove('translate-y-20', 'opacity-0');
                setTimeout(() => {
                    toast.classList.add('translate-y-20', 'opacity-0');
                }, 3000);
            }

            toggles.forEach(toggle => {
                toggle.addEventListener('change', function () {
                    const userId = this.getAttribute('data-id');
                    const isChecked = this.checked;
                    const statusText = this.closest('label').querySelector('.status-text');

                    // Cambio visual inmediato
                    statusText.textContent = isChecked ? 'ACTIVO' : 'INACTIVO';
                    statusText.classList.toggle('text-indigo-600', isChecked);
                    statusText.classList.toggle('text-slate-400', !isChecked);

                    const url = `/admin/gestionar-usuarios/${userId}/toggle-status`;

                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Error de conexión');
                        return response.json();
                    })
                    .then(data => {
                        showToast(data.message.toUpperCase());
                    })
                    .catch(error => {
                        // Revertir en caso de error
                        this.checked = !isChecked;
                        statusText.textContent = !isChecked ? 'ACTIVO' : 'INACTIVO';
                        statusText.classList.toggle('text-indigo-600', !isChecked);
                        statusText.classList.toggle('text-slate-400', isChecked);
                        alert('No se pudo actualizar el estado del usuario.');
                    });
                });
            });
        });
    </script>
@endpush