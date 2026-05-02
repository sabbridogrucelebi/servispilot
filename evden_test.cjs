const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const os = require('os');

console.log("==================================================");
console.log("   SERVİSPİLOT - PRO EVDEN TEST BAŞLATICI (V2 LAN)");
console.log("==================================================\n");

// 1. Yerel IP Adresini Bul
function getLocalIp() {
    const interfaces = os.networkInterfaces();
    for (const devName in interfaces) {
        const iface = interfaces[devName];
        for (let i = 0; i < iface.length; i++) {
            const alias = iface[i];
            // IPv4 olan, localhost olmayan ve dahili olmayan IP'yi bul
            if (alias.family === 'IPv4' && alias.address !== '127.0.0.1' && !alias.internal) {
                // Eger VMware veya VirtualBox sanal ag bagdastiricisi ise es gecmeye calis
                if (devName.toLowerCase().includes('vmware') || devName.toLowerCase().includes('virtual')) {
                    continue;
                }
                return alias.address;
            }
        }
    }
    return '127.0.0.1';
}

const localIp = getLocalIp();
const apiUrl = `http://${localIp}:8000/api`;

console.log(`[BAŞARILI] Yerel Wi-Fi IP Adresiniz: ${localIp}`);
console.log(`[BAŞARILI] Mobil API Adresi: ${apiUrl}\n`);

// 2. config.js dosyasını güncelle
console.log("1) Mobil uygulama config.js otomatik güncelleniyor...");
const configPath = path.join(__dirname, 'mobile-app', 'src', 'config.js');

if (fs.existsSync(configPath)) {
    let configContent = fs.readFileSync(configPath, 'utf8');
    configContent = configContent.replace(/API_BASE_URL:\s*['"][^'"]+['"]/, `API_BASE_URL: '${apiUrl}'`);
    fs.writeFileSync(configPath, configContent);
    console.log("   => config.js başarıyla Wi-Fi IP'nize ayarlandı!\n");
} else {
    console.error("   => HATA: config.js bulunamadı!\n");
}

// 3. Laravel ve Expo'yu paralel başlat
console.log("2) Sunucu (Laravel) baslatiliyor. Baska bir ekranda 'php artisan serve' acik olmadigindan emin olun!\n");

// Laravel Başlat
const laravel = spawn('php', ['artisan', 'serve', '--host=0.0.0.0', '--port=8000'], { 
    cwd: __dirname,
    stdio: 'inherit',
    shell: true
});

// Biraz bekle ve Expo'yu başlat (Laravel tam ayaklansın diye)
setTimeout(() => {
    console.log("\n==================================================");
    console.log("3) Expo (Mobil Arayüz) LAN modunda başlatılıyor...");
    console.log("   DİKKAT: Telefonunuz ile bilgisayarınız AYNI Wİ-Fİ ağına bağlı olmalıdır!");
    console.log("   Aşağıda çıkan QR kodu telefonunuzdan Expo Go ile okutun.");
    console.log("==================================================\n");
    
    // Tünel yerine `--lan` kullanıyoruz (Yerel ağ)
    const expo = spawn('npx', ['expo', 'start', '--lan'], { 
        cwd: path.join(__dirname, 'mobile-app'),
        stdio: 'inherit',
        shell: true
    });

    expo.on('close', (code) => {
        console.log(`\nExpo kapandı. Tüm sistemler kapatılıyor...`);
        spawn('taskkill', ['/pid', laravel.pid, '/f', '/t']);
        process.exit();
    });

    process.on('SIGINT', () => {
        console.log("\nÇıkış yapılıyor. Laravel ve Expo durduruluyor...");
        spawn('taskkill', ['/pid', expo.pid, '/f', '/t']);
        spawn('taskkill', ['/pid', laravel.pid, '/f', '/t']);
        process.exit();
    });

}, 4000);
