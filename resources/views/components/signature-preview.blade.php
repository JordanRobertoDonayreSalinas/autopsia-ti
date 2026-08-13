@props(['profesional', 'doc', 'cargo', 'fecha' => null])

<div {{ $attributes->merge(['class' => 'relative bg-teal-50 border border-teal-500/30 p-4 rounded-xl shadow-sm']) }} style="min-width: 200px; max-width: 250px;">
    <div class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 opacity-20">
        <img src="{{ url('images/logo_minsa_cc.png') }}" class="w-full h-auto grayscale" alt="MINSA">
    </div>
    <div class="pl-10">
        <div class="text-[9px] font-black text-teal-700 uppercase leading-none mb-1">Firma Digital</div>
        <div class="text-[8px] text-teal-900 leading-tight">
            Firmado digitalmente por:<br>
            <span class="font-bold text-[9px]">{{ strtoupper($profesional) }}</span><br>
            DNI: {{ $doc }}<br>
            Motivo: En señal de conformidad<br>
            Fecha: {{ $fecha ?? date('d/m/Y H:i') }}
        </div>
    </div>
</div>
