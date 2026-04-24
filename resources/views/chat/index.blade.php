@extends('layouts.app')

@section('title', 'PilotChat')
@section('subtitle', 'Kurum İçi İletişim')

@section('content')

<style>
    /* WhatsApp Web Custom Styles */
    .wa-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .wa-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .wa-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(11,20,26,.2);
        border-radius: 4px;
    }
    .wa-scroll:hover::-webkit-scrollbar-thumb {
        background-color: rgba(11,20,26,.3);
    }
    
    /* WhatsApp Background Doodle */
    .wa-bg {
        background-color: #efeae2;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M10 10h10v10H10zM30 30h10v10H30zM50 50h10v10H50zM70 70h10v10H70z' fill='%23000000' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
    }
    
    .wa-bubble-mine {
        background-color: #d9fdd3;
        border-radius: 8px 0 8px 8px;
        box-shadow: 0 1px 0.5px rgba(11,20,26,.13);
    }
    .wa-bubble-mine::before {
        content: "";
        position: absolute;
        top: 0;
        right: -8px;
        width: 8px;
        height: 13px;
        background: linear-gradient(to bottom right, #d9fdd3 50%, transparent 50%);
    }

    .wa-bubble-other {
        background-color: #ffffff;
        border-radius: 0 8px 8px 8px;
        box-shadow: 0 1px 0.5px rgba(11,20,26,.13);
    }
    .wa-bubble-other::before {
        content: "";
        position: absolute;
        top: 0;
        left: -8px;
        width: 8px;
        height: 13px;
        background: linear-gradient(to bottom left, #ffffff 50%, transparent 50%);
    }

    /* Colors */
    .wa-bg-panel { background-color: #f0f2f5; }
    .wa-bg-white { background-color: #ffffff; }
    .wa-text-primary { color: #111b21; }
    .wa-text-secondary { color: #667781; }
    .wa-text-icon { color: #54656f; }
    .wa-border { border-color: #d1d7db; }
    .wa-unread { background-color: #00a884; }
    .wa-hover:hover { background-color: #f5f6f6; }
</style>

<div class="h-[calc(100vh-140px)] flex bg-white overflow-hidden shadow-sm border border-slate-200" style="border-radius: 0;" x-data="chatApp()" x-init="init()">
    
    {{-- SOL PANEL --}}
    <div class="w-full md:w-[400px] flex-shrink-0 border-r wa-border flex flex-col wa-bg-white" :class="{ 'hidden md:flex': activeChat }">
        
        {{-- Header --}}
        <div class="h-[59px] px-4 wa-bg-panel flex items-center justify-between shrink-0">
            <div class="w-10 h-10 rounded-full bg-slate-300 overflow-hidden flex items-center justify-center text-white font-bold text-lg">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="flex items-center gap-4 text-[#54656f]">
                <button title="Topluluklar">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12 4a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm14-2a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"></path></svg>
                </button>
                <button title="Durum">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12 20.664a9.163 9.163 0 0 1-6.521-2.702.977.977 0 0 1 1.381-1.381 7.269 7.269 0 0 0 10.024.244.977.977 0 0 1 1.313 1.445A9.192 9.192 0 0 1 12 20.664zm7.965-6.112a.977.977 0 0 1-.944-1.229 7.26 7.26 0 0 0-4.8-8.804.977.977 0 0 1 .594-1.86 9.212 9.212 0 0 1 6.092 11.169.976.976 0 0 1-.942.724zm-16.025-.39a.977.977 0 0 1-.753-1.587 9.183 9.183 0 0 1 6.112-6.112.977.977 0 1 1 .595 1.86 7.23 7.23 0 0 0-4.8 4.8.977.977 0 0 1-1.154.639z"></path></svg>
                </button>
                <button title="Yeni Grup" @click="openGroupModal = true">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M19.005 3.175H4.674C3.642 3.175 3 3.789 3 4.821V21.02l3.544-3.514h12.461c1.033 0 2.064-1.06 2.064-2.093V4.821c-.001-1.032-1.032-1.646-2.064-1.646zm-4.989 9.869H7.041V11.1h6.975v1.944zm3-4H7.041V7.1h9.975v1.944z"></path></svg>
                </button>
                <button title="Menü">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12 7a2 2 0 1 0-.001-4.001A2 2 0 0 0 12 7zm0 2a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 9zm0 6a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 15z"></path></svg>
                </button>
            </div>
        </div>

        {{-- Arama --}}
        <div class="h-[49px] border-b wa-border flex items-center px-3 gap-2 shrink-0 bg-white">
            <div class="flex-1 bg-[#f0f2f5] rounded-lg flex items-center h-[35px] px-3">
                <button class="wa-text-icon shrink-0">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M15.009 13.805h-.636l-.22-.219a5.184 5.184 0 0 0 1.256-3.386 5.207 5.207 0 1 0-5.207 5.208 5.183 5.183 0 0 0 3.385-1.255l.221.22v.635l4.004 3.999 1.194-1.195-3.997-4.007zm-4.808 0a3.605 3.605 0 1 1 0-7.21 3.605 3.605 0 0 1 0 7.21z"></path></svg>
                </button>
                <input type="text" x-model="searchQuery" placeholder="Aratın veya yeni sohbet başlatın" class="bg-transparent border-none focus:ring-0 w-full text-sm wa-text-primary px-4 placeholder-[#8696a0]">
            </div>
            <button class="wa-text-icon shrink-0">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M10 18.1h4v-2h-4v2zm-7-12v2h18v-2H3zm3 7h12v-2H6v2z"></path></svg>
            </button>
        </div>

        {{-- Liste --}}
        <div class="flex-1 overflow-y-auto wa-scroll bg-white">
            <template x-for="conv in filteredConversations" :key="conv.type + '_' + conv.id">
                <div @click="selectChat(conv)" class="flex items-center cursor-pointer wa-hover" :class="{ 'bg-[#f0f2f5]': activeChat && activeChat.id === conv.id && activeChat.type === conv.type }">
                    <div class="px-3 py-3 shrink-0">
                        <div class="w-[49px] h-[49px] rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-500 overflow-hidden">
                            <span x-show="conv.type === 'direct'" x-text="conv.name.substring(0, 1)"></span>
                            <svg x-show="conv.type === 'group'" viewBox="0 0 24 24" width="28" height="28" fill="currentColor" class="opacity-50"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0 pr-4 py-3 border-b wa-border h-full flex flex-col justify-center">
                        <div class="flex justify-between items-center mb-0.5">
                            <span class="text-[17px] wa-text-primary truncate" x-text="conv.name"></span>
                            <span class="text-xs" :class="conv.unread_count > 0 ? 'text-[#00a884]' : 'wa-text-secondary'" x-text="conv.last_message_time || ''"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[14px] wa-text-secondary truncate pr-2" x-text="conv.last_message || '...'"></span>
                            <div x-show="conv.unread_count > 0" class="shrink-0 wa-unread text-white text-[10px] font-bold min-w-[20px] h-[20px] px-1 rounded-full flex items-center justify-center" x-text="conv.unread_count"></div>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="filteredConversations.length === 0" class="p-8 text-center text-sm wa-text-secondary">
                Sohbet bulunamadı.
            </div>
        </div>
    </div>

    {{-- SAĞ PANEL --}}
    <div class="flex-1 flex flex-col relative" :class="{ 'hidden md:flex': !activeChat }">
        
        {{-- Boş Durum (Sohbet seçilmediğinde) --}}
        <div x-show="!activeChat" class="absolute inset-0 flex flex-col items-center justify-center text-center p-8 wa-bg-panel border-b-8 border-[#00a884] z-10">
            <div class="w-[320px] h-[200px] mb-8 opacity-40">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.5" class="w-full h-full"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            </div>
            <h1 class="text-[32px] font-light text-[#41525d] mb-4">PilotChat Web</h1>
            <p class="text-[14px] text-[#667781] max-w-md leading-6">Aynı anda bilgisayarınızdan veya telefonunuzdan mesaj gönderip alın.<br>Güvenli kurum içi iletişim.</p>
            <div class="mt-8 flex items-center gap-2 text-[#8696a0] text-sm">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"></path></svg>
                Uçtan uca şifrelenmiştir.
            </div>
        </div>

        {{-- Sohbet Header --}}
        <div x-show="activeChat" class="h-[59px] px-4 wa-bg-panel flex items-center justify-between shrink-0 z-20 border-b wa-border">
            <div class="flex items-center gap-4 cursor-pointer">
                <button @click="activeChat = null" class="md:hidden wa-text-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"></path></svg>
                </button>
                <div class="w-10 h-10 rounded-full bg-slate-300 overflow-hidden flex items-center justify-center text-white font-bold text-lg">
                    <span x-show="activeChat?.type === 'direct'" x-text="activeChat?.name?.substring(0, 1)"></span>
                    <svg x-show="activeChat?.type === 'group'" viewBox="0 0 24 24" width="24" height="24" fill="currentColor" class="opacity-50"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
                </div>
                <div>
                    <h2 class="text-[16px] wa-text-primary" x-text="activeChat?.name"></h2>
                    <p class="text-[13px] wa-text-secondary truncate" style="max-width:300px" x-show="activeChat?.type === 'group'" x-text="activeChat?.participants"></p>
                    <p class="text-[13px] wa-text-secondary" x-show="activeChat?.type === 'direct' && activeChat?.is_active">çevrimiçi</p>
                </div>
            </div>
            <div class="flex items-center gap-4 wa-text-icon">
                <button title="Ara"><svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M15.009 13.805h-.636l-.22-.219a5.184 5.184 0 0 0 1.256-3.386 5.207 5.207 0 1 0-5.207 5.208 5.183 5.183 0 0 0 3.385-1.255l.221.22v.635l4.004 3.999 1.194-1.195-3.997-4.007zm-4.808 0a3.605 3.605 0 1 1 0-7.21 3.605 3.605 0 0 1 0 7.21z"></path></svg></button>
                <button title="Menü"><svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12 7a2 2 0 1 0-.001-4.001A2 2 0 0 0 12 7zm0 2a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 9zm0 6a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 15z"></path></svg></button>
            </div>
        </div>

        {{-- Mesajlar --}}
        <div class="flex-1 overflow-y-auto wa-scroll wa-bg p-[5%] flex flex-col gap-[2px]" id="messages-container" x-ref="messagesContainer">
            <template x-for="(msg, index) in messages" :key="msg.id">
                <div class="flex w-full mb-1" :class="msg.is_mine ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[85%] md:max-w-[65%] relative px-2 py-[6px]" :class="msg.is_mine ? 'wa-bubble-mine' : 'wa-bubble-other'" style="padding-bottom: 20px;">
                        
                        <div x-show="activeChat?.type === 'group' && !msg.is_mine" class="text-[13px] font-medium text-[#c0316e] mb-1 leading-4" x-text="msg.sender_name"></div>
                        
                        <span class="text-[14px] text-[#111b21] leading-relaxed whitespace-pre-wrap word-break" x-text="msg.body" style="word-break: break-word;"></span>
                        
                        <div class="absolute right-2 bottom-1 flex items-center gap-1">
                            <span class="text-[11px] text-[#667781]" x-text="msg.time"></span>
                            <span x-show="msg.is_mine">
                                <svg x-show="msg.is_read" viewBox="0 0 16 15" width="16" height="15" fill="#53bdeb"><path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path></svg>
                                <svg x-show="!msg.is_read" viewBox="0 0 16 15" width="16" height="15" fill="#8696a0"><path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path></svg>
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Mesaj Yazma Alanı --}}
        <div x-show="activeChat" class="min-h-[62px] px-4 py-[10px] wa-bg-panel flex items-end gap-2 shrink-0 z-20">
            <button class="wa-text-icon p-2 shrink-0"><svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor"><path d="M9.153 11.603c.795 0 1.439-.879 1.439-1.962s-.644-1.962-1.439-1.962-1.439.879-1.439 1.962.644 1.962 1.439 1.962zm-3.204 1.362c-.026-.307-.131 5.218 6.063 5.551 6.066-.25 6.066-5.551 6.066-5.551-6.078 1.416-12.129 0-12.129 0zm11.363-1.108s-.669 1.959-5.051 1.959c-3.379 0-5.549-2.158-5.549-2.158-1.512 1.522-2.462 3.64-2.462 5.971 0 4.634 3.774 8.409 8.41 8.409s8.41-3.775 8.41-8.409c0-2.304-.898-4.364-2.358-5.872zm-1.808-1.905c0-1.083-.644-1.962-1.439-1.962s-1.439.879-1.439 1.962.644 1.962 1.439 1.962 1.439-.879 1.439-1.962z"></path></svg></button>
            <button class="wa-text-icon p-2 shrink-0"><svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M1.816 15.556v.002c0 1.502.584 2.912 1.646 3.972s2.472 1.647 3.974 1.647a5.58 5.58 0 0 0 3.972-1.645l9.547-9.548c.769-.768 1.147-1.767 1.058-2.817-.079-.968-.548-1.927-1.319-2.698-1.594-1.592-4.068-1.711-5.517-.262l-7.916 7.915c-.881.881-.792 2.25.214 3.261.959.958 2.423 1.053 3.263.215l5.511-5.512c.28-.28.267-.722.053-.936l-.244-.244c-.191-.191-.567-.349-.957.04l-5.506 5.506c-.18.18-.635.127-.976-.214-.098-.097-.576-.613-.213-.973l7.915-7.917c.818-.817 2.267-.699 3.23.262.5.501.802 1.1.849 1.685.051.573-.156 1.111-.589 1.543l-9.547 9.549a3.97 3.97 0 0 1-2.829 1.171 3.975 3.975 0 0 1-2.83-1.173 3.973 3.973 0 0 1-1.172-2.828c0-1.071.415-2.076 1.172-2.83l7.209-7.211c.157-.157.264-.579.028-.814L11.5 4.36a.572.572 0 0 0-.834.018l-7.205 7.207a5.577 5.577 0 0 0-1.645 3.971z"></path></svg></button>
            
            <div class="flex-1 bg-white rounded-lg flex items-center min-h-[42px] px-3 mb-1 shadow-sm">
                <textarea x-model="newMessage" @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()" rows="1" class="w-full bg-transparent border-none focus:ring-0 resize-none py-2 text-[15px] max-h-32 wa-scroll" placeholder="Bir mesaj yazın"></textarea>
            </div>
            
            <button @click="sendMessage" x-show="newMessage.trim()" class="wa-text-icon p-2 shrink-0 hover:text-[#00a884] transition-colors mb-1">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M1.101 21.757L23.8 12.028 1.101 2.3l.011 7.912 13.623 1.816-13.623 1.817-.011 7.912z"></path></svg>
            </button>
            <button x-show="!newMessage.trim()" class="wa-text-icon p-2 shrink-0 mb-1">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M11.999 14.942c2.001 0 3.531-1.53 3.531-3.531V4.35c0-2.001-1.53-3.531-3.531-3.531S8.469 2.35 8.469 4.35v7.061c0 2.001 1.53 3.531 3.53 3.531zm6.238-3.53c0 3.531-2.942 6.002-6.237 6.002s-6.237-2.471-6.237-6.002H3.761c0 4.001 3.178 7.297 7.061 7.885v3.884h2.354v-3.884c3.884-.588 7.061-3.884 7.061-7.885h-2.002z"></path></svg>
            </button>
        </div>
    </div>

    {{-- Yeni Grup Modalı --}}
    <div x-show="openGroupModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#111b21] bg-opacity-80" x-transition.opacity>
        <div class="bg-white rounded-lg w-full max-w-sm shadow-2xl overflow-hidden" @click.away="openGroupModal = false" x-transition.scale.origin.bottom>
            <div class="px-6 py-4 border-b wa-border flex justify-between items-center wa-bg-panel">
                <h3 class="text-[16px] font-medium wa-text-primary">Yeni Grup Kur</h3>
                <button @click="openGroupModal = false" class="wa-text-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-4 bg-white">
                <div>
                    <label class="block text-sm text-[#00a884] mb-2 font-medium">Grup Konusu</label>
                    <input type="text" x-model="newGroup.name" class="w-full border-b-2 border-[#00a884] focus:border-[#00a884] focus:ring-0 px-0 py-2 text-[15px]" placeholder="Grup konusunu yazın">
                </div>
                <div class="pt-4">
                    <label class="block text-sm wa-text-secondary mb-2">Katılımcılar</label>
                    <div class="max-h-48 overflow-y-auto wa-scroll border wa-border rounded-lg">
                        <template x-for="user in usersList" :key="user.id">
                            <label class="flex items-center gap-3 p-3 hover:bg-[#f5f6f6] cursor-pointer border-b border-[#f0f2f5]">
                                <input type="checkbox" :value="user.id" x-model="newGroup.selectedUsers" class="w-4 h-4 rounded text-[#00a884] focus:ring-[#00a884] border-slate-300">
                                <span class="text-[15px] wa-text-primary" x-text="user.name"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <button @click="createGroup" :disabled="!newGroup.name.trim() || newGroup.selectedUsers.length === 0 || isCreatingGroup" class="w-full py-3 bg-[#00a884] text-white font-medium rounded-lg hover:bg-[#029071] disabled:opacity-50 transition-colors mt-4">
                    Oluştur
                </button>
            </div>
        </div>
    </div>

</div>

{{-- Notification Audio --}}
<audio id="chat-notification-sound" preload="auto"></audio>

<script>
    const popSoundData = 'data:audio/wav;base64,UklGRigAAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQQAAAAAAAD//w=='; 
    document.getElementById('chat-notification-sound').src = popSoundData;

    function chatApp() {
        return {
            conversations: [],
            searchQuery: '',
            activeChat: null,
            messages: [],
            newMessage: '',
            isSending: false,
            pollInterval: null,
            
            // Group logic
            openGroupModal: false,
            usersList: [],
            newGroup: {
                name: '',
                selectedUsers: []
            },
            isCreatingGroup: false,

            init() {
                this.fetchConversations();
                this.fetchUsersList();
                
                this.pollInterval = setInterval(() => {
                    this.fetchConversations(true);
                    if (this.activeChat) {
                        this.fetchMessages(true);
                    }
                }, 3000);
            },

            get filteredConversations() {
                if (!this.searchQuery.trim()) return this.conversations;
                const query = this.searchQuery.toLowerCase();
                return this.conversations.filter(c => c.name.toLowerCase().includes(query));
            },

            async fetchConversations(isPolling = false) {
                try {
                    const res = await fetch('{{ route("chat.conversations") }}');
                    const data = await res.json();
                    
                    if (isPolling) {
                        let totalUnreadOld = this.conversations.reduce((acc, c) => acc + c.unread_count, 0);
                        let totalUnreadNew = data.reduce((acc, c) => acc + c.unread_count, 0);
                        
                        if (totalUnreadNew > totalUnreadOld) {
                            this.playSound();
                        }
                    }

                    this.conversations = data;
                } catch (e) {}
            },

            async fetchMessages(isPolling = false) {
                if (!this.activeChat) return;
                
                try {
                    const res = await fetch(`{{ route("chat.messages") }}?type=${this.activeChat.type}&id=${this.activeChat.id}`);
                    const data = await res.json();
                    
                    if (isPolling && data.length > this.messages.length) {
                        const newMsg = data[data.length - 1];
                        if (!newMsg.is_mine) {
                            this.playSound();
                        }
                    }

                    const shouldScroll = !isPolling || data.length > this.messages.length;
                    this.messages = data;
                    
                    if (shouldScroll) {
                        this.scrollToBottom();
                        const chatInList = this.conversations.find(c => c.id === this.activeChat.id && c.type === this.activeChat.type);
                        if (chatInList) chatInList.unread_count = 0;
                    }
                } catch (e) {}
            },

            async sendMessage() {
                if (!this.newMessage.trim() || !this.activeChat) return;
                
                this.isSending = true;
                const body = this.newMessage;
                this.newMessage = ''; 
                
                try {
                    await fetch('{{ route("chat.send") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            type: this.activeChat.type,
                            id: this.activeChat.id,
                            body: body
                        })
                    });
                    
                    await this.fetchMessages();
                    await this.fetchConversations();
                } catch (e) {
                } finally {
                    this.isSending = false;
                    this.scrollToBottom();
                }
            },

            selectChat(conv) {
                this.activeChat = conv;
                this.messages = []; 
                conv.unread_count = 0; 
                this.fetchMessages();
                
                setTimeout(() => {
                    const ta = document.querySelector('textarea');
                    if(ta) ta.focus();
                }, 100);
            },

            scrollToBottom() {
                setTimeout(() => {
                    if (this.$refs.messagesContainer) {
                        this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                    }
                }, 100);
            },

            playSound() {
                const audio = document.getElementById('chat-notification-sound');
                if(audio) {
                    audio.currentTime = 0;
                    audio.play().catch(e => console.log('Audio play blocked:', e));
                }
            },

            async fetchUsersList() {
                try {
                    const res = await fetch('{{ route("chat.users") }}');
                    this.usersList = await res.json();
                } catch (e) {}
            },

            async createGroup() {
                if (!this.newGroup.name.trim() || this.newGroup.selectedUsers.length === 0) return;
                this.isCreatingGroup = true;

                try {
                    const res = await fetch('{{ route("chat.group.create") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            name: this.newGroup.name,
                            users: this.newGroup.selectedUsers
                        })
                    });
                    
                    if (res.ok) {
                        this.openGroupModal = false;
                        this.newGroup.name = '';
                        this.newGroup.selectedUsers = [];
                        await this.fetchConversations();
                    }
                } catch (e) {
                } finally {
                    this.isCreatingGroup = false;
                }
            }
        }
    }
</script>
@endsection
