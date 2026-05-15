import React from 'react';
import { motion } from 'framer-motion';
import { ArrowLeft, Compass, Map, Calendar, Sun, Plane, Star, Camera, Palmtree } from 'lucide-react';

export default function TurizmTasimaciligi({ onBack, onQuoteClick }) {
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
        background: 'linear-gradient(135deg, #10549c 0%, #0a3d75 100%)',
        padding: '120px 5% 80px',
        color: 'white',
        textAlign: 'center',
        position: 'relative',
        overflow: 'hidden'
      }}>
        {/* Animated Premium Icons Background */}
        <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', overflow: 'hidden', zIndex: 0, pointerEvents: 'none' }}>
          {[
            { Icon: Compass, size: 140, x: '8%', delay: 0, duration: 25 },
            { Icon: Map, size: 90, x: '22%', delay: 5, duration: 22 },
            { Icon: Sun, size: 60, x: '35%', delay: 2, duration: 18 },
            { Icon: Calendar, size: 120, x: '55%', delay: 10, duration: 28 },
            { Icon: Plane, size: 100, x: '70%', delay: 1, duration: 20 },
            { Icon: Camera, size: 130, x: '85%', delay: 7, duration: 24 },
            { Icon: Palmtree, size: 80, x: '15%', delay: 12, duration: 19 },
            { Icon: Star, size: 150, x: '78%', delay: 3, duration: 26 },
          ].map((item, i) => (
            <motion.div
              key={i}
              style={{
                position: 'absolute',
                bottom: '-30%',
                left: item.x,
                color: 'rgba(255, 255, 255, 0.1)',
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
          <span style={{ display: 'inline-block', background: 'rgba(226,27,27,0.8)', padding: '6px 16px', borderRadius: '50px', fontSize: '0.85rem', fontWeight: 700, letterSpacing: '2px', marginBottom: '20px' }}>
            KEŞFETMEYE HAZIR MISINIZ?
          </span>
          <h1 style={{ fontSize: 'clamp(2.5rem, 5vw, 4rem)', fontWeight: 800, marginBottom: '20px', fontFamily: 'var(--font-heading)', textShadow: '0 4px 20px rgba(0,0,0,0.3)' }}>
            Turizm Taşımacılığı
          </h1>
          <p style={{ fontSize: '1.2rem', color: 'rgba(255,255,255,0.85)', maxWidth: '700px', margin: '0 auto', lineHeight: 1.6 }}>
            Kültür turları, havalimanı transferleri ve özel gezi organizasyonlarında yeni model, tam donanımlı turizm araçlarımızla eşsiz bir seyahat deneyimi sunuyoruz.
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
              Yolculuğun<br/><span style={{ color: 'var(--color-accent)' }}>Keyfini Çıkarın.</span>
            </h2>
            <p style={{ fontSize: '1.1rem', color: 'var(--color-text-secondary)', lineHeight: 1.8, marginBottom: '20px' }}>
              Mehmet Çelebi Turizm olarak, seyahatlerinizin sadece bir ulaşım değil, unutulmaz bir anı olması gerektiğine inanıyoruz.
            </p>
            <p style={{ fontSize: '1.1rem', color: 'var(--color-text-secondary)', lineHeight: 1.8, marginBottom: '30px' }}>
              Yerli ve yabancı turist grupları, okul gezileri, şirket etkinlikleri ve özel organizasyonlar için tasarlanmış VIP dizaynlı otobüs ve minibüs filomuzla Türkiye'nin her noktasına güvenle taşıyoruz. Deneyimli kaptanlarımız ve lüks araçlarımızla yorgunluğu değil, manzarayı hissedeceksiniz.
            </p>
            <button className="btn-primary" onClick={onQuoteClick}>
              Tur Programı Planla
            </button>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, x: 30 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: 0.6 }}
            style={{ display: 'flex', flexDirection: 'column', gap: '20px', paddingTop: '20px' }}
          >
            <FeatureCard 
              icon={<Compass size={28} color="#FFF" />} 
              title="Geniş Rota Ağı" 
              desc="Türkiye'nin tüm turistik destinasyonlarına hakim, bölgesel tecrübeye sahip profesyonel kaptanlar." 
            />
            <FeatureCard 
              icon={<Sun size={28} color="#FFF" />} 
              title="İklimlendirme ve Konfor" 
              desc="Her mevsim şartlarına uygun, özel iklimlendirme sistemli ve geniş koltuk aralıklı yeni araçlar." 
            />
            <FeatureCard 
              icon={<Calendar size={28} color="#FFF" />} 
              title="Esnek Planlama" 
              desc="Organizasyon takviminize özel esnek hareket saatleri ve ihtiyaca uygun araç kapasiteleri." 
            />
            <FeatureCard 
              icon={<Star size={28} color="#FFF" />} 
              title="Tam Donanımlı Filo" 
              desc="Buzdolabı, TV, ses sistemi ve VIP dizaynlı iç mekanlarla uzun yollarda otel konforu." 
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
