import React from 'react';
import { motion } from 'framer-motion';
import { ArrowLeft, Users, Wifi, Wind, Coffee, ShieldCheck, Tv } from 'lucide-react';

const fleetData = {
  otobus: {
    title: 'Otobüs Filomuz',
    desc: 'Şehirlerarası ve uluslararası standartlarda, maksimum konfor ve güvenlik donanımına sahip lüks otobüslerimiz.',
    vehicles: [
      {
        name: 'Mercedes-Benz Travego',
        seats: '46+1+1 / 54+1+1',
        image: '/images/fleet/otobus.png', // Fallback to existing image
        features: ['Geniş Diz Mesafesi', 'Kişisel Eğlence Ekranı (10 inç)', '220V Priz & USB', 'Ergonomik Deri Koltuklar', 'Gelişmiş İklimlendirme', 'Araç İçi Buzdolabı & Çay/Kahve Makinesi'],
        highlights: [Wind, Tv, Coffee]
      },
      {
        name: 'Mercedes-Benz Tourismo',
        seats: '50+1+1 / 54+1+1',
        image: '/images/fleet/otobus.png',
        features: ['AEBS (Acil Fren Sistemi)', 'Şerit Takip Asistanı', 'Wi-Fi Bağlantısı', 'Geniş Bagaj Hacmi', 'Özel Okuma Lambaları', 'Akıllı Klima Sistemi'],
        highlights: [ShieldCheck, Wifi, Users]
      },
      {
        name: 'Neoplan Tourliner',
        seats: '50+1+1',
        image: '/images/fleet/otobus.png',
        features: ['Panoramik Camlar', 'Premium Koltuk Kumaşları', 'Çift Bölge İklimlendirme', 'Geniş Koridor', 'Aktif Süspansiyon Sistemi', 'LED Aydınlatma'],
        highlights: [Wind, Users, Coffee]
      }
    ]
  },
  midibus: {
    title: 'Midibüs Filomuz',
    desc: 'Orta ölçekli gruplar için tasarlanmış, kıvrak, konforlu ve ekonomik seyahat çözümleri.',
    vehicles: [
      {
        name: 'Otokar Sultan Mega',
        seats: '31+1+1',
        image: '/images/fleet/midibus.png',
        features: ['Geniş İç Hacim', 'Yatar Koltuklar', 'Güçlü Klima', 'Hostes Koltuğu', 'Geniş Bagaj', 'Okuma Lambası'],
        highlights: [Wind, Users, ShieldCheck]
      },
      {
        name: 'Isuzu Turkuaz',
        seats: '31+1',
        image: '/images/fleet/midibus.png',
        features: ['Ergonomik Koltuklar', 'Çift Cam İzolasyonu', 'Bağımsız Süspansiyon', 'Dijital Klima', 'TV/DVD Sistemi'],
        highlights: [Tv, Wind, ShieldCheck]
      }
    ]
  },
  minibus: {
    title: 'Minibüs Filomuz',
    desc: 'Küçük gruplar, personel taşımacılığı ve butik turlar için ideal, seri ve donanımlı minibüslerimiz.',
    vehicles: [
      {
        name: 'Mercedes-Benz Sprinter',
        seats: '16+1 / 19+1',
        image: '/images/fleet/minibus.png',
        features: ['Gelişmiş Güvenlik Paketi', 'Otomatik Kapı Sistemi', 'Yüksek Tavan Ferahlığı', '3 Noktalı Emniyet Kemeri', 'Bağımsız Arka Klima'],
        highlights: [ShieldCheck, Wind, Users]
      },
      {
        name: 'Volkswagen Crafter',
        seats: '16+1 / 19+1',
        image: '/images/fleet/minibus.png',
        features: ['Konforlu Yolcu Koltukları', 'Sessiz Kabin', 'Gelişmiş Süspansiyon', 'USB Şarj Çıkışları', 'Yolcu Bölümü Kliması'],
        highlights: [Wind, Wifi, ShieldCheck]
      }
    ]
  },
  vip: {
    title: 'VIP Araç Filomuz',
    desc: 'Lüksün sınırlarını zorlayan, tamamen kişiye özel dizayn edilmiş ultra premium VIP araçlarımız.',
    vehicles: [
      {
        name: 'Mercedes-Benz Vito VIP',
        seats: '4+1 / 6+1',
        image: '/images/fleet/vip.png',
        features: ['Elektrikli Masajlı Koltuklar', 'Ara Bölme (TV\'li Asansörlü)', 'PlayStation / Apple TV', 'Minibar ve Kahve Makinesi', 'Yıldız Tavan Aydınlatması', 'Touchpad Kontrol Ünitesi'],
        highlights: [Tv, Coffee, Wifi]
      },
      {
        name: 'Mercedes-Benz Sprinter VIP',
        seats: '9+1 / 12+1',
        image: '/images/fleet/vip.png',
        features: ['Ultra Geniş Diz Mesafesi', 'Business Class Koltuklar', 'Çalışma Masası', 'Buzdolabı', 'Gelişmiş Ses Sistemi', 'Bağımsız Arka İklimlendirme'],
        highlights: [Wind, Tv, ShieldCheck]
      }
    ]
  },
  otomobil: {
    title: 'Otomobil Filomuz',
    desc: 'Bireysel kiralama, şoförlü tahsis ve protokol taşımacılığına uygun, D ve E segmenti prestijli otomobillerimiz.',
    vehicles: [
      {
        name: 'Mercedes-Benz E-Class',
        seats: '4+1',
        image: '/images/fleet/otomobil.png',
        features: ['Deri Döşeme', 'Isıtmalı Koltuklar', 'Sessiz Kabin', 'Ambiyans Aydınlatma', 'Aktif Fren Asistanı'],
        highlights: [ShieldCheck, Wind, Coffee]
      },
      {
        name: 'Volkswagen Passat',
        seats: '4+1',
        image: '/images/fleet/otomobil.png',
        features: ['ErgoComfort Koltuklar', 'Geniş Bagaj (586L)', 'Üç Bölgeli Dijital Klima', 'Yorgunluk Tespit Sistemi', 'Apple CarPlay'],
        highlights: [Wind, Wifi, ShieldCheck]
      }
    ]
  }
};

export default function FiloDetay({ category, onBack, onQuoteClick }) {
  const data = fleetData[category] || fleetData['otobus'];

  return (
    <motion.div 
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -20 }}
      transition={{ duration: 0.5 }}
      style={{ background: '#f8fafc', minHeight: '100vh', paddingBottom: '100px' }}
    >
      {/* Hero Banner */}
      <div style={{
        background: 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
        padding: '120px 5% 80px',
        color: 'white',
        textAlign: 'center',
        position: 'relative',
        overflow: 'hidden'
      }}>
        {/* Background Gradients */}
        <div style={{ position: 'absolute', top: '-50%', left: '-10%', width: '60%', height: '200%', background: 'radial-gradient(circle, rgba(16,84,156,0.2) 0%, rgba(0,0,0,0) 70%)', transform: 'rotate(30deg)' }} />
        <div style={{ position: 'absolute', bottom: '-50%', right: '-10%', width: '60%', height: '200%', background: 'radial-gradient(circle, rgba(226,27,27,0.15) 0%, rgba(0,0,0,0) 70%)', transform: 'rotate(-30deg)' }} />

        <motion.button 
          onClick={onBack}
          whileHover={{ x: -5, background: 'rgba(255,255,255,0.2)' }}
          style={{ 
            position: 'absolute', top: '120px', left: '5%', 
            background: 'rgba(255,255,255,0.1)', border: '1px solid rgba(255,255,255,0.2)', 
            color: 'white', padding: '10px 24px', borderRadius: '50px', 
            display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer',
            backdropFilter: 'blur(10px)', fontWeight: 600, transition: 'all 0.3s ease',
            zIndex: 99
          }}
        >
          <ArrowLeft size={18} /> Geri Dön
        </motion.button>

        <motion.div
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ delay: 0.2, duration: 0.5 }}
          style={{ position: 'relative', zIndex: 10 }}
        >
          <span style={{ display: 'inline-block', background: 'rgba(255,255,255,0.1)', border: '1px solid rgba(255,255,255,0.2)', padding: '6px 20px', borderRadius: '50px', fontSize: '0.85rem', fontWeight: 700, letterSpacing: '2px', marginBottom: '20px' }}>
            PREMIUM ARAÇ FİLOMUZ
          </span>
          <h1 style={{ fontSize: 'clamp(2.5rem, 5vw, 4rem)', fontWeight: 900, marginBottom: '20px', fontFamily: 'var(--font-heading)', textShadow: '0 4px 20px rgba(0,0,0,0.5)' }}>
            {data.title}
          </h1>
          <p style={{ fontSize: '1.2rem', color: 'rgba(255,255,255,0.85)', maxWidth: '700px', margin: '0 auto', lineHeight: 1.6 }}>
            {data.desc}
          </p>
        </motion.div>
      </div>

      {/* Vehicles List */}
      <div style={{ maxWidth: '1200px', margin: '-40px auto 0', padding: '0 5%', position: 'relative', zIndex: 10 }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: '40px' }}>
          {data.vehicles.map((v, index) => (
            <motion.div 
              key={index}
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.3 + (index * 0.1) }}
              style={{ 
                background: 'white', 
                borderRadius: '24px', 
                boxShadow: '0 20px 40px rgba(0,0,0,0.06)', 
                overflow: 'hidden',
                display: 'flex',
                flexDirection: window.innerWidth < 900 ? 'column' : 'row',
                border: '1px solid rgba(0,0,0,0.05)'
              }}
            >
              {/* Image Container */}
              <div style={{ 
                flex: '0 0 45%', 
                background: 'linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '40px',
                position: 'relative'
              }}>
                <div style={{ position: 'absolute', top: '20px', left: '20px', background: 'var(--color-accent)', color: 'white', padding: '6px 16px', borderRadius: '50px', fontSize: '0.85rem', fontWeight: 800, letterSpacing: '1px' }}>
                  {category.toUpperCase()}
                </div>
                <img 
                  src={v.image} 
                  alt={v.name} 
                  style={{ 
                    width: '100%', 
                    height: 'auto', 
                    objectFit: 'contain',
                    filter: 'drop-shadow(0 20px 30px rgba(0,0,0,0.2))',
                    transform: 'scale(1.1)'
                  }} 
                />
              </div>

              {/* Details Container */}
              <div style={{ flex: '1', padding: '40px', display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
                <h2 style={{ fontSize: '2.2rem', fontWeight: 900, color: 'var(--color-heading)', marginBottom: '15px', fontFamily: 'var(--font-heading)' }}>
                  {v.name}
                </h2>
                
                <div style={{ display: 'flex', alignItems: 'center', gap: '15px', marginBottom: '25px', paddingBottom: '25px', borderBottom: '1px solid rgba(0,0,0,0.05)' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', background: 'rgba(16,84,156,0.1)', color: 'var(--color-primary)', padding: '8px 16px', borderRadius: '12px', fontWeight: 700 }}>
                    <Users size={18} />
                    Kapasite: {v.seats} Kişi
                  </div>
                  <div style={{ display: 'flex', gap: '8px' }}>
                    {v.highlights.map((Icon, i) => (
                      <div key={i} style={{ background: '#f8fafc', padding: '8px', borderRadius: '10px', color: '#64748b' }}>
                        <Icon size={18} />
                      </div>
                    ))}
                  </div>
                </div>

                <div style={{ marginBottom: '30px' }}>
                  <h4 style={{ fontSize: '1.1rem', fontWeight: 700, color: 'var(--color-heading)', marginBottom: '15px' }}>Öne Çıkan Donanımlar</h4>
                  <ul style={{ 
                    listStyle: 'none', 
                    padding: 0, 
                    margin: 0, 
                    display: 'grid', 
                    gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', 
                    gap: '12px' 
                  }}>
                    {v.features.map((feature, i) => (
                      <li key={i} style={{ display: 'flex', alignItems: 'center', gap: '10px', color: 'var(--color-text-secondary)', fontSize: '0.95rem', fontWeight: 500 }}>
                        <div style={{ width: '6px', height: '6px', borderRadius: '50%', background: 'var(--color-accent)' }} />
                        {feature}
                      </li>
                    ))}
                  </ul>
                </div>

                <button 
                  onClick={onQuoteClick}
                  style={{ 
                    background: 'var(--color-primary)', 
                    color: 'white', 
                    border: 'none', 
                    padding: '16px 32px', 
                    borderRadius: '50px', 
                    fontSize: '1.05rem', 
                    fontWeight: 700, 
                    cursor: 'pointer', 
                    boxShadow: '0 10px 20px rgba(16,84,156,0.2)',
                    alignSelf: 'flex-start',
                    transition: 'all 0.3s ease'
                  }}
                  onMouseOver={(e) => { e.currentTarget.style.transform = 'translateY(-3px)'; e.currentTarget.style.boxShadow = '0 15px 25px rgba(16,84,156,0.3)'; }}
                  onMouseOut={(e) => { e.currentTarget.style.transform = 'translateY(0)'; e.currentTarget.style.boxShadow = '0 10px 20px rgba(16,84,156,0.2)'; }}
                >
                  Bu Araç İçin Teklif Al
                </button>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </motion.div>
  );
}
