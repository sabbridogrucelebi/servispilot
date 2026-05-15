import React from 'react';
import { motion } from 'framer-motion';
import { ArrowLeft, Clock, Building2, ShieldCheck, MapPin } from 'lucide-react';

export default function PersonelTasimaciligi({ onBack, onQuoteClick }) {
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
        background: 'linear-gradient(135deg, #050b14 0%, #0a192f 100%)',
        padding: '120px 5% 80px',
        color: 'white',
        textAlign: 'center',
        position: 'relative',
        overflow: 'hidden'
      }}>
        {/* Animated Premium Corporate Icons Background */}
        <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', overflow: 'hidden', zIndex: 0, pointerEvents: 'none' }}>
          {[
            { Icon: Building2, size: 140, x: '10%', delay: 0, duration: 25 },
            { Icon: Clock, size: 110, x: '40%', delay: 2, duration: 18 },
            { Icon: ShieldCheck, size: 100, x: '70%', delay: 1, duration: 20 },
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
              animate={{ y: ['0vh', '-130vh'], rotate: [0, 90] }}
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
          <span style={{ display: 'inline-block', background: 'rgba(226,27,27,0.8)', padding: '6px 16px', borderRadius: '50px', fontSize: '0.85rem', fontWeight: 700, letterSpacing: '2px', marginBottom: '20px' }}>
            İŞİNİZE DEĞER KATIYORUZ
          </span>
          <h1 style={{ fontSize: 'clamp(2.5rem, 5vw, 4rem)', fontWeight: 900, marginBottom: '20px', fontFamily: 'var(--font-heading)', textShadow: '0 4px 20px rgba(0,0,0,0.5)', letterSpacing: '1px' }}>
            Kurumsal Personel Taşımacılığı
          </h1>
          <p style={{ fontSize: '1.2rem', color: 'rgba(255,255,255,0.85)', maxWidth: '700px', margin: '0 auto', lineHeight: 1.6 }}>
            Çalışanlarınızın her güne zinde, güvenli ve motive başlaması kurumunuzun en büyük gücüdür. Modern filomuz ve akıllı rota optimizasyonu ile şirketinizin dinamiğine güç katıyoruz.
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
            style={{ background: 'white', padding: '50px', borderRadius: '24px', boxShadow: '0 20px 40px rgba(0,0,0,0.08)', borderTop: '4px solid var(--color-accent-secondary)' }}
          >
            <h2 style={{ fontSize: '2.2rem', color: 'var(--color-text-primary)', marginBottom: '24px', fontFamily: 'var(--font-heading)', lineHeight: 1.2 }}>
              Sıfır Gecikme,<br/><span style={{ color: 'var(--color-accent)' }}>Maksimum Verim.</span>
            </h2>
            <p style={{ fontSize: '1.1rem', color: 'var(--color-text-secondary)', lineHeight: 1.8, marginBottom: '20px' }}>
              Zamanın iş dünyasındaki değerini çok iyi biliyoruz. Gelişmiş filo takip sistemimiz ve tecrübeli sürücülerimiz sayesinde personelinizin tam zamanında iş başı yapmasını sağlıyoruz. Gecikmelere ve strese son veriyoruz.
            </p>
            <p style={{ fontSize: '1.1rem', color: 'var(--color-text-secondary)', lineHeight: 1.8, marginBottom: '30px' }}>
              Personelinizi taşıdığımız her araç, düzenli bakımları yapılan, yüksek konforlu ve şirketinizin prestijini yansıtan yeni nesil donanımlara sahiptir. ISO 9001 kalite standartlarında ve tüm yetki belgelerine sahip profesyonel kadromuzla, operasyonlarınıza değer katıyoruz.
            </p>
            <button className="btn-primary" onClick={onQuoteClick}>
              Hemen Teklif Alın
            </button>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, x: 30 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: 0.6 }}
            style={{ display: 'flex', flexDirection: 'column', gap: '20px', paddingTop: '20px' }}
          >
            <FeatureCard 
              icon={<Clock size={28} color="#FFF" />} 
              title="Dakiklik Garantisi" 
              desc="Akıllı rota optimizasyonu ile en yoğun trafikte bile zamanında ve güvenli ulaşım garantisi veriyoruz." 
            />
            <FeatureCard 
              icon={<Building2 size={28} color="#FFF" />} 
              title="Prestijli Araç Filosu" 
              desc="Kurumunuzun imajına yakışır, geniş, klimalı ve konforlu, tam donanımlı filomuz hizmetinizdedir." 
            />
            <FeatureCard 
              icon={<MapPin size={28} color="#FFF" />} 
              title="GPS Canlı Takip" 
              desc="GPS tabanlı canlı takip sistemi ile güzergah, hız ve personel biniş kontrollerini anlık yapıyoruz." 
            />
            <FeatureCard 
              icon={<ShieldCheck size={28} color="#FFF" />} 
              title="Tam Denetim" 
              desc="Alanında uzman denetmenlerimizle araç içi kalite, temizlik ve sürücü davranış kontrolleri." 
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
      <div style={{ background: 'linear-gradient(135deg, #10549c 0%, #0a3d75 100%)', padding: '14px', borderRadius: '14px', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, boxShadow: '0 8px 15px rgba(16,84,156,0.3)' }}>
        {icon}
      </div>
      <div>
        <h3 style={{ fontSize: '1.15rem', marginBottom: '8px', color: 'var(--color-text-primary)', fontFamily: 'var(--font-heading)' }}>{title}</h3>
        <p style={{ fontSize: '0.95rem', color: 'var(--color-text-secondary)', lineHeight: 1.6 }}>{desc}</p>
      </div>
    </motion.div>
  );
}
