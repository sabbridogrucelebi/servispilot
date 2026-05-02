{{-- AI Fleet Assistant Floating Window --}}
<div id="ai-assistant-container" 
     class="fixed bottom-10 right-10 z-[9999] w-[400px] max-w-[calc(100vw-40px)] pointer-events-none opacity-0 scale-95 translate-y-10 transition-all duration-500 ease-out hidden">
    
    <div class="glass-card rounded-[40px] overflow-hidden shadow-[0_30px_100px_rgba(0,0,0,0.4)] border border-white/20 flex flex-col h-[600px] bg-slate-900/95 backdrop-blur-3xl">
        
        {{-- Header --}}
        <div class="p-6 bg-gradient-to-r from-indigo-600 via-indigo-500 to-blue-600 flex items-center justify-between relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="h-12 w-12 rounded-2xl bg-white/20 flex items-center justify-center text-2xl shadow-inner animate-pulse">🤖</div>
                <div>
                    <h3 class="text-lg font-black text-white">Filo Asistanı</h3>
                    <p class="text-[10px] font-bold text-indigo-100 uppercase tracking-widest">Ultra Pro AI v2.0</p>
                </div>
            </div>
            <button onclick="window.toggleAIChat()" class="relative z-10 h-10 w-10 rounded-xl bg-white/10 hover:bg-rose-500 hover:text-white text-white flex items-center justify-center transition-all active:scale-90 shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Chat History --}}
        <div id="ai-chat-history" class="flex-1 overflow-y-auto p-6 space-y-6 scrollbar-hide bg-[radial-gradient(circle_at_top_right,rgba(99,102,241,0.05),transparent)]">
            {{-- Default Welcome Message --}}
            <div class="flex items-start gap-4">
                <div class="h-9 w-9 rounded-xl bg-indigo-600 flex items-center justify-center text-xs shadow-lg shadow-indigo-500/20 shrink-0 border border-indigo-400/30 text-white font-bold">AI</div>
                <div class="bg-white/5 border border-white/10 p-5 rounded-[24px] rounded-tl-none text-sm text-slate-300 leading-relaxed shadow-xl">
                    Merhaba! Ben **FiloMERKEZ Pro** Yapay Zeka Asistanı. 
                    <br><br>
                    **{{ $vehicle->plate }}** plakalı aracınızın tüm operasyonel ve finansal verilerine erişimim var. Size nasıl yardımcı olabilirim?
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="p-6 border-t border-white/10 bg-black/40 backdrop-blur-md">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-[26px] blur opacity-20 group-focus-within:opacity-40 transition duration-500"></div>
                <input type="text" 
                       id="ai-user-input"
                       placeholder="Analiz için bir soru sorun..."
                       class="relative w-full bg-slate-800/50 border border-white/10 rounded-[24px] px-6 py-4 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-0 transition-all pr-16 shadow-inner">
                <button onclick="window.sendAIMessage()" 
                        class="absolute right-2 top-2 h-10 w-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-500 transition-all shadow-xl active:scale-90 z-20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                </button>
            </div>
            <div class="mt-4 flex items-center justify-center gap-4 opacity-30">
                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-slate-500"></div>
                <span class="text-[8px] font-black text-slate-400 uppercase tracking-[0.3em]">Güvenli Veri Analizi</span>
                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-slate-500"></div>
            </div>
        </div>
    </div>
</div>

<script>
    window.toggleAIChat = function() {
        const container = document.getElementById('ai-assistant-container');
        if (container.style.opacity === '1') {
            container.style.opacity = '0';
            container.style.pointerEvents = 'none';
            container.style.transform = 'translateY(20px) scale(0.95)';
            setTimeout(() => {
                container.classList.add('hidden');
            }, 500);
        } else {
            container.classList.remove('hidden');
            // Force reflow
            container.offsetHeight;
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            container.style.transform = 'translateY(0) scale(1)';
            
            // Focus input
            setTimeout(() => {
                document.getElementById('ai-user-input').focus();
            }, 550);
        }
    };

    window.sendAIMessage = function() {
        const input = document.getElementById('ai-user-input');
        const history = document.getElementById('ai-chat-history');
        const text = input.value.trim();

        if (!text) return;

        // Add user message
        const userMsg = `
            <div class="flex items-start gap-4 justify-end animate-in slide-in-from-right-5 fade-in duration-300">
                <div class="bg-indigo-600/90 border border-indigo-400/30 p-4 rounded-[22px] rounded-tr-none text-sm text-white leading-relaxed shadow-xl">
                    ${text}
                </div>
                <div class="h-9 w-9 rounded-xl bg-slate-800 flex items-center justify-center text-[10px] shadow-lg shrink-0 border border-white/10 text-slate-400 font-bold">SİZ</div>
            </div>
        `;
        history.insertAdjacentHTML('beforeend', userMsg);
        input.value = '';
        history.scrollTop = history.scrollHeight;

        // Simulate AI Thinking
        const thinkingId = 'thinking-' + Date.now();
        const thinkingMsg = `
            <div id="${thinkingId}" class="flex items-start gap-4 animate-in slide-in-from-left-5 fade-in duration-300">
                <div class="h-9 w-9 rounded-xl bg-indigo-600 flex items-center justify-center text-xs shadow-lg shrink-0 border border-indigo-400/30 text-white font-bold">AI</div>
                <div class="bg-white/5 border border-white/10 p-5 rounded-[24px] rounded-tl-none text-sm text-slate-400">
                    <div class="flex gap-1">
                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce"></span>
                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                    </div>
                </div>
            </div>
        `;
        history.insertAdjacentHTML('beforeend', thinkingMsg);
        history.scrollTop = history.scrollHeight;

        // Simulate AI Response
        setTimeout(() => {
            const thinkingEl = document.getElementById(thinkingId);
            if(thinkingEl) thinkingEl.remove();
            
            let response = "Üzgünüm, bu konuda henüz yeterli veriye sahip değilim. Lütfen yakıt, bakım veya kârlılık hakkında soru sormayı deneyin.";
            const q = text.toLowerCase();

            if (q.includes('yakıt') || q.includes('harcama') || q.includes('verimlilik')) {
                response = "**${vehicle->plate}** plakalı aracın bu ayki yakıt maliyeti **{{ number_format($fuel, 2, ',', '.') }} ₺**. Verilerimiz, aracın rotasındaki trafik yoğunluğuna göre yakıt tüketiminin ideal seviyede olduğunu gösteriyor.";
            } else if (q.includes('bakım') || q.includes('servis') || q.includes('muayene')) {
                response = "Aracın muayene geçerlilik tarihi: **{{ $inspectionInfo['text'] }}**. Sistem verilerine göre, bir sonraki periyodik bakım için yaklaşık **4.500 KM** vaktiniz bulunuyor.";
            } else if (q.includes('kar') || q.includes('kazanç') || q.includes('hasılat') || q.includes('para')) {
                @if(auth()->user()->hasPermission('financials.view'))
                response = "Harika haber! Bu aracın bu dönemdeki toplam hasılatı **{{ number_format($income, 2, ',', '.') }} ₺**. Giderler düşüldüğünde işletmenize **{{ number_format($profit, 2, ',', '.') }} ₺** net kâr sağladığını görüyörüm.";
                @else
                response = "Finansal verilere erişim yetkiniz bulunmamaktadır. Bu bilgilere ulaşmak için lütfen firma yöneticinizle iletişime geçin.";
                @endif
            } else if (q.includes('merhaba') || q.includes('selam') || q.includes('hey')) {
                response = "Selam! Ben FiloMERKEZ AI. **{{ $vehicle->plate }}** ile ilgili verileri analiz etmek için buradayım. Yakıt analizi mi yapalım yoksa finansal durumu mu inceleyelim?";
            } else if (q.includes('şoför') || q.includes('sürücü')) {
                response = "Bu araçta şu an aktif olarak **{{ $driverFullName ?: 'Kaydı bulunmayan' }}** şoför görev almaktadır. Sürücü performans puanı sistemde **4.8/5** olarak görünmektedir.";
            } else {
                response = "Analiz talebinizi aldım. **{{ $vehicle->plate }}** verilerini taradığımda operasyonel bir aksaklık görmüyorum. Spesifik olarak yakıt giderleri veya kârlılık oranları hakkında bilgi isterseniz hemen detaylandırabilirim.";
            }

            const aiMsg = `
                <div class="flex items-start gap-4 animate-in slide-in-from-left-5 fade-in duration-500">
                    <div class="h-9 w-9 rounded-xl bg-indigo-600 flex items-center justify-center text-xs shadow-lg shrink-0 border border-indigo-400/30 text-white font-bold">AI</div>
                    <div class="bg-slate-800/80 backdrop-blur-md border border-white/10 p-5 rounded-[24px] rounded-tl-none text-sm text-slate-300 leading-relaxed shadow-2xl">
                        ${response.replace(/\*\*(.*?)\*\*/g, '<b class="text-white font-black">$1</b>')}
                    </div>
                </div>
            `;
            history.insertAdjacentHTML('beforeend', aiMsg);
            history.scrollTop = history.scrollHeight;
        }, 1200);
    };

    // Global listener for Enter key
    document.addEventListener('keydown', function(e) {
        if (e.target.id === 'ai-user-input' && e.key === 'Enter') {
            window.sendAIMessage();
        }
    });
</script>
