const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const qrcode = require('qrcode-terminal');

console.log("==================================================");
console.log("   SERVİSPİLOT - PRO UZAKTAN BAĞLANTI (V3 NGROK'SUZ)");
console.log("==================================================\n");

console.log("Sunucu temizleniyor (Eski açık portlar kapatılıyor)...");

let laravelUrl = '';
let expoUrl = '';

// 1. Laravel Tüneli Başlat
const ltLaravel = spawn('npx', ['localtunnel', '--port', '8000'], { shell: true });
ltLaravel.stdout.on('data', (data) => {
    const output = data.toString();
    if (output.includes('your url is:')) {
        const match = output.match(/https?:\/\/[a-z0-9-]+\.loca\.lt/);
        if (match && !laravelUrl) {
            laravelUrl = match[0] + '/api';
            console.log("=> Laravel API Tüneli Açıldı: " + laravelUrl);
            startIfBothReady();
        }
    }
});

// 2. Expo Tüneli Başlat
const ltExpo = spawn('npx', ['localtunnel', '--port', '8081'], { shell: true });
ltExpo.stdout.on('data', (data) => {
    const output = data.toString();
    if (output.includes('your url is:')) {
        const match = output.match(/https?:\/\/([a-z0-9-]+)\.loca\.lt/);
        if (match && !expoUrl) {
            // Expo URL'ini exp:// formatına çevir
            expoUrl = `exp://${match[1]}.loca.lt`;
            console.log("=> Expo Arayüz Tüneli Açıldı: " + expoUrl);
            startIfBothReady();
        }
    }
});

function startIfBothReady() {
    if (laravelUrl && expoUrl) {
        console.log("\n=> Her iki tünel de başarıyla oluşturuldu!");
        
        // config.js güncelle
        const configPath = path.join(__dirname, 'mobile-app', 'src', 'config.js');
        if (fs.existsSync(configPath)) {
            let configContent = fs.readFileSync(configPath, 'utf8');
            configContent = configContent.replace(/API_BASE_URL:\s*['"][^'"]+['"]/, `API_BASE_URL: '${laravelUrl}'`);
            fs.writeFileSync(configPath, configContent);
            console.log("=> config.js ayarlandı.");
        }
        
        // Laravel Başlat
        const laravel = spawn('php', ['artisan', 'serve', '--port=8000'], { 
            cwd: __dirname,
            stdio: 'ignore',
            shell: true
        });

        // Expo Başlat
        const expo = spawn('npx', ['expo', 'start', '--lan'], { 
            cwd: path.join(__dirname, 'mobile-app'),
            stdio: 'ignore', // Kendi QR kodumuzu basacağımız için Expo'nun çıktılarını gizliyoruz
            shell: true,
            env: {
                ...process.env,
                EXPO_PACKAGER_PROXY_URL: `https://${expoUrl.replace('exp://', '')}`,
                EXPO_PUBLIC_API_URL: laravelUrl // Opsiyonel, guvence olsun diye
            }
        });

        setTimeout(() => {
            console.log("\n==================================================");
            console.log("   SİSTEM HAZIR! UZAKTAN ERİŞİM QR KODU AŞAĞIDADIR");
            console.log("   DİKKAT: Telefonunuzdan SADECE BU QR KODU okutun.");
            console.log("==================================================\n");
            
            qrcode.generate(expoUrl, { small: true });

            console.log("\nÇıkmak için Ctrl+C'ye basın.");
        }, 5000);

        process.on('SIGINT', () => {
            console.log("\nSistemler kapatılıyor...");
            spawn('taskkill', ['/pid', laravel.pid, '/f', '/t']);
            spawn('taskkill', ['/pid', expo.pid, '/f', '/t']);
            ltLaravel.kill();
            ltExpo.kill();
            process.exit();
        });
    }
}
