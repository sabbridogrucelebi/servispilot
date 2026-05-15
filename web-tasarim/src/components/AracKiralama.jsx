import React from 'react';
import { motion } from 'framer-motion';
import { ArrowLeft, Car, Key, Settings, CreditCard, ShieldCheck, MapPin, Gauge, Headset } from 'lucide-react';

export default function AracKiralama({ onBack, onQuoteClick }) {
  return (
    <motion.div 
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -20 }}
      transition={{ duration: 0.5 }}
      style={{ background: '#f8fafc', minHeight: '100vh' }}
    >
      {/* Hero Section */}
      <div style={{
        background: 'linear-gradient(135deg, #1f2937 0%, #111827 100%)',
        padding: '120px 5% 80px',
        color: 'white',
        textAlign: 'center',
        position: 'relative',
        overflow: 'hidden'
      }}>
        {/* Animated Premium Icons Background */}
        <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', overflow: 'hidden', zIndex: 0, pointerEvents: 'none' }}>
          {[
            { Icon: Car, size: 140, x: '8%', delay: 0, duration: 25 },
            { Icon: Key, size: 90, x: '22%', delay: 5, duration: 22 },
            { Icon: Settings, size: 60, x: '35%', delay: 2, duration: 18 },
            { Icon: CreditCard, size: 120, x: '55%', delay: 10, duration: 28 },
            { Icon: ShieldCheck, size: 100, x: '70%', delay: 1, duration: 20 },
            { Icon: MapPin, size: 130, x: '85%', delay: 7, duration: 24 },
            { Icon: Gauge, size: 80, x: '15%', delay: 12, duration: 19 },
            { Icon: Headset, size: 150, x: '78%', delay: 3, duration: 26 },
          ].map((item, i) => (
            <motion.div
              key={i}
              style={{
                position: 'absolute',
                bottom: '-30%',
                left: item.x,
                color: 'rgba(255, 255, 255, 0.05)',
                pointerEvents: 'none',
              }}
              animate={{ y: ['0vh', '-130vh'], rotate: [0, 120] }}
              transition={{ duration: item.duration, repeat: Infinity, ease: 'linear', delay: item.delay }}
            >
              <item.Icon size={item.size} strokeWidth={1} />
            </motion.div>
          ))}
        </div>
        
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
          <span style={{ display: 'inline-block', background: 'rgba(16, 84, 156, 0.8)', padding: '6px 16px', borderRadius: '50px', fontSize: '0.85rem', fontWeight: 700, letterSpacing: '2px', marginBottom: '20px' }}>
            ÖZGÜRLÜĞÜN ANAHTARI
          </span>
          <h1 style={{ fontSize: 'clamp(2.5rem, 5vw, 4rem)', fontWeight: 800, marginBottom: '20px', fontFamily: 'var(--font-heading)', textShadow: '0 4px 20px rgba(0,0,0,0.3)' }}>
            Araç Kiralama
          </h1>
          <p style={{ fontSize: '1.2rem', color: 'rgba(255,255,255,0.85)', maxWidth: '700px', margin: '0 auto', lineHeight: 1.6 }}>
            Bireysel veya kurumsal seyahatleriniz için geniş, yeni ve bakımlı araç filomuzla hizmetinizdeyiz. İhtiyacınıza uygun aracı seçin, yola güvenle çıkın.
          </p>
        </motion.div>
      </div>

      {/* Content Section */}
      <div style={{ maxWidth: '1100px', margin: '-40px auto 60px', padding: '0 5%', position: 'relative', zIndex: 10 }}>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '40px' }}>
          
          <motion.div 
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.4 }}
            style={{ background: 'white', padding: '50px', borderRadius: '24px', boxShadow: '0 20px 40px rgba(0,0,0,0.08)', borderTop: '4px solid #10549c' }}
          >
            <h2 style={{ fontSize: '2.2rem', color: 'var(--color-text-primary)', marginBottom: '24px', fontFamily: 'var(--font-heading)', lineHeight: 1.2 }}>
              Geniş Filo, <br/><span style={{ color: '#10549c' }}>Esnek Çözümler.</span>
            </h2>
            <p style={{ fontSize: '1.1rem', color: 'var(--color-text-secondary)', lineHeight: 1.8, marginBottom: '20px' }}>
              İster günlük kısa bir seyahat, ister uzun dönem kurumsal kiralama olsun; bütçenize ve tarzınıza en uygun aracı filomuzda bulabilirsiniz.
            </p>
            <p style={{ fontSize: '1.1rem', color: 'var(--color-text-secondary)', lineHeight: 1.8, marginBottom: '30px' }}>
              Filomuzdaki tüm araçlar düşük kilometreli, yetkili servis bakımlı ve tam kasko güvencesindedir. Ekonomik sınıftan lüks SUV modellere, otomatik veya manuel vites seçenekleriyle sorunsuz bir sürüş keyfi yaşayın. Ekstra sürpriz maliyetler olmadan şeffaf fiyatlandırma ile kiralayın.
            </p>
            <button className="btn-primary" onClick={onQuoteClick}>
              Müsait Araçları Sor
            </button>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, x: 30 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: 0.6 }}
            style={{ display: 'flex', flexDirection: 'column', gap: '20px', paddingTop: '20px' }}
          >
            <FeatureCard 
              icon={<ShieldCheck size={28} color="#FFF" />} 
              title="Tam Kasko Güvencesi" 
              desc="Tüm araçlarımız Rent A Car kaskosuna sahip olup, yolculuğunuz boyunca içinizin rahat olmasını sağlar." 
            />
            <FeatureCard 
              icon={<Settings size={28} color="#FFF" />} 
              title="Yetkili Servis Bakımlı" 
              desc="Araçlarımızın tamamı düşük kilometrede olup, periyodik bakımları zamanında yetkili servislerde yapılmaktadır." 
            />
            <FeatureCard 
              icon={<Headset size={28} color="#FFF" />} 
              title="7/24 Yol Yardım" 
              desc="Olası bir arıza veya kaza durumunda 7 gün 24 saat kesintisiz destek ve anında ikame araç hizmeti." 
            />
            <FeatureCard 
              icon={<CreditCard size={28} color="#FFF" />} 
              title="Şeffaf Fiyatlandırma" 
              desc="Gizli ücretler, son dakika sürprizleri yok. Anlaştığınız fiyat üzerinden güvenli kiralama." 
            />
          </motion.div>

        </div>
      </div>
    </motion.div>
  );
}

function FeatureCard({ icon, title, desc }) {
  return (
    <motion.div 
      whileHover={{ y: -5, boxShadow: '0 15px 35px rgba(0,0,0,0.06)' }}
      style={{ background: 'white', padding: '24px', borderRadius: '16px', boxShadow: '0 10px 30px rgba(0,0,0,0.03)', display: 'flex', gap: '20px', alignItems: 'flex-start', transition: 'all 0.3s ease' }}
    >
      <div style={{ background: 'linear-gradient(135deg, #10549c 0%, #1e293b 100%)', padding: '14px', borderRadius: '14px', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, boxShadow: '0 8px 15px rgba(16,84,156,0.3)' }}>
        {icon}
      </div>
      <div>
        <h3 style={{ fontSize: '1.15rem', marginBottom: '8px', color: 'var(--color-text-primary)', fontFamily: 'var(--font-heading)' }}>{title}</h3>
        <p style={{ fontSize: '0.95rem', color: 'var(--color-text-secondary)', lineHeight: 1.6 }}>{desc}</p>
      </div>
    </motion.div>
  );
}
